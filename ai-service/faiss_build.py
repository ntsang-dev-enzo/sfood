import os
import json
import numpy as np
from sqlalchemy import create_engine, text

# Build a FAISS index from product_embeddings table
DB_URL = "mysql+pymysql://root:mysql@127.0.0.1:3306/sfood"
engine = create_engine(DB_URL)

OUT_DIR = os.path.join(os.path.dirname(__file__), 'index')
os.makedirs(OUT_DIR, exist_ok=True)
INDEX_FILE = os.path.join(OUT_DIR, 'product_index.faiss')
IDS_FILE = os.path.join(OUT_DIR, 'product_ids.npy')
EMB_FILE = os.path.join(OUT_DIR, 'product_embeddings.npy')

try:
    import faiss
except Exception as e:
    print('faiss not available:', e)
    raise

with engine.connect() as conn:
    rows = conn.execute(text("SELECT product_id, embedding FROM product_embeddings")).fetchall()

    ids = []
    embs = []
    for r in rows:
        try:
            e = json.loads(r.embedding)
            ids.append(int(r.product_id))
            embs.append(np.array(e, dtype=np.float32))
        except Exception:
            continue

    if len(embs) == 0:
        print('No embeddings found to build index')
        raise SystemExit(1)

    embs = np.stack(embs, axis=0)

    # ensure normalized
    norms = np.linalg.norm(embs, axis=1, keepdims=True)
    norms[norms == 0] = 1.0
    embs = embs / norms

    d = embs.shape[1]
    print(f'Building FAISS index with {embs.shape[0]} vectors, dim={d}')

    index = faiss.IndexFlatIP(d)  # inner product for cosine on normalized vectors
    index.add(embs)

    faiss.write_index(index, INDEX_FILE)
    np.save(IDS_FILE, np.array(ids, dtype=np.int64))
    np.save(EMB_FILE, embs)

    print('Index written to', INDEX_FILE)
    print('IDs written to', IDS_FILE)

