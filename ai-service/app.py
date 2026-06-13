from flask import Flask, request, jsonify
from flask_cors import CORS

from transformers import CLIPModel, CLIPProcessor, AutoModelForCausalLM, AutoTokenizer, pipeline
from PIL import Image
import re

from sqlalchemy import create_engine, text
import numpy as _np

import torch
import numpy as np
import json
import tempfile
import os
try:
    import pytesseract
    HAS_PYTESSERACT = True
except Exception:
    HAS_PYTESSERACT = False

# FAISS index
FAISS_INDEX_PATH = os.path.join(os.path.dirname(__file__), 'index', 'product_index.faiss')
FAISS_IDS_PATH = os.path.join(os.path.dirname(__file__), 'index', 'product_ids.npy')
try:
    import faiss
    HAS_FAISS = True
except Exception:
    HAS_FAISS = False

faiss_index = None
faiss_ids = None
if HAS_FAISS and os.path.exists(FAISS_INDEX_PATH) and os.path.exists(FAISS_IDS_PATH):
    try:
        faiss_index = faiss.read_index(FAISS_INDEX_PATH)
        faiss_ids = _np.load(FAISS_IDS_PATH)
        print('Loaded FAISS index with', faiss_ids.shape[0], 'ids')
    except Exception as e:
        print('Failed to load FAISS index:', e)
        faiss_index = None
        faiss_ids = None
else:
    if not HAS_FAISS:
        print('faiss not installed; FAISS retrieval disabled')
    else:
        print('FAISS index files not found; build with faiss_build.py')

FAISS_RECIPE_INDEX_PATH = os.path.join(os.path.dirname(__file__), 'index', 'recipe_index.faiss')
FAISS_RECIPE_IDS_PATH = os.path.join(os.path.dirname(__file__), 'index', 'recipe_ids.npy')

faiss_recipe_index = None
faiss_recipe_ids = None
if HAS_FAISS and os.path.exists(FAISS_RECIPE_INDEX_PATH) and os.path.exists(FAISS_RECIPE_IDS_PATH):
    try:
        faiss_recipe_index = faiss.read_index(FAISS_RECIPE_INDEX_PATH)
        faiss_recipe_ids = _np.load(FAISS_RECIPE_IDS_PATH)
        print('Loaded FAISS recipe index with', faiss_recipe_ids.shape[0], 'ids')
    except Exception as e:
        print('Failed to load FAISS recipe index:', e)
        faiss_recipe_index = None
        faiss_recipe_ids = None

# =========================
# APP
# =========================

app = Flask(__name__)
CORS(app)

# =========================
# MYSQL
# =========================

DB_URL = "mysql+pymysql://root:mysql@127.0.0.1:3306/sfood"
engine = create_engine(DB_URL)

# =========================
# CLIP
# =========================

MODEL_NAME = "openai/clip-vit-large-patch14"

print("Loading CLIP...")
model = CLIPModel.from_pretrained(MODEL_NAME)
processor = CLIPProcessor.from_pretrained(MODEL_NAME)
print("CLIP loaded")

# =========================
# LLM (Qwen2.5-0.5B-Instruct)
# =========================

LLM_MODEL_NAME = "Qwen/Qwen2.5-0.5B-Instruct"
print("Loading LLM...")
try:
    device = "cuda" if torch.cuda.is_available() else "cpu"
    llm_tokenizer = AutoTokenizer.from_pretrained(LLM_MODEL_NAME)
    llm_model = AutoModelForCausalLM.from_pretrained(LLM_MODEL_NAME, torch_dtype=torch.float16).to(device)
    llm_pipeline = pipeline("text-generation", model=llm_model, tokenizer=llm_tokenizer, max_new_tokens=512)
    print(f"LLM loaded on {device}")
except Exception as e:
    print("Failed to load LLM:", e)
    llm_pipeline = None


# =========================
# EMBEDDING
# =========================

