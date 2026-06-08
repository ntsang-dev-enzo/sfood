from sqlalchemy import create_engine, text
DB_URL = "mysql+pymysql://root:mysql@127.0.0.1:3306/sfood"
engine = create_engine(DB_URL)

with engine.begin() as conn:
    dupes = conn.execute(text("""
        SELECT product_id, COUNT(*) AS cnt
        FROM product_embeddings
        GROUP BY product_id
        HAVING cnt > 1
    """)).fetchall()

    print('Found dupes:', len(dupes))
    for d in dupes:
        pid = d.product_id
        keep = conn.execute(text("SELECT id FROM product_embeddings WHERE product_id = :pid ORDER BY updated_at DESC, id DESC LIMIT 1"), {'pid': pid}).fetchone()
        keep_id = keep.id if keep else None
        if keep_id:
            res = conn.execute(text("DELETE FROM product_embeddings WHERE product_id = :pid AND id != :keep"), {'pid': pid, 'keep': keep_id})
            print(f"Cleaned product {pid}, kept id {keep_id}")

    # Try to add unique index
    try:
        conn.execute(text("ALTER TABLE product_embeddings ADD UNIQUE KEY ux_product_id (product_id)"))
        print('Added unique key on product_id')
    except Exception as e:
        print('Failed to add unique key:', e)
