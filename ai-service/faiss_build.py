import os
import json
import numpy as np
from sqlalchemy import create_engine, text

DB_URL = "mysql+pymysql://root:mysql@127.0.0.1:3306/sfood"
engine = create_engine(DB_URL)

OUT_DIR = os.path.join(os.path.dirname(__file__), 'index')
os.makedirs(OUT_DIR, exist_ok=True)

try:
    import faiss
except Exception as e:
    print('faiss not available:', e)
    raise

def build_index(table_name, id_column, prefix):
    INDEX_FILE = os.path.join(OUT_DIR, f'{prefix}_index.faiss')
    IDS_FILE = os.path.join(OUT_DIR, f'{prefix}_ids.npy')
    EMB_FILE = os.path.join(OUT_DIR, f'{prefix}_embeddings.npy')

    with engine.connect() as conn:
        rows = conn.execute(text(f"SELECT {id_column}, embedding FROM {table_name}")).fetchall()

        ids = []
        embs = []
        for r in rows:
            try:
                e = json.loads(r.embedding)
                ids.append(int(getattr(r, id_column)))
                embs.append(np.array(e, dtype=np.float32))
            except Exception:
                continue

        if len(embs) == 0:
            print(f'No embeddings found for {table_name}')
            return

        embs = np.stack(embs, axis=0)
        norms = np.linalg.norm(embs, axis=1, keepdims=True)
        norms[norms == 0] = 1.0
        embs = embs / norms

        d = embs.shape[1]
        print(f'Building FAISS index for {table_name} with {embs.shape[0]} vectors, dim={d}')

        index = faiss.IndexFlatIP(d)
        index.add(embs)

        faiss.write_index(index, INDEX_FILE)
        np.save(IDS_FILE, np.array(ids, dtype=np.int64))
        np.save(EMB_FILE, embs)

        print(f'Index written to {INDEX_FILE}')

build_index('product_embeddings', 'product_id', 'product')
build_index('recipe_embeddings', 'recipe_id', 'recipe')