def generate_embedding(image_path):
    image = Image.open(image_path).convert("RGB")

    inputs = processor(images=image, return_tensors="pt")

    with torch.no_grad():
        features = model.get_image_features(**inputs)

    # support different return types from model
    if isinstance(features, torch.Tensor):
        emb_tensor = features
    elif hasattr(features, "pooler_output"):
        emb_tensor = features.pooler_output
    elif isinstance(features, dict) and "pooler_output" in features:
        emb_tensor = features["pooler_output"]
    elif hasattr(features, "last_hidden_state"):
        emb_tensor = features.last_hidden_state.mean(dim=1)
    else:
        raise ValueError(f"Unexpected features type: {type(features)}")

    emb = emb_tensor.squeeze().cpu().numpy()
    norm = np.linalg.norm(emb)
    if norm == 0:
        raise ValueError(f"Zero vector embedding for image: {image_path}")
    emb = emb / norm
    return emb.astype(np.float32)


def compute_color_hist_from_image(image_path):
    img = Image.open(image_path).convert('RGB')
    arr = np.array(img)
    hist = []
    for c in range(3):
        h, _ = np.histogram(arr[..., c].ravel(), bins=16, range=(0, 255))
        hist.extend(h.tolist())
    hist = np.array(hist, dtype=np.float32)
    if hist.sum() > 0:
        hist = hist / hist.sum()
    return hist.astype(np.float32)


# =========================
# COSINE SIMILARITY
# =========================

def cosine_similarity(a, b):
    a = np.asarray(a, dtype=np.float32)
    b = np.asarray(b, dtype=np.float32)
    denom = (np.linalg.norm(a) * np.linalg.norm(b)) + 1e-10
    return float(np.dot(a, b) / denom)


# =========================
# SEARCH
# =========================

