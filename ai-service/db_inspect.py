from sqlalchemy import create_engine, text
DB_URL = "mysql+pymysql://root:mysql@127.0.0.1:3306/sfood"
engine = create_engine(DB_URL)

with engine.connect() as conn:
    prod_cnt = conn.execute(text("SELECT COUNT(*) AS c FROM products")).fetchone().c
    thumb_cnt = conn.execute(text("SELECT COUNT(*) AS c FROM products WHERE thumbnail IS NOT NULL")).fetchone().c
    print('products_total', prod_cnt)
    print('products_with_thumbnail', thumb_cnt)

    # product_images table
    try:
        imgs = conn.execute(text("SELECT image FROM product_images LIMIT 20")).fetchall()
        print('product_images_rows_sample', len(imgs))
        for i in imgs[:10]:
            print('-', i.image)
    except Exception as e:
        print('product_images table not found or error:', str(e))

    # count embeddings
    try:
        emb_total = conn.execute(text('SELECT COUNT(*) AS c FROM product_embeddings')).fetchone().c
        print('product_embeddings_total', emb_total)
    except Exception as e:
        print('product_embeddings table missing or error:', str(e))
