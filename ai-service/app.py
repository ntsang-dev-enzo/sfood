from flask import Flask, request, jsonify
from flask_cors import CORS

from transformers import CLIPModel, CLIPProcessor
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
                if HAS_PYTESSACT:
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
            EMB_W = 0.6
            COLOR_W = 0.2
            TEXT_W = 0.2

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
                emb = r.get('embedding_sim', 0.0)
                col = r.get('color_sim', 0.0)
                final_score = EMB_W * emb + COLOR_W * col + TEXT_W * ts
                # boost strongly if text exact match appears in name
                if ts > 0.8:
                    final_score += 0.15
                # expose 'similarity' for backward compatibility (Laravel expects this)
                entry = {**r, 'text_score': ts, 'score': final_score}
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

        return jsonify({'success': True, 'product_id': int(product_id) if product_id is not None else None, 'product_image_id': int(product_image_id) if product_image_id is not None else None})
    finally:
        try:
            os.unlink(tmp_path)
        except Exception:
            pass


# =========================
# RUN
# =========================

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000, debug=True)