@app.route("/search", methods=["POST"])
def search():
    if "image" not in request.files:
        return jsonify({"error": "No image"}), 400

    file = request.files["image"]
    if file.filename == "":
        return jsonify({"error": "Empty filename"}), 400

    with tempfile.NamedTemporaryFile(delete=False, suffix=os.path.splitext(file.filename)[1] or ".jpg") as tmp:
        file.save(tmp.name)
        try:
            # generate embedding
            query_embedding = generate_embedding(tmp.name)

            # compute query color histogram
            try:
                query_color_hist = compute_color_hist_from_image(tmp.name)
            except Exception:
                query_color_hist = None

            # OCR: extract text if pytesseract available
            ocr_text = ""
            ocr_keywords = []
            try:
                if HAS_PYTESSERACT:
                    img = Image.open(tmp.name)
                    ocr_text = pytesseract.image_to_string(img)
                    # simple keywords: alphanumeric words longer than 1 char
                    ocr_keywords = [w.lower() for w in re.findall(r"\w{2,}", ocr_text)]
            except Exception:
                ocr_text = ""
                ocr_keywords = []
        finally:
            try:
                os.unlink(tmp.name)
            except Exception:
                pass

    results = []

    # If FAISS available and index loaded, use ANN retrieval first
    if faiss_index is not None:
        try:
            k = 100
            q = _np.asarray(query_embedding, dtype=_np.float32).reshape(1, -1)
            # index uses inner product on normalized vectors
            D, I = faiss_index.search(q, k)
            ids = []
            sims = []
            for dist, idx in zip(D[0], I[0]):
                if idx < 0:
                    continue
                pid = int(faiss_ids[idx])
                ids.append(pid)
                sims.append(float(dist))

            # fetch product info for retrieved ids preserving order
            if ids:
                # fetch color_hist from product_embeddings (thumbnail entries) and product info
                placeholders = ','.join([':id' + str(i) for i in range(len(ids))])
                params = {('id' + str(i)): ids[i] for i in range(len(ids))}
                products_info = {}
                with engine.connect() as conn:
                    prod_rows = conn.execute(text(f"SELECT id, name, slug, thumbnail FROM products WHERE id IN ({placeholders})"), params).fetchall()
                    for r in prod_rows:
                        products_info[int(r.id)] = {"name": r.name, "slug": r.slug, "thumbnail": r.thumbnail}

                    # try to get color hist from product_embeddings for the thumbnail image (product_image_id = 0)
                    hist_rows = conn.execute(text(f"SELECT product_id, color_hist FROM product_embeddings WHERE product_id IN ({placeholders}) AND product_image_id = 0"), params).fetchall()
                    color_by_id = {}
                    for hr in hist_rows:
                        try:
                            color_by_id[int(hr.product_id)] = np.array(json.loads(hr.color_hist), dtype=np.float32)
                        except Exception:
                            continue

                for pid, sim in zip(ids, sims):
                    info = products_info.get(pid)
                    if not info:
                        continue

                    # compute color similarity
                    color_sim = 0.0
                    if query_color_hist is not None:
                        db_hist = color_by_id.get(pid)
                        if db_hist is not None and db_hist.sum() > 0:
                            # cosine on histograms
                            denom = (np.linalg.norm(query_color_hist) * np.linalg.norm(db_hist)) + 1e-10
                            color_sim = float(np.dot(query_color_hist, db_hist) / denom)

                    # simple OCR->name overlap score will be computed below (text_score)
                    results.append({
                        "product_id": pid,
                        "name": info["name"],
                        "slug": info["slug"],
                        "thumbnail": info["thumbnail"],
                        "embedding_sim": sim,
                        "color_sim": color_sim,
                    })

            # combine scores: embedding, color, and text
            # Normalize embeddings to 0-1 range to match color and text scales
            embs = [r.get('embedding_sim', 0.0) for r in results]
            min_emb = min(embs) if embs else 0.0
            max_emb = max(embs) if embs else 1.0
            range_emb = (max_emb - min_emb) if (max_emb - min_emb) > 0 else 1.0

            EMB_W = 0.75
            COLOR_W = 0.10
            TEXT_W = 0.15

            def text_score_for(name, keywords):
                if not keywords or not name:
                    return 0.0
                name_toks = re.findall(r"\w{2,}", str(name).lower())
                if not name_toks:
                    return 0.0
                kws = set(keywords)
                match = sum(1 for t in name_toks if t in kws)
                return float(match) / float(len(name_toks))

            final_results = []
            for r in results:
                ts = text_score_for(r.get('name'), ocr_keywords)
                
                # Normalize emb
                raw_emb = r.get('embedding_sim', 0.0)
                norm_emb = (raw_emb - min_emb) / range_emb
                
                col = r.get('color_sim', 0.0)
                final_score = EMB_W * norm_emb + COLOR_W * col + TEXT_W * ts
                
                # boost strongly if text exact match appears in name
                if ts > 0.5:
                    final_score += 0.2
                    
                # expose 'similarity' for backward compatibility (Laravel expects this)
                entry = {**r, 'text_score': ts, 'score': final_score, 'raw_emb': float(raw_emb), 'norm_emb': float(norm_emb)}
                entry['similarity'] = float(final_score)
                final_results.append(entry)

            final_results = sorted(final_results, key=lambda x: x['score'], reverse=True)[:12]
            return jsonify({"success": True, "results": final_results, "ocr_text": ocr_text, "ocr_keywords": ocr_keywords})
        except Exception as e:
            print('FAISS retrieval failed:', e)

    # Fallback: brute-force similarity across stored embeddings in DB
    with engine.connect() as conn:
        rows = conn.execute(text("""
            SELECT
                pe.product_id,
                pe.embedding,
                p.name,
                p.slug,
                p.thumbnail
            FROM product_embeddings pe
            JOIN products p
                ON p.id = pe.product_id
        """)).fetchall()

        for row in rows:
            try:
                db_embedding = np.array(json.loads(row.embedding), dtype=np.float32)
            except Exception:
                continue

            similarity = cosine_similarity(query_embedding, db_embedding)

            results.append({
                "product_id": row.product_id,
                "name": row.name,
                "slug": row.slug,
                "thumbnail": row.thumbnail,
                "similarity": similarity,
            })

    results = sorted(results, key=lambda x: x["similarity"], reverse=True)[:12]

    return jsonify({"success": True, "results": results, "ocr_text": ocr_text, "ocr_keywords": ocr_keywords})


