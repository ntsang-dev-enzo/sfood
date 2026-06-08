from sqlalchemy import create_engine, text
DB_URL = "mysql+pymysql://root:mysql@127.0.0.1:3306/sfood"
engine = create_engine(DB_URL)

with engine.connect() as conn:
    rows = conn.execute(text("""
        SELECT pe.product_id, p.name, COUNT(*) AS cnt
        FROM product_embeddings pe
        LEFT JOIN products p ON p.id = pe.product_id
        GROUP BY pe.product_id
        ORDER BY cnt DESC
        LIMIT 50
    """)).fetchall()

    print("product_id | name | count")
    for r in rows:
        print(r.product_id, "|", r.name, "|", r.cnt)

    total = conn.execute(text("SELECT COUNT(*) AS total FROM product_embeddings")).fetchone()
    unique = conn.execute(text("SELECT COUNT(DISTINCT product_id) AS unique_count FROM product_embeddings")).fetchone()
    print('\nTotal embeddings:', total.total)
    print('Unique product_ids with embeddings:', unique.unique_count)

    # Show duplicates for top product if any
    top = rows[0].product_id if rows else None
    if top:
        samples = conn.execute(text("SELECT id, embedding, created_at FROM product_embeddings WHERE product_id = :pid LIMIT 5"), {"pid": top}).fetchall()
        print('\nSample embeddings for product', top)
        for s in samples:
            print(s.id, s.created_at, 'len=', len(s.embedding) if s.embedding else 0)
