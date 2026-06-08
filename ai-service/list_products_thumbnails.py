from sqlalchemy import create_engine, text
import os
DB_URL = "mysql+pymysql://root:mysql@127.0.0.1:3306/sfood"
engine = create_engine(DB_URL)

base = os.path.join('..', 'storage', 'app', 'public')

with engine.connect() as conn:
    rows = conn.execute(text("SELECT id, thumbnail FROM products WHERE thumbnail IS NOT NULL LIMIT 200"))
    total = 0
    found = 0
    samples = []
    for r in rows:
        total += 1
        path = os.path.join(base, r.thumbnail)
        exists = os.path.exists(path)
        if exists:
            found += 1
        samples.append((r.id, r.thumbnail, exists))

    print('Products with thumbnail:', total)
    print('Files found on disk:', found)
    print('\nSample rows:')
    for s in samples[:50]:
        print(s)