@app.route("/chat-rag", methods=["POST"])
def chat_rag():
    data = request.json or {}
    messages = data.get('messages', [])
    
    if not messages:
        return jsonify({"success": False, "error": "No messages provided"}), 400
        
    last_user_msg = ""
    for msg in reversed(messages):
        if msg.get('role') == 'user':
            last_user_msg = msg.get('content', '')
            break
            
    if not last_user_msg:
        return jsonify({"success": False, "error": "No user message found"}), 400

    # 1. Search DB using CLIP features of the user's text
    results = []
    recipes = []
    if faiss_index is not None and faiss_ids is not None:
        try:
            inputs = processor(text=[last_user_msg], return_tensors="pt", padding=True)
            with torch.no_grad():
                text_features = model.get_text_features(**inputs)
            text_features = text_features / text_features.norm(dim=-1, keepdim=True)
            emb_np = text_features.cpu().numpy()
            
            distances, indices = faiss_index.search(emb_np, 4) # Top 4
            found_ids = [int(faiss_ids[idx]) for idx in indices[0] if idx != -1]
            
            if found_ids:
                placeholders = ', '.join(['%s'] * len(found_ids))
                query = text(f"SELECT id, name, min_price as price, slug FROM products WHERE id IN ({placeholders}) AND is_active = 1")
                with engine.connect() as conn:
                    rows = conn.execute(query, tuple(found_ids)).fetchall()
                    for row in rows:
                        results.append({
                            "id": row[0],
                            "name": row[1],
                            "price": float(row[2]) if row[2] else 0,
                            "slug": row[3] if len(row) > 3 else str(row[0])
                        })
                    
                    recipe_query = text(f"SELECT DISTINCT r.title, r.slug FROM recipes r JOIN recipe_ingredients ri ON r.id = ri.recipe_id WHERE ri.product_id IN ({placeholders}) AND r.is_published = 1 LIMIT 3")
                    r_rows = conn.execute(recipe_query, tuple(found_ids)).fetchall()
                    for rr in r_rows:
                        recipes.append({"title": rr[0], "slug": rr[1]})
        except Exception as e:
            print("FAISS error in chat-rag:", e)
            
    # Fallback: Simple keyword search in DB if no results from FAISS
    if not results:
        try:
            # Extract potential keywords (very basic)
            keywords = [word for word in last_user_msg.split() if len(word) > 2 and word.lower() not in ['tôi', 'muốn', 'mua', 'cho', 'xin', 'cái', 'làm', 'sao']]
            if keywords:
                with engine.connect() as conn:
                    for kw in keywords:
                        kw_like = f"%{kw}%"
                        q = text("SELECT id, name, min_price as price, slug FROM products WHERE is_active = 1 AND name LIKE :kw LIMIT 4")
                        rows = conn.execute(q, {"kw": kw_like}).fetchall()
                        for row in rows:
                            if not any(r['id'] == row[0] for r in results):
                                results.append({
                                    "id": row[0],
                                    "name": row[1],
                                    "price": float(row[2]) if row[2] else 0,
                                    "slug": row[3] if len(row) > 3 else str(row[0])
                                })
                        if len(results) >= 4:
                            break
                            
                # Popular recipes fallback
                if not recipes:
                    with engine.connect() as conn:
                        pop_query = text("SELECT title, slug FROM recipes WHERE is_published = 1 ORDER BY view_count DESC LIMIT 2")
                        pop_rows = conn.execute(pop_query).fetchall()
                        for pr in pop_rows:
                            recipes.append({"title": pr[0], "slug": pr[1]})
        except Exception as e:
            print("Search error:", e)

    # 2. Build system prompt
    context_str = "Danh sách sản phẩm tìm được:\n"
    if results:
        for p in results:
            context_str += f"- Sản phẩm: {p['name']} (Giá: {p['price']}đ, Link: /products/{p['slug']})\n"
    else:
        context_str += "Không tìm thấy sản phẩm cụ thể nào phù hợp.\n"

    if recipes:
        context_str += "\nDanh sách món ăn/công thức có thể gợi ý:\n"
        for r in recipes:
            context_str += f"- Món ăn: {r['title']} (Link: /recipes/{r['slug']})\n"

    system_prompt = (
        "Bạn là trợ lý ảo thân thiện của SFood. "
        "Bạn trả lời bằng tiếng Việt, ngắn gọn, tự nhiên. "
        "Nếu người dùng muốn mua nguyên liệu, hãy gợi ý sản phẩm. Nếu người dùng hỏi 'hôm nay ăn gì' hoặc muốn nấu ăn, hãy gợi ý các món ăn/công thức. "
        "ĐẶC BIỆT: Nếu người dùng yêu cầu 'mở', 'xem chi tiết', 'chuyển hướng' đến một sản phẩm hoặc món ăn, HÃY CHÈN CHUỖI [REDIRECT:slug] vào cuối câu trả lời (với slug là mã link của sản phẩm/món ăn đó). "
        "Dưới đây là ngữ cảnh từ cơ sở dữ liệu: \n" + context_str
    )

    # Prepend system prompt to messages
    full_messages = [{"role": "system", "content": system_prompt}] + messages

    # 3. Generate response using LLM
    if llm_pipeline is None:
        return jsonify({"success": False, "error": "LLM not loaded", "results": results}), 500
        
    try:
        prompt = llm_tokenizer.apply_chat_template(full_messages, tokenize=False, add_generation_prompt=True)
        outputs = llm_pipeline(prompt, max_new_tokens=512, do_sample=True, temperature=0.7, top_p=0.9)
        generated_text = outputs[0]["generated_text"]
        
        # Extract the assistant's reply part
        # Qwen uses <|im_start|>assistant ...
        reply = generated_text.split("<|im_start|>assistant\n")[-1].split("<|im_end|>")[0].strip()
        
        return jsonify({"success": True, "reply": reply, "results": results, "recipes": recipes})
    except Exception as e:
        print("LLM Error:", e)
        return jsonify({"success": False, "error": str(e), "results": results, "recipes": recipes}), 500


