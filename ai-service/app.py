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
                placeholders = ','.join([':id' + str(i) for i in range(len(ids))])
                params = {('id' + str(i)): ids[i] for i in range(len(ids))}
                rows = []
                with engine.connect() as conn:
                    rows = conn.execute(text(f"SELECT id, name, slug, thumbnail FROM products WHERE id IN ({placeholders})"), params).fetchall()

                row_by_id = {r.id: r for r in rows}
                for pid, sim in zip(ids, sims):
                    r = row_by_id.get(pid)
                    if not r:
                        continue
                    results.append({
                        "product_id": pid,
                        "name": r.name,
                        "slug": r.slug,
                        "thumbnail": r.thumbnail,
                        "similarity": sim,
                    })

            # limit and return top results
            results = [r for r in results if r]
            results = results[:12]
            return jsonify({"success": True, "results": results, "ocr_text": ocr_text, "ocr_keywords": ocr_keywords})
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


# =========================
# RUN
# =========================

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000, debug=True)
