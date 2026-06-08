import json
import os
import numpy as np
import torch
from PIL import Image
from sqlalchemy import create_engine, text
from transformers import CLIPModel, CLIPProcessor

# =========================
# MYSQL
# =========================
DB_URL = "mysql+pymysql://root:mysql@127.0.0.1:3306/sfood"
engine = create_engine(DB_URL)

# =========================
# CLIP MODEL
# =========================
MODEL_NAME = "openai/clip-vit-large-patch14"

print("Loading CLIP model...")
model = CLIPModel.from_pretrained(MODEL_NAME)
processor = CLIPProcessor.from_pretrained(MODEL_NAME)
print("CLIP loaded")


# =========================
# EMBEDDING FUNCTION
# =========================
def generate_embedding(image_path):

    image = Image.open(image_path).convert("RGB")

    inputs = processor(images=image, return_tensors="pt")

    with torch.no_grad():

        features = model.get_image_features(**inputs)

    # `features` can be a raw Tensor or a model output object
    if isinstance(features, torch.Tensor):
        emb_tensor = features
    elif hasattr(features, "pooler_output"):
        emb_tensor = features.pooler_output
    elif isinstance(features, dict) and "pooler_output" in features:
        emb_tensor = features["pooler_output"]
    elif hasattr(features, "last_hidden_state"):
        # fall back to mean pooling over sequence dimension
        emb_tensor = features.last_hidden_state.mean(dim=1)
    else:
        raise ValueError(f"Unexpected features type: {type(features)}")

    emb = emb_tensor.squeeze().cpu().numpy()

    # guard against zero-vector
    norm = np.linalg.norm(emb)
    if norm == 0:
        raise ValueError("Zero vector embedding for image: %s" % image_path)

    emb = emb / norm
    emb = emb.astype(np.float32)

    return emb.tolist()


# =========================
# GENERATE EMBEDDINGS
# =========================
with engine.begin() as conn:
    # Ensure the target table exists
    conn.execute(text("""
        CREATE TABLE IF NOT EXISTS product_embeddings (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            product_id BIGINT UNSIGNED NOT NULL,
            product_image_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            image_path VARCHAR(1024) NULL,
            embedding LONGTEXT NOT NULL,
            created_at DATETIME,
            updated_at DATETIME,
            UNIQUE KEY ux_product_image (product_id, product_image_id),
            INDEX (product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    """))

    # If table existed with old schema, attempt safe migrations: add columns/indexes
    try:
        conn.execute(text("ALTER TABLE product_embeddings ADD COLUMN IF NOT EXISTS product_image_id BIGINT UNSIGNED NOT NULL DEFAULT 0;"))
    except Exception:
        # Some MySQL versions don't support IF NOT EXISTS for ADD COLUMN; ignore if already present
        try:
            conn.execute(text("ALTER TABLE product_embeddings ADD COLUMN product_image_id BIGINT UNSIGNED NOT NULL DEFAULT 0;"))
        except Exception:
            pass

    try:
        conn.execute(text("ALTER TABLE product_embeddings ADD COLUMN IF NOT EXISTS image_path VARCHAR(1024) NULL;"))
    except Exception:
        try:
            conn.execute(text("ALTER TABLE product_embeddings ADD COLUMN image_path VARCHAR(1024) NULL;"))
        except Exception:
            pass

    # Ensure unique index on (product_id, product_image_id). Drop old ux_product_id if present.
    try:
        conn.execute(text("ALTER TABLE product_embeddings DROP INDEX ux_product_id;"))
    except Exception:
        pass

    try:
        conn.execute(text("CREATE UNIQUE INDEX ux_product_image ON product_embeddings (product_id, product_image_id);"))
    except Exception:
        pass

    # Gather all images: product thumbnails and product_images.image_path
    rows = conn.execute(text("""
        SELECT id AS product_id, thumbnail AS image_path, 0 AS product_image_id
        FROM products
        WHERE thumbnail IS NOT NULL
        UNION ALL
        SELECT product_id, image_path, id AS product_image_id
        FROM product_images
        WHERE image_path IS NOT NULL
    """)).fetchall()

    for r in rows:
        pid = int(r.product_id)
        pimgid = int(r.product_image_id) if r.product_image_id is not None else 0
        rel_path = r.image_path

        image_path = os.path.join("..", "storage", "app", "public", rel_path)

        if not os.path.exists(image_path):
            print("Image not found:", image_path)
            continue

        try:
            print("Embedding:", image_path)
            embedding = generate_embedding(image_path)

            conn.execute(
                text("""
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
                    "product_id": pid,
                    "product_image_id": pimgid,
                    "image_path": rel_path,
                    "embedding": json.dumps(embedding),
                },
            )

            print(f"Embedded product {pid} image {pimgid}")

        except Exception as e:
            print("Failed:", e)