# =========================
# HEALTH
# =========================

@app.route("/health")
def health():
    return jsonify({"status": "ok"})


@app.route("/embed_single", methods=["POST"])
def embed_single():
    # endpoint used by Laravel jobs to upload a single image and store embedding
    product_id = request.form.get('product_id')
    product_image_id = request.form.get('product_image_id', 0)
    image_path_field = request.form.get('image_path')

    if 'image' not in request.files:
        return jsonify({'success': False, 'error': 'No image uploaded'}), 400

    file = request.files['image']
    if file.filename == "":
        return jsonify({'success': False, 'error': 'Empty filename'}), 400

    # save temp file
    with tempfile.NamedTemporaryFile(delete=False, suffix=os.path.splitext(file.filename)[1] or '.jpg') as tmp:
        file.save(tmp.name)
        tmp_path = tmp.name

    try:
        emb = generate_embedding(tmp_path)
        try:
            color_hist = compute_color_hist_from_image(tmp_path).tolist()
        except Exception:
            color_hist = None

        # upsert into product_embeddings
        with engine.begin() as conn:
            try:
                conn.execute(text("""
                    INSERT INTO product_embeddings
                    (product_id, product_image_id, image_path, embedding, color_hist, created_at, updated_at)
                    VALUES
                    (:product_id, :product_image_id, :image_path, :embedding, :color_hist, NOW(), NOW())
                    ON DUPLICATE KEY UPDATE
                        embedding = :embedding,
                        image_path = :image_path,
                        color_hist = :color_hist,
                        updated_at = NOW()
                """),
                {
                    'product_id': int(product_id) if product_id is not None else 0,
                    'product_image_id': int(product_image_id) if product_image_id is not None else 0,
                    'image_path': image_path_field,
                    'embedding': json.dumps(emb.tolist() if hasattr(emb, 'tolist') else emb),
                    'color_hist': json.dumps(color_hist) if color_hist is not None else None,
                })
            except Exception:
                # fallback to insertion without color_hist (older schemas)
                try:
                    conn.execute(text("""
                        INSERT INTO product_embeddings
                        (product_id, product_image_id, image_path, embedding, created_at, updated_at)
                        VALUES
                        (:product_id, :product_image_id, :image_path, :embedding, NOW(), NOW())
                        ON DUPLICATE KEY UPDATE
                            embedding = :embedding,
                            image_path = :image_path,
                            updated_at = NOW()
                    """),
                    {
                        'product_id': int(product_id) if product_id is not None else 0,
                        'product_image_id': int(product_image_id) if product_image_id is not None else 0,
                        'image_path': image_path_field,
                        'embedding': json.dumps(emb.tolist() if hasattr(emb, 'tolist') else emb),
                    })
                except Exception as e:
                    return jsonify({'success': False, 'error': str(e)}), 500

        # Rebuild and reload FAISS index
        import subprocess
        try:
            subprocess.run(["python", "faiss_build.py"], cwd=os.path.dirname(os.path.abspath(__file__)), check=True)
            global faiss_index, faiss_ids
            if HAS_FAISS and os.path.exists(FAISS_INDEX_PATH) and os.path.exists(FAISS_IDS_PATH):
                faiss_index = faiss.read_index(FAISS_INDEX_PATH)
                faiss_ids = _np.load(FAISS_IDS_PATH)
                print('Reloaded FAISS index with', faiss_ids.shape[0], 'ids')
        except Exception as e:
            print("Failed to rebuild/reload FAISS:", e)

        return jsonify({'success': True, 'product_id': int(product_id) if product_id is not None else None, 'product_image_id': int(product_image_id) if product_image_id is not None else None})
    finally:
        try:
            os.unlink(tmp_path)
        except Exception:
            pass

@app.route("/embed_recipe", methods=["POST"])
def embed_recipe():
    recipe_id = request.form.get('recipe_id')
    image_path_field = request.form.get('image_path')

    if 'image' not in request.files:
        return jsonify({'success': False, 'error': 'No image uploaded'}), 400

    file = request.files['image']
    if file.filename == "":
        return jsonify({'success': False, 'error': 'Empty filename'}), 400

    with tempfile.NamedTemporaryFile(delete=False, suffix=os.path.splitext(file.filename)[1] or '.jpg') as tmp:
        file.save(tmp.name)
        tmp_path = tmp.name

    try:
        emb = generate_embedding(tmp_path)
        try:
            color_hist = compute_color_hist_from_image(tmp_path).tolist()
        except Exception:
            color_hist = None

        with engine.begin() as conn:
            try:
                conn.execute(text("""
                    INSERT INTO recipe_embeddings
                    (recipe_id, image_path, embedding, color_hist, created_at, updated_at)
                    VALUES
                    (:recipe_id, :image_path, :embedding, :color_hist, NOW(), NOW())
                    ON DUPLICATE KEY UPDATE
                        embedding = :embedding,
                        image_path = :image_path,
                        color_hist = :color_hist,
                        updated_at = NOW()
                """),
                {
                    'recipe_id': int(recipe_id) if recipe_id is not None else 0,
                    'image_path': image_path_field,
                    'embedding': json.dumps(emb.tolist() if hasattr(emb, 'tolist') else emb),
                    'color_hist': json.dumps(color_hist) if color_hist is not None else None,
                })
            except Exception as e:
                return jsonify({'success': False, 'error': str(e)}), 500

        import subprocess
        try:
            subprocess.run(["python", "faiss_build.py"], cwd=os.path.dirname(os.path.abspath(__file__)), check=True)
            global faiss_recipe_index, faiss_recipe_ids
            if HAS_FAISS and os.path.exists(FAISS_RECIPE_INDEX_PATH) and os.path.exists(FAISS_RECIPE_IDS_PATH):
                faiss_recipe_index = faiss.read_index(FAISS_RECIPE_INDEX_PATH)
                faiss_recipe_ids = _np.load(FAISS_RECIPE_IDS_PATH)
                print('Reloaded FAISS recipe index with', faiss_recipe_ids.shape[0], 'ids')
        except Exception as e:
            print("Failed to rebuild/reload FAISS:", e)

        return jsonify({'success': True, 'recipe_id': int(recipe_id) if recipe_id is not None else None})
    finally:
        try:
            os.unlink(tmp_path)
        except Exception:
            pass

@app.route("/search_recipe", methods=["POST"])
def search_recipe():
    if "image" not in request.files:
        return jsonify({"error": "No image"}), 400

    file = request.files["image"]
    if file.filename == "":
        return jsonify({"error": "Empty filename"}), 400

    with tempfile.NamedTemporaryFile(delete=False, suffix=os.path.splitext(file.filename)[1] or ".jpg") as tmp:
        file.save(tmp.name)
        try:
            query_embedding = generate_embedding(tmp.name)
        finally:
            try:
                os.unlink(tmp.name)
            except Exception:
                pass

    results = []
    if faiss_recipe_index is not None:
        try:
            k = 100
            q = _np.asarray(query_embedding, dtype=_np.float32).reshape(1, -1)
            D, I = faiss_recipe_index.search(q, k)
            ids = []
            sims = []
            for dist, idx in zip(D[0], I[0]):
                if idx < 0:
                    continue
                pid = int(faiss_recipe_ids[idx])
                ids.append(pid)
                sims.append(float(dist))

            if ids:
                placeholders = ','.join([':id' + str(i) for i in range(len(ids))])
                params = {('id' + str(i)): ids[i] for i in range(len(ids))}
                recipes_info = {}
                with engine.connect() as conn:
                    rows = conn.execute(text(f"SELECT id, title as name, slug, thumbnail FROM recipes WHERE id IN ({placeholders}) AND is_published=1"), params).fetchall()
                    for r in rows:
                        recipes_info[int(r.id)] = {"name": r.name, "slug": r.slug, "thumbnail": r.thumbnail}

                for pid, sim in zip(ids, sims):
                    info = recipes_info.get(pid)
                    if not info:
                        continue
                    results.append({
                        "recipe_id": pid,
                        "name": info["name"],
                        "slug": info["slug"],
                        "thumbnail": info["thumbnail"],
                        "score": sim,
                        "similarity": sim,
                    })

            results = sorted(results, key=lambda x: x['score'], reverse=True)[:12]
            return jsonify({"success": True, "results": results})
        except Exception as e:
            print('FAISS retrieval failed:', e)
            return jsonify({"success": False, "error": str(e)}), 500
    
    # Fallback: FAISS recipe index not loaded, return all published recipes from DB
    try:
        with engine.connect() as conn:
            rows = conn.execute(text("SELECT id, title as name, slug, thumbnail FROM recipes WHERE is_published = 1 ORDER BY view_count DESC LIMIT 6")).fetchall()
            for r in rows:
                results.append({
                    "recipe_id": int(r.id),
                    "name": r.name,
                    "slug": r.slug,
                    "thumbnail": r.thumbnail,
                    "score": 0,
                    "similarity": 0,
                })
        return jsonify({"success": True, "results": results})
    except Exception as e:
        print('DB fallback failed:', e)
        return jsonify({"success": False, "error": "FAISS index not loaded and DB fallback failed"}), 500


@app.route("/search_global", methods=["POST"])
def search_global():
    if "image" not in request.files:
        return jsonify({"error": "No image"}), 400

    file = request.files["image"]
    if file.filename == "":
        return jsonify({"error": "Empty filename"}), 400

    with tempfile.NamedTemporaryFile(delete=False, suffix=os.path.splitext(file.filename)[1] or ".jpg") as tmp:
        file.save(tmp.name)
        try:
            query_embedding = generate_embedding(tmp.name)
        finally:
            try:
                os.unlink(tmp.name)
            except Exception:
                pass

    response_data = {"success": True, "products": [], "recipes": [], "label": "Thực phẩm"}

    # Search Products
    if faiss_index is not None:
        try:
            k = 100
            q = _np.asarray(query_embedding, dtype=_np.float32).reshape(1, -1)
            D, I = faiss_index.search(q, k)
            ids = []
            sims = []
            for dist, idx in zip(D[0], I[0]):
                if idx < 0:
                    continue
                pid = int(faiss_ids[idx])
                ids.append(pid)
                sims.append(float(dist))

            if ids:
                placeholders = ','.join([':id' + str(i) for i in range(len(ids))])
                params = {('id' + str(i)): ids[i] for i in range(len(ids))}
                products_info = {}
                with engine.connect() as conn:
                    rows = conn.execute(text(f"SELECT id, name FROM products WHERE id IN ({placeholders})"), params).fetchall()
                    for r in rows:
                        products_info[int(r.id)] = {"name": r.name}

                for pid, sim in zip(ids, sims):
                    info = products_info.get(pid)
                    if not info:
                        continue
                    response_data["products"].append({
                        "product_id": pid,
                        "name": info["name"],
                        "similarity": sim,
                    })
            response_data["products"] = sorted(response_data["products"], key=lambda x: x['similarity'], reverse=True)[:12]
            if len(response_data["products"]) > 0:
                response_data["label"] = response_data["products"][0]["name"]
        except Exception as e:
            print('FAISS product retrieval failed:', e)

    # Search Recipes
    if faiss_recipe_index is not None:
        try:
            k = 100
            q = _np.asarray(query_embedding, dtype=_np.float32).reshape(1, -1)
            D, I = faiss_recipe_index.search(q, k)
            ids = []
            sims = []
            for dist, idx in zip(D[0], I[0]):
                if idx < 0:
                    continue
                pid = int(faiss_recipe_ids[idx])
                ids.append(pid)
                sims.append(float(dist))

            if ids:
                response_data["recipes"] = [{"recipe_id": pid, "similarity": sim} for pid, sim in zip(ids, sims)]
                response_data["recipes"] = sorted(response_data["recipes"], key=lambda x: x['similarity'], reverse=True)[:12]
        except Exception as e:
            print('FAISS recipe retrieval failed:', e)
    
    return jsonify(response_data)

# =========================
# RUN
# =========================

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000, debug=True)
