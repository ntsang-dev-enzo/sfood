<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('product_embeddings')->truncate();
        DB::table('product_images')->truncate();
        DB::table('product_variants')->truncate();
        DB::table('products')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ── Tạo / lấy categories ─────────────────────────────────────
        $categoryMap = $this->upsertCategories();

        // ── Dữ liệu sản phẩm ─────────────────────────────────────────
        $products = $this->productData();

        $now = Carbon::now();
        $productId = 1;

        foreach ($products as $p) {
            $slug = Str::slug($p['name']) . '-' . $productId;
            $catId = $categoryMap[$p['category']];

            // ----- products -----
            DB::table('products')->insert([
                'id'          => $productId,
                'category_id' => $catId,
                'name'        => $p['name'],
                'name_en'     => $p['name_en'],
                'slug'        => $slug,
                'description' => $p['description'] ?? null,
                'description_en' => $p['description_en'] ?? null,
                'min_price'   => $p['min_price'],
                'is_hot'      => $p['is_hot'] ?? false,
                'is_new'      => $p['is_new'] ?? false,
                'is_active'   => true,
                'view_count'  => rand(10, 2000),
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);

            // ----- product_variants -----
            $variantOrder = 1;
            foreach ($p['variants'] as $v) {
                DB::table('product_variants')->insert([
                    'product_id'  => $productId,
                    'name'        => $v['name'],
                    'price'       => $v['price'],
                    'sale_price'  => $v['sale_price'] ?? null,
                    'stock'       => $v['stock'] ?? rand(20, 500),
                    'sku'         => strtoupper('SKU-' . $productId . '-' . $variantOrder),
                    'is_active'   => true,
                    'flash_sale_price'     => null,
                    'flash_sale_starts_at' => null,
                    'flash_sale_ends_at'   => null,
                    'flash_sale_stock'     => null,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
                $variantOrder++;
            }

            // ----- product_images (placeholder Picsum) -----
            $seed = $productId * 7;
            DB::table('product_images')->insert([
                'product_id'  => $productId,
                'image_path'  => "https://picsum.photos/seed/{$seed}/600/600",
                'sort_order'  => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
            DB::table('product_images')->insert([
                'product_id'  => $productId,
                'image_path'  => "https://picsum.photos/seed/{$seed}2/600/600",
                'sort_order'  => 2,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);

            $productId++;
        }

        $this->command->info("✅  Đã seed " . count($products) . " sản phẩm thành công.");
    }

    // ─────────────────────────────────────────────────────────────────
    // CATEGORIES
    // ─────────────────────────────────────────────────────────────────
    private function upsertCategories(): array
    {
        $cats = [
            ['name' => 'Rau củ quả',        'name_en' => 'Vegetables & Fruits',    'icon' => '🥦', 'slug' => 'rau-cu-qua'],
            ['name' => 'Thịt & Gia cầm',    'name_en' => 'Meat & Poultry',         'icon' => '🥩', 'slug' => 'thit-gia-cam'],
            ['name' => 'Hải sản',            'name_en' => 'Seafood',                'icon' => '🦐', 'slug' => 'hai-san'],
            ['name' => 'Gia vị & Nước chấm', 'name_en' => 'Spices & Sauces',       'icon' => '🧂', 'slug' => 'gia-vi-nuoc-cham'],
            ['name' => 'Dầu ăn & Bơ',       'name_en' => 'Cooking Oil & Butter',  'icon' => '🫙', 'slug' => 'dau-an-bo'],
            ['name' => 'Gạo & Ngũ cốc',     'name_en' => 'Rice & Grains',          'icon' => '🌾', 'slug' => 'gao-ngu-coc'],
            ['name' => 'Mì & Bún khô',       'name_en' => 'Noodles & Pasta',       'icon' => '🍜', 'slug' => 'mi-bun-kho'],
            ['name' => 'Trứng & Sữa',        'name_en' => 'Eggs & Dairy',           'icon' => '🥚', 'slug' => 'trung-sua'],
            ['name' => 'Đồ hộp & Chế biến', 'name_en' => 'Canned & Processed',    'icon' => '🥫', 'slug' => 'do-hop-che-bien'],
            ['name' => 'Snack & Bánh kẹo',  'name_en' => 'Snacks & Sweets',        'icon' => '🍬', 'slug' => 'snack-banh-keo'],
            ['name' => 'Đồ uống',            'name_en' => 'Beverages',              'icon' => '🥤', 'slug' => 'do-uong'],
            ['name' => 'Thực phẩm khô',      'name_en' => 'Dry Goods',             'icon' => '🫘', 'slug' => 'thuc-pham-kho'],
            ['name' => 'Nấm & Đậu phụ',     'name_en' => 'Mushrooms & Tofu',      'icon' => '🍄', 'slug' => 'nam-dau-phu'],
            ['name' => 'Trái cây',           'name_en' => 'Fruits',                 'icon' => '🍎', 'slug' => 'trai-cay'],
        ];

        $now = Carbon::now();
        $map = [];
        foreach ($cats as $i => $c) {
            $existing = DB::table('categories')->where('slug', $c['slug'])->first();
            if ($existing) {
                $map[$c['name']] = $existing->id;
            } else {
                $id = DB::table('categories')->insertGetId([
                    'name'       => $c['name'],
                    'name_en'    => $c['name_en'],
                    'slug'       => $c['slug'],
                    'icon'       => $c['icon'],
                    'sort_order' => $i + 1,
                    'is_active'  => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $map[$c['name']] = $id;
            }
        }
        return $map;
    }

    // ─────────────────────────────────────────────────────────────────
    // 200 SẢN PHẨM
    // ─────────────────────────────────────────────────────────────────
    private function productData(): array
    {
        return [
            // ═══════════════ RAU CỦ QUẢ (25) ═══════════════
            [
                'category' => 'Rau củ quả',
                'name' => 'Rau muống tươi', 'name_en' => 'Morning Glory',
                'description' => 'Rau muống tươi, giòn, sạch, thu hái mỗi sáng.',
                'description_en' => 'Fresh morning glory, crispy and clean, harvested daily.',
                'min_price' => 8000, 'is_hot' => true,
                'variants' => [
                    ['name' => '300g', 'name_en' => '300g', 'price' => 8000, 'sale_price' => null, 'stock' => 300],
                    ['name' => '500g', 'name_en' => '500g', 'price' => 12000, 'sale_price' => 10000, 'stock' => 200],
                ],
            ],
            [
                'category' => 'Rau củ quả',
                'name' => 'Cải xanh', 'name_en' => 'Green Mustard',
                'description' => 'Cải xanh tươi ngon, phù hợp luộc hoặc xào.',
                'description_en' => 'Fresh green mustard, great for boiling or stir-frying.',
                'min_price' => 9000,
                'variants' => [
                    ['name' => '300g', 'price' => 9000, 'stock' => 250],
                    ['name' => '500g', 'price' => 14000, 'sale_price' => 12000, 'stock' => 180],
                ],
            ],
            [
                'category' => 'Rau củ quả',
                'name' => 'Bắp cải tươi', 'name_en' => 'Fresh Cabbage',
                'description' => 'Bắp cải tươi, lá xanh mướt, giòn ngọt.',
                'description_en' => 'Fresh cabbage, crispy and sweet.',
                'min_price' => 15000,
                'variants' => [
                    ['name' => '500g', 'price' => 15000, 'stock' => 300],
                    ['name' => '1kg',  'price' => 28000, 'sale_price' => 25000, 'stock' => 200],
                ],
            ],
            [
                'category' => 'Rau củ quả',
                'name' => 'Cà rốt', 'name_en' => 'Carrot',
                'description' => 'Cà rốt tươi, ngọt, giàu vitamin A.',
                'description_en' => 'Fresh carrots, sweet, rich in vitamin A.',
                'min_price' => 12000, 'is_hot' => true,
                'variants' => [
                    ['name' => '500g', 'price' => 12000, 'stock' => 400],
                    ['name' => '1kg',  'price' => 22000, 'stock' => 300],
                ],
            ],
            [
                'category' => 'Rau củ quả',
                'name' => 'Khoai tây', 'name_en' => 'Potato',
                'description' => 'Khoai tây tươi, bở, thích hợp kho, chiên, hầm.',
                'description_en' => 'Fresh potatoes, suitable for braising, frying, stewing.',
                'min_price' => 18000, 'is_new' => true,
                'variants' => [
                    ['name' => '500g', 'price' => 18000, 'stock' => 350],
                    ['name' => '1kg',  'price' => 32000, 'sale_price' => 28000, 'stock' => 250],
                    ['name' => '2kg',  'price' => 60000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Rau củ quả',
                'name' => 'Hành tây', 'name_en' => 'Onion',
                'description' => 'Hành tây tươi, cay nhẹ, thích hợp xào, salad.',
                'description_en' => 'Fresh onions, mildly spicy, great for stir-fry or salad.',
                'min_price' => 14000,
                'variants' => [
                    ['name' => '500g', 'price' => 14000, 'stock' => 300],
                    ['name' => '1kg',  'price' => 25000, 'stock' => 200],
                ],
            ],
            [
                'category' => 'Rau củ quả',
                'name' => 'Tỏi tươi', 'name_en' => 'Fresh Garlic',
                'description' => 'Tỏi tươi thơm nồng, cần thiết cho mọi gian bếp.',
                'description_en' => 'Aromatic fresh garlic, essential in every kitchen.',
                'min_price' => 20000, 'is_hot' => true,
                'variants' => [
                    ['name' => '200g', 'price' => 20000, 'stock' => 400],
                    ['name' => '500g', 'price' => 45000, 'sale_price' => 40000, 'stock' => 250],
                ],
            ],
            [
                'category' => 'Rau củ quả',
                'name' => 'Gừng tươi', 'name_en' => 'Fresh Ginger',
                'description' => 'Gừng tươi thơm cay, dùng nấu ăn và pha trà.',
                'description_en' => 'Fresh ginger, spicy aromatic, used for cooking and tea.',
                'min_price' => 15000,
                'variants' => [
                    ['name' => '200g', 'price' => 15000, 'stock' => 350],
                    ['name' => '500g', 'price' => 35000, 'stock' => 200],
                ],
            ],
            [
                'category' => 'Rau củ quả',
                'name' => 'Khổ qua (Mướp đắng)', 'name_en' => 'Bitter Melon',
                'description' => 'Mướp đắng tươi, vị đắng thanh, tốt cho sức khỏe.',
                'description_en' => 'Fresh bitter melon, refreshing bitter taste, great for health.',
                'min_price' => 12000,
                'variants' => [
                    ['name' => '500g', 'price' => 12000, 'stock' => 200],
                    ['name' => '1kg',  'price' => 22000, 'sale_price' => 19000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Rau củ quả',
                'name' => 'Đậu cove', 'name_en' => 'Green Beans',
                'description' => 'Đậu cove tươi, giòn ngon, giàu chất xơ.',
                'description_en' => 'Fresh green beans, crispy and fiber-rich.',
                'min_price' => 15000,
                'variants' => [
                    ['name' => '300g', 'price' => 15000, 'stock' => 250],
                    ['name' => '500g', 'price' => 22000, 'stock' => 180],
                ],
            ],
            [
                'category' => 'Rau củ quả',
                'name' => 'Ngò rí (Mùi tàu)', 'name_en' => 'Cilantro / Culantro',
                'description' => 'Ngò rí thơm, dùng trang trí và nêm nếm món ăn.',
                'description_en' => 'Aromatic cilantro for garnishing and seasoning.',
                'min_price' => 5000,
                'variants' => [
                    ['name' => '100g', 'price' => 5000, 'stock' => 500],
                    ['name' => '200g', 'price' => 9000, 'stock' => 300],
                ],
            ],
            [
                'category' => 'Rau củ quả',
                'name' => 'Rau thơm hỗn hợp', 'name_en' => 'Mixed Fresh Herbs',
                'description' => 'Combo rau thơm: húng quế, kinh giới, tía tô.',
                'description_en' => 'Herb mix: basil, marjoram, perilla.',
                'min_price' => 10000,
                'variants' => [
                    ['name' => 'Gói 200g', 'name_en' => '200g pack', 'price' => 10000, 'stock' => 200],
                    ['name' => 'Gói 400g', 'name_en' => '400g pack', 'price' => 18000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Rau củ quả',
                'name' => 'Cà chua', 'name_en' => 'Tomato',
                'description' => 'Cà chua đỏ tươi, chua ngọt, đa dụng.',
                'description_en' => 'Fresh red tomatoes, sweet-sour, versatile.',
                'min_price' => 15000, 'is_hot' => true,
                'variants' => [
                    ['name' => '500g', 'price' => 15000, 'stock' => 400],
                    ['name' => '1kg',  'price' => 28000, 'sale_price' => 24000, 'stock' => 300],
                ],
            ],
            [
                'category' => 'Rau củ quả',
                'name' => 'Dưa leo', 'name_en' => 'Cucumber',
                'description' => 'Dưa leo tươi mát, giòn ngọt, ăn sống hoặc xào.',
                'description_en' => 'Fresh cucumber, crispy and sweet, eat raw or stir-fry.',
                'min_price' => 10000,
                'variants' => [
                    ['name' => '500g', 'price' => 10000, 'stock' => 350],
                    ['name' => '1kg',  'price' => 18000, 'stock' => 250],
                ],
            ],
            [
                'category' => 'Rau củ quả',
                'name' => 'Ớt sừng đỏ', 'name_en' => 'Red Horn Chili',
                'description' => 'Ớt đỏ cay vừa, thích hợp làm tương ớt hoặc xào.',
                'description_en' => 'Moderately spicy red chili for sauce or stir-fry.',
                'min_price' => 10000,
                'variants' => [
                    ['name' => '200g', 'price' => 10000, 'stock' => 300],
                    ['name' => '500g', 'price' => 22000, 'stock' => 200],
                ],
            ],
            [
                'category' => 'Rau củ quả',
                'name' => 'Cải thảo', 'name_en' => 'Napa Cabbage',
                'description' => 'Cải thảo tươi, ngọt dịu, phù hợp lẩu và xào.',
                'description_en' => 'Fresh napa cabbage, mildly sweet, perfect for hotpot.',
                'min_price' => 18000,
                'variants' => [
                    ['name' => '500g', 'price' => 18000, 'stock' => 250],
                    ['name' => '1kg',  'price' => 32000, 'sale_price' => 28000, 'stock' => 180],
                ],
            ],
            [
                'category' => 'Rau củ quả',
                'name' => 'Giá đỗ', 'name_en' => 'Bean Sprouts',
                'description' => 'Giá đỗ tươi trắng muốt, giòn ngọt.',
                'description_en' => 'Fresh bean sprouts, white and crispy.',
                'min_price' => 6000,
                'variants' => [
                    ['name' => '300g', 'price' => 6000, 'stock' => 500],
                    ['name' => '500g', 'price' => 9000, 'stock' => 400],
                ],
            ],
            [
                'category' => 'Rau củ quả',
                'name' => 'Khoai lang tím', 'name_en' => 'Purple Sweet Potato',
                'description' => 'Khoai lang tím Nhật, bùi ngọt, giàu anthocyanin.',
                'description_en' => 'Japanese purple sweet potato, nutty-sweet, rich in anthocyanin.',
                'min_price' => 22000, 'is_new' => true,
                'variants' => [
                    ['name' => '500g', 'price' => 22000, 'stock' => 200],
                    ['name' => '1kg',  'price' => 40000, 'sale_price' => 36000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Rau củ quả',
                'name' => 'Bí đỏ', 'name_en' => 'Pumpkin',
                'description' => 'Bí đỏ hầm ngọt, nấu cháo hoặc soup.',
                'description_en' => 'Sweet pumpkin for braising, porridge or soup.',
                'min_price' => 18000,
                'variants' => [
                    ['name' => '500g', 'price' => 18000, 'stock' => 200],
                    ['name' => '1kg',  'price' => 32000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Rau củ quả',
                'name' => 'Ngô (Bắp) ngọt', 'name_en' => 'Sweet Corn',
                'description' => 'Bắp ngọt tươi, luộc hoặc nướng đều ngon.',
                'description_en' => 'Fresh sweet corn, delicious boiled or grilled.',
                'min_price' => 10000, 'is_hot' => true,
                'variants' => [
                    ['name' => '1 trái', 'name_en' => '1 cob', 'price' => 10000, 'stock' => 500],
                    ['name' => '3 trái', 'name_en' => '3 cobs', 'price' => 28000, 'sale_price' => 25000, 'stock' => 300],
                ],
            ],
            [
                'category' => 'Rau củ quả',
                'name' => 'Súp lơ xanh (Broccoli)', 'name_en' => 'Broccoli',
                'description' => 'Broccoli tươi, xanh đậm, giàu vitamin C.',
                'description_en' => 'Fresh broccoli, deep green, rich in vitamin C.',
                'min_price' => 25000, 'is_new' => true,
                'variants' => [
                    ['name' => '400g', 'price' => 25000, 'stock' => 200],
                    ['name' => '700g', 'price' => 42000, 'sale_price' => 38000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Rau củ quả',
                'name' => 'Cần tây', 'name_en' => 'Celery',
                'description' => 'Cần tây tươi, mùi thơm đặc trưng, tốt cho tim mạch.',
                'description_en' => 'Fresh celery, distinctive aroma, good for heart health.',
                'min_price' => 12000,
                'variants' => [
                    ['name' => '300g', 'price' => 12000, 'stock' => 250],
                    ['name' => '500g', 'price' => 18000, 'stock' => 180],
                ],
            ],
            [
                'category' => 'Rau củ quả',
                'name' => 'Bông atiso', 'name_en' => 'Artichoke',
                'description' => 'Atiso Đà Lạt tươi, đặc sản vùng cao nguyên.',
                'description_en' => 'Fresh Da Lat artichoke, highland specialty.',
                'min_price' => 35000, 'is_new' => true,
                'variants' => [
                    ['name' => '1 bông', 'name_en' => '1 flower', 'price' => 35000, 'stock' => 100],
                    ['name' => '3 bông', 'name_en' => '3 flowers', 'price' => 95000, 'sale_price' => 85000, 'stock' => 80],
                ],
            ],
            [
                'category' => 'Rau củ quả',
                'name' => 'Đậu bắp', 'name_en' => 'Okra',
                'description' => 'Đậu bắp tươi, giàu chất nhầy tốt cho tiêu hóa.',
                'description_en' => 'Fresh okra, rich in mucilage good for digestion.',
                'min_price' => 12000,
                'variants' => [
                    ['name' => '300g', 'price' => 12000, 'stock' => 200],
                    ['name' => '500g', 'price' => 18000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Rau củ quả',
                'name' => 'Rau dền đỏ', 'name_en' => 'Red Amaranth',
                'description' => 'Rau dền đỏ tươi, giàu sắt và canxi.',
                'description_en' => 'Fresh red amaranth, rich in iron and calcium.',
                'min_price' => 8000,
                'variants' => [
                    ['name' => '300g', 'price' => 8000, 'stock' => 300],
                    ['name' => '500g', 'price' => 12000, 'stock' => 200],
                ],
            ],

            // ═══════════════ THỊT & GIA CẦM (20) ═══════════════
            [
                'category' => 'Thịt & Gia cầm',
                'name' => 'Thịt ba chỉ heo', 'name_en' => 'Pork Belly',
                'description' => 'Thịt ba chỉ heo tươi, cân bằng mỡ nạc, phù hợp kho, quay, cuộn.',
                'description_en' => 'Fresh pork belly, balanced fat-to-meat ratio, ideal for braising or roasting.',
                'min_price' => 85000, 'is_hot' => true,
                'variants' => [
                    ['name' => '300g', 'price' => 85000, 'stock' => 150],
                    ['name' => '500g', 'price' => 130000, 'sale_price' => 119000, 'stock' => 100],
                    ['name' => '1kg',  'price' => 250000, 'stock' => 80],
                ],
            ],
            [
                'category' => 'Thịt & Gia cầm',
                'name' => 'Thịt nạc vai heo', 'name_en' => 'Pork Shoulder',
                'description' => 'Nạc vai heo tươi, thịt mềm, ít mỡ, phù hợp xào, kho.',
                'description_en' => 'Fresh pork shoulder, tender meat, low fat.',
                'min_price' => 75000,
                'variants' => [
                    ['name' => '300g', 'price' => 75000, 'stock' => 150],
                    ['name' => '500g', 'price' => 120000, 'stock' => 100],
                ],
            ],
            [
                'category' => 'Thịt & Gia cầm',
                'name' => 'Xương sườn heo non', 'name_en' => 'Baby Back Ribs',
                'description' => 'Sườn heo non tươi, nhiều thịt, ngọt xương.',
                'description_en' => 'Fresh baby back ribs, meaty and flavorful.',
                'min_price' => 95000, 'is_hot' => true,
                'variants' => [
                    ['name' => '500g', 'price' => 95000, 'stock' => 120],
                    ['name' => '1kg',  'price' => 185000, 'sale_price' => 169000, 'stock' => 80],
                ],
            ],
            [
                'category' => 'Thịt & Gia cầm',
                'name' => 'Đùi gà tươi', 'name_en' => 'Chicken Thigh',
                'description' => 'Đùi gà tươi, da vàng, thịt ngọt, thích hợp chiên, kho, nướng.',
                'description_en' => 'Fresh chicken thigh, golden skin, sweet meat.',
                'min_price' => 55000, 'is_hot' => true,
                'variants' => [
                    ['name' => '500g (~2 đùi)', 'name_en' => '500g (~2 thighs)', 'price' => 55000, 'stock' => 200],
                    ['name' => '1kg (~4 đùi)', 'name_en' => '1kg (~4 thighs)', 'price' => 105000, 'sale_price' => 95000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Thịt & Gia cầm',
                'name' => 'Ức gà tươi', 'name_en' => 'Chicken Breast',
                'description' => 'Ức gà tươi, ít chất béo, giàu protein, phù hợp ăn kiêng.',
                'description_en' => 'Fresh chicken breast, low fat, high protein, diet-friendly.',
                'min_price' => 60000, 'is_new' => true,
                'variants' => [
                    ['name' => '300g', 'price' => 60000, 'stock' => 200],
                    ['name' => '500g', 'price' => 95000, 'sale_price' => 88000, 'stock' => 150],
                    ['name' => '1kg',  'price' => 180000, 'stock' => 100],
                ],
            ],
            [
                'category' => 'Thịt & Gia cầm',
                'name' => 'Gà nguyên con', 'name_en' => 'Whole Chicken',
                'description' => 'Gà ta nguyên con, tươi sống, làm sạch sẵn.',
                'description_en' => 'Whole free-range chicken, freshly cleaned.',
                'min_price' => 120000,
                'variants' => [
                    ['name' => '~1kg', 'price' => 120000, 'stock' => 80],
                    ['name' => '~1.5kg', 'price' => 175000, 'sale_price' => 160000, 'stock' => 60],
                ],
            ],
            [
                'category' => 'Thịt & Gia cầm',
                'name' => 'Thịt bò thăn nội', 'name_en' => 'Beef Tenderloin',
                'description' => 'Thăn nội bò tươi, mềm mọng, thích hợp bít tết.',
                'description_en' => 'Fresh beef tenderloin, tender and juicy, perfect for steak.',
                'min_price' => 180000, 'is_hot' => true,
                'variants' => [
                    ['name' => '200g', 'price' => 180000, 'stock' => 80],
                    ['name' => '300g', 'price' => 260000, 'sale_price' => 240000, 'stock' => 60],
                    ['name' => '500g', 'price' => 420000, 'stock' => 40],
                ],
            ],
            [
                'category' => 'Thịt & Gia cầm',
                'name' => 'Thịt bò bắp', 'name_en' => 'Beef Shank',
                'description' => 'Bắp bò tươi, vân gân đẹp, thích hợp hầm, nấu phở.',
                'description_en' => 'Fresh beef shank with beautiful marbling, great for stew or pho.',
                'min_price' => 140000,
                'variants' => [
                    ['name' => '300g', 'price' => 140000, 'stock' => 100],
                    ['name' => '500g', 'price' => 220000, 'sale_price' => 199000, 'stock' => 80],
                ],
            ],
            [
                'category' => 'Thịt & Gia cầm',
                'name' => 'Cánh gà', 'name_en' => 'Chicken Wings',
                'description' => 'Cánh gà tươi, da giòn, thịt ngọt, chiên hoặc nướng.',
                'description_en' => 'Fresh chicken wings, crispy skin, sweet meat.',
                'min_price' => 65000, 'is_hot' => true,
                'variants' => [
                    ['name' => '500g', 'price' => 65000, 'stock' => 200],
                    ['name' => '1kg',  'price' => 125000, 'sale_price' => 115000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Thịt & Gia cầm',
                'name' => 'Chân giò heo', 'name_en' => 'Pork Trotter',
                'description' => 'Chân giò heo tươi, nhiều collagen, hầm mềm rất ngon.',
                'description_en' => 'Fresh pork trotter, rich in collagen, delicious when braised.',
                'min_price' => 70000,
                'variants' => [
                    ['name' => '500g', 'price' => 70000, 'stock' => 120],
                    ['name' => '1kg',  'price' => 135000, 'stock' => 80],
                ],
            ],
            [
                'category' => 'Thịt & Gia cầm',
                'name' => 'Vịt tươi nguyên con', 'name_en' => 'Whole Duck',
                'description' => 'Vịt ta nguyên con, sạch lông, phù hợp quay hoặc tiềm.',
                'description_en' => 'Whole free-range duck, cleaned, great for roasting.',
                'min_price' => 180000, 'is_new' => true,
                'variants' => [
                    ['name' => '~1.5kg', 'price' => 180000, 'stock' => 60],
                    ['name' => '~2kg',   'price' => 230000, 'sale_price' => 210000, 'stock' => 40],
                ],
            ],
            [
                'category' => 'Thịt & Gia cầm',
                'name' => 'Thịt heo xay sẵn', 'name_en' => 'Ground Pork',
                'description' => 'Heo xay tươi, pha nạc vai, tiện dùng làm chả, bánh bao.',
                'description_en' => 'Freshly ground pork shoulder, convenient for dumplings or patties.',
                'min_price' => 65000, 'is_hot' => true,
                'variants' => [
                    ['name' => '300g', 'price' => 65000, 'stock' => 200],
                    ['name' => '500g', 'price' => 100000, 'sale_price' => 92000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Thịt & Gia cầm',
                'name' => 'Bò xay sẵn', 'name_en' => 'Ground Beef',
                'description' => 'Bò xay tươi, đóng gói hút chân không, tiện lợi.',
                'description_en' => 'Freshly ground beef, vacuum-packed for freshness.',
                'min_price' => 110000,
                'variants' => [
                    ['name' => '300g', 'price' => 110000, 'stock' => 150],
                    ['name' => '500g', 'price' => 175000, 'sale_price' => 160000, 'stock' => 100],
                ],
            ],
            [
                'category' => 'Thịt & Gia cầm',
                'name' => 'Gan heo tươi', 'name_en' => 'Fresh Pork Liver',
                'description' => 'Gan heo tươi, giàu sắt và vitamin nhóm B.',
                'description_en' => 'Fresh pork liver, rich in iron and B vitamins.',
                'min_price' => 45000,
                'variants' => [
                    ['name' => '300g', 'price' => 45000, 'stock' => 150],
                    ['name' => '500g', 'price' => 70000, 'stock' => 100],
                ],
            ],
            [
                'category' => 'Thịt & Gia cầm',
                'name' => 'Lòng heo sơ chế', 'name_en' => 'Cleaned Pork Offal',
                'description' => 'Lòng heo sơ chế sạch, bao gồm ruột non, tim, cật.',
                'description_en' => 'Cleaned pork offal set: small intestine, heart, kidney.',
                'min_price' => 55000,
                'variants' => [
                    ['name' => '500g', 'price' => 55000, 'stock' => 100],
                    ['name' => '1kg',  'price' => 105000, 'stock' => 70],
                ],
            ],
            [
                'category' => 'Thịt & Gia cầm',
                'name' => 'Xương ống heo', 'name_en' => 'Pork Marrow Bone',
                'description' => 'Xương ống heo tươi, nhiều tủy, hầm nước dùng ngọt thanh.',
                'description_en' => 'Fresh pork marrow bones, great for clear broth.',
                'min_price' => 40000,
                'variants' => [
                    ['name' => '500g', 'price' => 40000, 'stock' => 150],
                    ['name' => '1kg',  'price' => 75000, 'sale_price' => 68000, 'stock' => 100],
                ],
            ],
            [
                'category' => 'Thịt & Gia cầm',
                'name' => 'Bắp bò Mỹ nhập khẩu', 'name_en' => 'US Beef Shank',
                'description' => 'Bắp bò Mỹ đông lạnh, vân gân mịn, hương vị đậm đà.',
                'description_en' => 'Imported US beef shank, fine marbling, rich flavor.',
                'min_price' => 190000, 'is_new' => true,
                'variants' => [
                    ['name' => '300g', 'price' => 190000, 'stock' => 80],
                    ['name' => '500g', 'price' => 300000, 'sale_price' => 275000, 'stock' => 60],
                ],
            ],
            [
                'category' => 'Thịt & Gia cầm',
                'name' => 'Ngan (Vịt xiêm) cắt miếng', 'name_en' => 'Muscovy Duck Pieces',
                'description' => 'Ngan ta cắt miếng vừa ăn, thịt dai ngon, ít mỡ.',
                'description_en' => 'Muscovy duck cut into pieces, chewy and flavorful, low fat.',
                'min_price' => 110000,
                'variants' => [
                    ['name' => '500g', 'price' => 110000, 'stock' => 80],
                    ['name' => '1kg',  'price' => 210000, 'stock' => 60],
                ],
            ],
            [
                'category' => 'Thịt & Gia cầm',
                'name' => 'Thịt dê tươi', 'name_en' => 'Fresh Goat Meat',
                'description' => 'Thịt dê tươi, thơm đặc trưng, giàu protein.',
                'description_en' => 'Fresh goat meat, distinctive aroma, protein-rich.',
                'min_price' => 160000, 'is_new' => true,
                'variants' => [
                    ['name' => '300g', 'price' => 160000, 'stock' => 60],
                    ['name' => '500g', 'price' => 250000, 'sale_price' => 230000, 'stock' => 40],
                ],
            ],
            [
                'category' => 'Thịt & Gia cầm',
                'name' => 'Xúc xích tiệt trùng', 'name_en' => 'Sterilized Sausage',
                'description' => 'Xúc xích Đức tiệt trùng, tiện lợi, ăn liền.',
                'description_en' => 'German-style sterilized sausage, convenient ready-to-eat.',
                'min_price' => 35000, 'is_hot' => true,
                'variants' => [
                    ['name' => 'Gói 175g (5 cái)', 'name_en' => '175g pack (5 pcs)', 'price' => 35000, 'stock' => 400],
                    ['name' => 'Gói 350g (10 cái)', 'name_en' => '350g pack (10 pcs)', 'price' => 65000, 'sale_price' => 58000, 'stock' => 300],
                ],
            ],

            // ═══════════════ HẢI SẢN (20) ═══════════════
            [
                'category' => 'Hải sản',
                'name' => 'Tôm sú tươi', 'name_en' => 'Tiger Prawn',
                'description' => 'Tôm sú tươi sống, con to, thịt ngọt chắc.',
                'description_en' => 'Fresh live tiger prawns, large, sweet and firm.',
                'min_price' => 120000, 'is_hot' => true,
                'variants' => [
                    ['name' => '300g (~6-8 con)', 'name_en' => '300g (~6-8 pcs)', 'price' => 120000, 'stock' => 100],
                    ['name' => '500g (~10-14 con)', 'name_en' => '500g (~10-14 pcs)', 'price' => 190000, 'sale_price' => 175000, 'stock' => 80],
                ],
            ],
            [
                'category' => 'Hải sản',
                'name' => 'Tôm thẻ chân trắng', 'name_en' => 'White-leg Shrimp',
                'description' => 'Tôm thẻ tươi, thịt giòn, phù hợp rang muối, chiên xù.',
                'description_en' => 'Fresh white-leg shrimp, firm, great for salted or breaded frying.',
                'min_price' => 85000, 'is_hot' => true,
                'variants' => [
                    ['name' => '300g', 'price' => 85000, 'stock' => 150],
                    ['name' => '500g', 'price' => 135000, 'sale_price' => 125000, 'stock' => 100],
                    ['name' => '1kg',  'price' => 260000, 'stock' => 60],
                ],
            ],
            [
                'category' => 'Hải sản',
                'name' => 'Cá basa phi lê', 'name_en' => 'Pangasius Fillet',
                'description' => 'Cá basa phi lê, không xương, dễ chế biến.',
                'description_en' => 'Boneless pangasius fillet, easy to cook.',
                'min_price' => 65000,
                'variants' => [
                    ['name' => '300g', 'price' => 65000, 'stock' => 200],
                    ['name' => '500g', 'price' => 100000, 'sale_price' => 92000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Hải sản',
                'name' => 'Mực ống tươi', 'name_en' => 'Fresh Squid',
                'description' => 'Mực ống tươi nguyên con, thịt trắng dày.',
                'description_en' => 'Fresh whole squid, thick white meat.',
                'min_price' => 95000, 'is_hot' => true,
                'variants' => [
                    ['name' => '300g', 'price' => 95000, 'stock' => 120],
                    ['name' => '500g', 'price' => 150000, 'sale_price' => 139000, 'stock' => 80],
                ],
            ],
            [
                'category' => 'Hải sản',
                'name' => 'Cua biển tươi sống', 'name_en' => 'Live Sea Crab',
                'description' => 'Cua biển tươi sống, gạch đầy, thịt chắc.',
                'description_en' => 'Live sea crab, full of roe, firm meat.',
                'min_price' => 150000, 'is_hot' => true,
                'variants' => [
                    ['name' => '~400g/con', 'name_en' => '~400g/crab', 'price' => 150000, 'stock' => 80],
                    ['name' => '~600g/con', 'name_en' => '~600g/crab', 'price' => 220000, 'sale_price' => 200000, 'stock' => 60],
                ],
            ],
            [
                'category' => 'Hải sản',
                'name' => 'Nghêu tươi', 'name_en' => 'Fresh Clams',
                'description' => 'Nghêu tươi sống, thịt ngọt, xào sả ớt ngon.',
                'description_en' => 'Fresh live clams, sweet meat, great stir-fried with lemongrass.',
                'min_price' => 40000,
                'variants' => [
                    ['name' => '500g', 'price' => 40000, 'stock' => 200],
                    ['name' => '1kg',  'price' => 75000, 'sale_price' => 68000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Hải sản',
                'name' => 'Hàu tươi', 'name_en' => 'Fresh Oysters',
                'description' => 'Hàu biển tươi, béo ngậy, nướng phô mai hoặc ăn sống.',
                'description_en' => 'Fresh sea oysters, creamy, great grilled with cheese or raw.',
                'min_price' => 60000, 'is_new' => true,
                'variants' => [
                    ['name' => '6 con', 'name_en' => '6 pcs', 'price' => 60000, 'stock' => 100],
                    ['name' => '12 con', 'name_en' => '12 pcs', 'price' => 110000, 'sale_price' => 99000, 'stock' => 80],
                ],
            ],
            [
                'category' => 'Hải sản',
                'name' => 'Cá hồi phi lê (Salmon)', 'name_en' => 'Salmon Fillet',
                'description' => 'Cá hồi Na Uy phi lê tươi, giàu omega-3.',
                'description_en' => 'Norwegian salmon fillet, rich in omega-3.',
                'min_price' => 180000, 'is_hot' => true,
                'variants' => [
                    ['name' => '200g', 'price' => 180000, 'stock' => 100],
                    ['name' => '300g', 'price' => 260000, 'sale_price' => 240000, 'stock' => 80],
                    ['name' => '500g', 'price' => 420000, 'stock' => 50],
                ],
            ],
            [
                'category' => 'Hải sản',
                'name' => 'Cá thu đao tươi', 'name_en' => 'Fresh Pacific Mackerel',
                'description' => 'Cá thu đao tươi, thịt đậm vị, chiên hoặc sốt cà.',
                'description_en' => 'Fresh Pacific mackerel, rich flavor, pan-fried or in tomato sauce.',
                'min_price' => 70000,
                'variants' => [
                    ['name' => '500g (~1-2 con)', 'name_en' => '500g (~1-2 fish)', 'price' => 70000, 'stock' => 150],
                    ['name' => '1kg', 'price' => 135000, 'stock' => 100],
                ],
            ],
            [
                'category' => 'Hải sản',
                'name' => 'Tôm khô', 'name_en' => 'Dried Shrimp',
                'description' => 'Tôm khô loại 1, màu đỏ cam đẹp, vị ngọt tự nhiên.',
                'description_en' => 'Grade-1 dried shrimp, beautiful orange-red color, naturally sweet.',
                'min_price' => 80000, 'is_hot' => true,
                'variants' => [
                    ['name' => '100g', 'price' => 80000, 'stock' => 200],
                    ['name' => '200g', 'price' => 150000, 'sale_price' => 135000, 'stock' => 150],
                    ['name' => '500g', 'price' => 360000, 'stock' => 80],
                ],
            ],
            [
                'category' => 'Hải sản',
                'name' => 'Bạch tuộc tươi', 'name_en' => 'Fresh Octopus',
                'description' => 'Bạch tuộc tươi, dai ngon, phù hợp nướng hoặc xào.',
                'description_en' => 'Fresh octopus, chewy and delicious, great grilled or stir-fried.',
                'min_price' => 90000,
                'variants' => [
                    ['name' => '300g', 'price' => 90000, 'stock' => 80],
                    ['name' => '500g', 'price' => 145000, 'sale_price' => 130000, 'stock' => 60],
                ],
            ],
            [
                'category' => 'Hải sản',
                'name' => 'Sò huyết tươi', 'name_en' => 'Blood Cockles',
                'description' => 'Sò huyết tươi, hấp sả ngon, nhiều sắt.',
                'description_en' => 'Fresh blood cockles, delicious steamed with lemongrass, iron-rich.',
                'min_price' => 50000,
                'variants' => [
                    ['name' => '500g', 'price' => 50000, 'stock' => 150],
                    ['name' => '1kg',  'price' => 95000, 'sale_price' => 88000, 'stock' => 100],
                ],
            ],
            [
                'category' => 'Hải sản',
                'name' => 'Cá điêu hồng tươi', 'name_en' => 'Fresh Red Tilapia',
                'description' => 'Cá điêu hồng tươi, thịt trắng ngon, hấp hoặc chiên.',
                'description_en' => 'Fresh red tilapia, white flesh, great steamed or fried.',
                'min_price' => 55000,
                'variants' => [
                    ['name' => '500g (~1 con)', 'name_en' => '500g (~1 fish)', 'price' => 55000, 'stock' => 150],
                    ['name' => '1kg (~2 con)',  'name_en' => '1kg (~2 fish)', 'price' => 105000, 'stock' => 100],
                ],
            ],
            [
                'category' => 'Hải sản',
                'name' => 'Ghẹ xanh tươi', 'name_en' => 'Live Blue Swimming Crab',
                'description' => 'Ghẹ xanh tươi sống, thịt ngọt, hấp bia ngon.',
                'description_en' => 'Live blue swimming crab, sweet meat, great steamed with beer.',
                'min_price' => 100000, 'is_new' => true,
                'variants' => [
                    ['name' => '500g (~2 con)', 'name_en' => '500g (~2 crabs)', 'price' => 100000, 'stock' => 80],
                    ['name' => '1kg (~4 con)',  'name_en' => '1kg (~4 crabs)', 'price' => 195000, 'sale_price' => 179000, 'stock' => 60],
                ],
            ],
            [
                'category' => 'Hải sản',
                'name' => 'Chả cá thác lác', 'name_en' => 'Featherback Fish Cake',
                'description' => 'Chả cá thác lác handmade, dai giòn, không phụ gia.',
                'description_en' => 'Handmade featherback fish cake, chewy and crispy, additive-free.',
                'min_price' => 80000,
                'variants' => [
                    ['name' => '200g', 'price' => 80000, 'stock' => 150],
                    ['name' => '400g', 'price' => 155000, 'sale_price' => 140000, 'stock' => 100],
                ],
            ],
            [
                'category' => 'Hải sản',
                'name' => 'Cá ngừ đại dương phi lê', 'name_en' => 'Ocean Tuna Fillet',
                'description' => 'Cá ngừ đại dương phi lê, thịt đỏ tươi, ăn sống (sashimi) hoặc áp chảo.',
                'description_en' => 'Fresh ocean tuna fillet, red meat, great for sashimi or searing.',
                'min_price' => 150000, 'is_new' => true,
                'variants' => [
                    ['name' => '200g', 'price' => 150000, 'stock' => 60],
                    ['name' => '300g', 'price' => 220000, 'sale_price' => 200000, 'stock' => 40],
                ],
            ],
            [
                'category' => 'Hải sản',
                'name' => 'Tôm hùm đất (Tôm càng xanh)', 'name_en' => 'Giant River Prawn',
                'description' => 'Tôm càng xanh tươi sống, càng to, thịt ngọt chắc.',
                'description_en' => 'Live giant river prawns, large claws, sweet firm meat.',
                'min_price' => 170000, 'is_hot' => true,
                'variants' => [
                    ['name' => '300g (~2-3 con)', 'name_en' => '300g (~2-3 pcs)', 'price' => 170000, 'stock' => 60],
                    ['name' => '500g (~4-5 con)', 'name_en' => '500g (~4-5 pcs)', 'price' => 275000, 'sale_price' => 250000, 'stock' => 40],
                ],
            ],
            [
                'category' => 'Hải sản',
                'name' => 'Khô mực một nắng', 'name_en' => 'Sun-dried Squid',
                'description' => 'Mực một nắng tự nhiên, nướng chấm tương ngon.',
                'description_en' => 'Naturally sun-dried squid, delicious grilled with sauce.',
                'min_price' => 110000,
                'variants' => [
                    ['name' => '100g', 'price' => 110000, 'stock' => 100],
                    ['name' => '200g', 'price' => 210000, 'sale_price' => 190000, 'stock' => 70],
                ],
            ],
            [
                'category' => 'Hải sản',
                'name' => 'Ruốc tươi (Mắm ruốc)', 'name_en' => 'Fresh Shrimp Paste',
                'description' => 'Ruốc Huế tươi, màu tím đặc trưng, dùng kho hoặc làm nước chấm.',
                'description_en' => 'Fresh Hue shrimp paste, distinctive purple color.',
                'min_price' => 25000,
                'variants' => [
                    ['name' => '200g', 'price' => 25000, 'stock' => 200],
                    ['name' => '500g', 'price' => 55000, 'sale_price' => 49000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Hải sản',
                'name' => 'Cá bớp phi lê đông lạnh', 'name_en' => 'Frozen Cobia Fillet',
                'description' => 'Cá bớp Vân Phong phi lê đông lạnh, thịt chắc béo.',
                'description_en' => 'Frozen Van Phong cobia fillet, firm and fatty.',
                'min_price' => 130000,
                'variants' => [
                    ['name' => '300g', 'price' => 130000, 'stock' => 80],
                    ['name' => '500g', 'price' => 210000, 'sale_price' => 190000, 'stock' => 60],
                ],
            ],

            // ═══════════════ GIA VỊ & NƯỚC CHẤM (20) ═══════════════
            [
                'category' => 'Gia vị & Nước chấm',
                'name' => 'Nước mắm Phú Quốc', 'name_en' => 'Phu Quoc Fish Sauce',
                'description' => 'Nước mắm Phú Quốc độ đạm 40N, vàng óng, mùi thơm dịu.',
                'description_en' => 'Phu Quoc 40N fish sauce, golden amber, delicate aroma.',
                'min_price' => 45000, 'is_hot' => true,
                'variants' => [
                    ['name' => 'Chai 500ml', 'name_en' => '500ml bottle', 'price' => 45000, 'stock' => 300],
                    ['name' => 'Chai 750ml', 'name_en' => '750ml bottle', 'price' => 65000, 'sale_price' => 58000, 'stock' => 200],
                    ['name' => 'Chai 1 lít',  'name_en' => '1L bottle', 'price' => 85000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Gia vị & Nước chấm',
                'name' => 'Nước tương Maggi', 'name_en' => 'Maggi Soy Sauce',
                'description' => 'Nước tương Maggi đậm đà, phù hợp xào, kho, chấm.',
                'description_en' => 'Maggi soy sauce, rich flavor, great for stir-fry, braising.',
                'min_price' => 20000, 'is_hot' => true,
                'variants' => [
                    ['name' => 'Chai 200ml', 'name_en' => '200ml', 'price' => 20000, 'stock' => 400],
                    ['name' => 'Chai 700ml', 'name_en' => '700ml', 'price' => 55000, 'sale_price' => 49000, 'stock' => 250],
                ],
            ],
            [
                'category' => 'Gia vị & Nước chấm',
                'name' => 'Tương ớt Cholimex', 'name_en' => 'Cholimex Chili Sauce',
                'description' => 'Tương ớt Cholimex cay vừa, chấm bất kỳ món nào.',
                'description_en' => 'Cholimex chili sauce, moderately spicy, universal dipping sauce.',
                'min_price' => 18000, 'is_hot' => true,
                'variants' => [
                    ['name' => 'Chai 250g', 'name_en' => '250g', 'price' => 18000, 'stock' => 500],
                    ['name' => 'Chai 520g', 'name_en' => '520g', 'price' => 35000, 'sale_price' => 31000, 'stock' => 300],
                ],
            ],
            [
                'category' => 'Gia vị & Nước chấm',
                'name' => 'Hạt nêm Knorr', 'name_en' => 'Knorr Seasoning Granules',
                'description' => 'Hạt nêm Knorr từ thịt thật, nêm mọi món ngon.',
                'description_en' => 'Knorr seasoning granules from real meat, enhances every dish.',
                'min_price' => 22000, 'is_hot' => true,
                'variants' => [
                    ['name' => 'Gói 170g', 'name_en' => '170g pack', 'price' => 22000, 'stock' => 400],
                    ['name' => 'Gói 400g', 'name_en' => '400g pack', 'price' => 48000, 'sale_price' => 43000, 'stock' => 250],
                    ['name' => 'Túi 900g', 'name_en' => '900g bag', 'price' => 100000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Gia vị & Nước chấm',
                'name' => 'Muối I-ốt tinh', 'name_en' => 'Iodized Sea Salt',
                'description' => 'Muối biển i-ốt tinh khiết, hạt mịn đều.',
                'description_en' => 'Pure iodized sea salt, fine grain.',
                'min_price' => 8000,
                'variants' => [
                    ['name' => 'Gói 400g', 'name_en' => '400g', 'price' => 8000, 'stock' => 500],
                    ['name' => 'Gói 1kg',  'name_en' => '1kg', 'price' => 18000, 'stock' => 300],
                ],
            ],
            [
                'category' => 'Gia vị & Nước chấm',
                'name' => 'Đường trắng Biên Hòa', 'name_en' => 'Bien Hoa White Sugar',
                'description' => 'Đường trắng Biên Hòa, hạt đều, độ ngọt chuẩn.',
                'description_en' => 'Bien Hoa refined white sugar, uniform grain, standard sweetness.',
                'min_price' => 22000,
                'variants' => [
                    ['name' => 'Túi 1kg',  'name_en' => '1kg', 'price' => 22000, 'stock' => 400],
                    ['name' => 'Túi 5kg',  'name_en' => '5kg', 'price' => 100000, 'sale_price' => 92000, 'stock' => 200],
                ],
            ],
            [
                'category' => 'Gia vị & Nước chấm',
                'name' => 'Nước tương đen (Xì dầu)', 'name_en' => 'Dark Soy Sauce',
                'description' => 'Xì dầu đen đặc sánh, dùng ướp thịt và tạo màu.',
                'description_en' => 'Dark thick soy sauce, used for marinating and coloring.',
                'min_price' => 25000,
                'variants' => [
                    ['name' => 'Chai 150ml', 'name_en' => '150ml', 'price' => 25000, 'stock' => 300],
                    ['name' => 'Chai 300ml', 'name_en' => '300ml', 'price' => 42000, 'sale_price' => 38000, 'stock' => 200],
                ],
            ],
            [
                'category' => 'Gia vị & Nước chấm',
                'name' => 'Giấm gạo', 'name_en' => 'Rice Vinegar',
                'description' => 'Giấm gạo trắng dịu, dùng làm gỏi, dưa chua.',
                'description_en' => 'Mild white rice vinegar, for salad or pickles.',
                'min_price' => 15000,
                'variants' => [
                    ['name' => 'Chai 250ml', 'name_en' => '250ml', 'price' => 15000, 'stock' => 300],
                    ['name' => 'Chai 500ml', 'name_en' => '500ml', 'price' => 25000, 'stock' => 200],
                ],
            ],
            [
                'category' => 'Gia vị & Nước chấm',
                'name' => 'Sốt hoisin', 'name_en' => 'Hoisin Sauce',
                'description' => 'Sốt hoisin đậm ngọt, chấm cuốn hoặc tẩm ướp BBQ.',
                'description_en' => 'Sweet hoisin sauce for spring rolls or BBQ marinade.',
                'min_price' => 28000,
                'variants' => [
                    ['name' => 'Chai 260g', 'name_en' => '260g', 'price' => 28000, 'stock' => 250],
                    ['name' => 'Chai 480g', 'name_en' => '480g', 'price' => 48000, 'sale_price' => 43000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Gia vị & Nước chấm',
                'name' => 'Bột ngọt Ajinomoto', 'name_en' => 'Ajinomoto MSG',
                'description' => 'Bột ngọt Ajinomoto chính hãng, tăng vị umami cho món ăn.',
                'description_en' => 'Authentic Ajinomoto MSG, enhances umami flavor.',
                'min_price' => 12000,
                'variants' => [
                    ['name' => 'Gói 100g', 'name_en' => '100g', 'price' => 12000, 'stock' => 500],
                    ['name' => 'Gói 300g', 'name_en' => '300g', 'price' => 30000, 'sale_price' => 27000, 'stock' => 300],
                ],
            ],
            [
                'category' => 'Gia vị & Nước chấm',
                'name' => 'Ớt bột Hàn Quốc (Gochugaru)', 'name_en' => 'Korean Chili Powder (Gochugaru)',
                'description' => 'Ớt bột Hàn cay vừa, dùng làm kimchi và tteokbokki.',
                'description_en' => 'Korean gochugaru chili flakes, moderately spicy, for kimchi.',
                'min_price' => 45000, 'is_new' => true,
                'variants' => [
                    ['name' => '100g', 'price' => 45000, 'stock' => 150],
                    ['name' => '200g', 'price' => 85000, 'sale_price' => 75000, 'stock' => 100],
                ],
            ],
            [
                'category' => 'Gia vị & Nước chấm',
                'name' => 'Sốt teriyaki', 'name_en' => 'Teriyaki Sauce',
                'description' => 'Sốt teriyaki Nhật, ngọt dịu, ướp gà cá rất ngon.',
                'description_en' => 'Japanese teriyaki sauce, mildly sweet, great for marinating.',
                'min_price' => 55000, 'is_new' => true,
                'variants' => [
                    ['name' => 'Chai 240ml', 'name_en' => '240ml', 'price' => 55000, 'stock' => 200],
                    ['name' => 'Chai 500ml', 'name_en' => '500ml', 'price' => 100000, 'sale_price' => 90000, 'stock' => 120],
                ],
            ],
            [
                'category' => 'Gia vị & Nước chấm',
                'name' => 'Mắm nêm', 'name_en' => 'Fermented Fish Sauce (Mam Nem)',
                'description' => 'Mắm nêm Đà Nẵng, thơm đặc trưng, chấm thịt nướng và bún.',
                'description_en' => 'Da Nang fermented fish sauce, distinctive aroma for grilled meat.',
                'min_price' => 20000,
                'variants' => [
                    ['name' => 'Chai 250ml', 'name_en' => '250ml', 'price' => 20000, 'stock' => 200],
                    ['name' => 'Chai 500ml', 'name_en' => '500ml', 'price' => 35000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Gia vị & Nước chấm',
                'name' => 'Tương cà (Ketchup)', 'name_en' => 'Tomato Ketchup',
                'description' => 'Tương cà Heinz đậm vị, chấm chiên hoặc làm pizza.',
                'description_en' => 'Heinz tomato ketchup, rich flavor for dipping or pizza.',
                'min_price' => 32000,
                'variants' => [
                    ['name' => 'Chai 300g', 'name_en' => '300g', 'price' => 32000, 'stock' => 300],
                    ['name' => 'Chai 567g', 'name_en' => '567g', 'price' => 55000, 'sale_price' => 48000, 'stock' => 200],
                ],
            ],
            [
                'category' => 'Gia vị & Nước chấm',
                'name' => 'Bột nghệ', 'name_en' => 'Turmeric Powder',
                'description' => 'Bột nghệ vàng tự nhiên, thơm, dùng ướp cá và nấu cà ri.',
                'description_en' => 'Natural turmeric powder, aromatic, for marinating fish and curry.',
                'min_price' => 18000,
                'variants' => [
                    ['name' => 'Gói 100g', 'name_en' => '100g', 'price' => 18000, 'stock' => 300],
                    ['name' => 'Gói 200g', 'name_en' => '200g', 'price' => 32000, 'stock' => 200],
                ],
            ],
            [
                'category' => 'Gia vị & Nước chấm',
                'name' => 'Bột cà ri', 'name_en' => 'Curry Powder',
                'description' => 'Bột cà ri Ấn Độ thơm nồng, blend 15 gia vị.',
                'description_en' => 'Indian curry powder, fragrant blend of 15 spices.',
                'min_price' => 22000,
                'variants' => [
                    ['name' => 'Gói 100g', 'name_en' => '100g', 'price' => 22000, 'stock' => 250],
                    ['name' => 'Gói 250g', 'name_en' => '250g', 'price' => 50000, 'sale_price' => 44000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Gia vị & Nước chấm',
                'name' => 'Sốt mayonnaise', 'name_en' => 'Mayonnaise',
                'description' => 'Mayonnaise Kewpie Nhật, béo mịn, hơi ngọt dịu.',
                'description_en' => 'Japanese Kewpie mayo, smooth and creamy.',
                'min_price' => 45000, 'is_hot' => true,
                'variants' => [
                    ['name' => 'Tuýp 130g', 'name_en' => '130g tube', 'price' => 45000, 'stock' => 300],
                    ['name' => 'Tuýp 500g', 'name_en' => '500g tube', 'price' => 130000, 'sale_price' => 115000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Gia vị & Nước chấm',
                'name' => 'Dầu hào', 'name_en' => 'Oyster Sauce',
                'description' => 'Dầu hào Lee Kum Kee đặc sánh, ngọt tự nhiên từ hàu.',
                'description_en' => 'Lee Kum Kee oyster sauce, naturally sweet from real oysters.',
                'min_price' => 35000, 'is_hot' => true,
                'variants' => [
                    ['name' => 'Chai 255g', 'name_en' => '255g', 'price' => 35000, 'stock' => 350],
                    ['name' => 'Chai 510g', 'name_en' => '510g', 'price' => 60000, 'sale_price' => 53000, 'stock' => 200],
                ],
            ],
            [
                'category' => 'Gia vị & Nước chấm',
                'name' => 'Bột ngũ vị hương', 'name_en' => 'Five Spice Powder',
                'description' => 'Ngũ vị hương truyền thống: quế, đinh hương, hồi, tiêu, tiểu hồi.',
                'description_en' => 'Traditional five spice: cinnamon, clove, star anise, pepper, fennel.',
                'min_price' => 15000,
                'variants' => [
                    ['name' => 'Gói 50g',  'name_en' => '50g', 'price' => 15000, 'stock' => 250],
                    ['name' => 'Gói 100g', 'name_en' => '100g', 'price' => 25000, 'stock' => 180],
                ],
            ],
            [
                'category' => 'Gia vị & Nước chấm',
                'name' => 'Tiêu xay Phú Quốc', 'name_en' => 'Phu Quoc Ground Pepper',
                'description' => 'Tiêu Phú Quốc xay mịn, thơm cay đặc trưng.',
                'description_en' => 'Ground Phu Quoc pepper, distinctively fragrant and spicy.',
                'min_price' => 25000, 'is_hot' => true,
                'variants' => [
                    ['name' => 'Hũ 50g',  'name_en' => '50g jar', 'price' => 25000, 'stock' => 300],
                    ['name' => 'Hũ 100g', 'name_en' => '100g jar', 'price' => 45000, 'sale_price' => 40000, 'stock' => 200],
                ],
            ],

            // ═══════════════ DẦU ĂN & BƠ (10) ═══════════════
            [
                'category' => 'Dầu ăn & Bơ',
                'name' => 'Dầu ăn Tường An', 'name_en' => 'Tuong An Cooking Oil',
                'description' => 'Dầu ăn thực vật Tường An, không cholesterol, chiên không bắn.',
                'description_en' => 'Tuong An vegetable oil, cholesterol-free, non-spattering.',
                'min_price' => 35000, 'is_hot' => true,
                'variants' => [
                    ['name' => 'Chai 1 lít',  'name_en' => '1L', 'price' => 35000, 'stock' => 300],
                    ['name' => 'Chai 2 lít',  'name_en' => '2L', 'price' => 65000, 'sale_price' => 59000, 'stock' => 200],
                    ['name' => 'Can 5 lít',   'name_en' => '5L', 'price' => 155000, 'stock' => 100],
                ],
            ],
            [
                'category' => 'Dầu ăn & Bơ',
                'name' => 'Dầu olive Borges', 'name_en' => 'Borges Olive Oil',
                'description' => 'Dầu olive Borges Extra Virgin, press lạnh lần đầu.',
                'description_en' => 'Borges Extra Virgin Olive Oil, first cold press.',
                'min_price' => 95000, 'is_new' => true,
                'variants' => [
                    ['name' => 'Chai 250ml', 'name_en' => '250ml', 'price' => 95000, 'stock' => 150],
                    ['name' => 'Chai 500ml', 'name_en' => '500ml', 'price' => 170000, 'sale_price' => 155000, 'stock' => 100],
                    ['name' => 'Chai 1 lít',  'name_en' => '1L', 'price' => 320000, 'stock' => 60],
                ],
            ],
            [
                'category' => 'Dầu ăn & Bơ',
                'name' => 'Bơ lạt Anchor', 'name_en' => 'Anchor Unsalted Butter',
                'description' => 'Bơ lạt Anchor New Zealand, thơm béo tự nhiên.',
                'description_en' => 'Anchor New Zealand unsalted butter, naturally rich and creamy.',
                'min_price' => 55000, 'is_hot' => true,
                'variants' => [
                    ['name' => '200g', 'price' => 55000, 'stock' => 200],
                    ['name' => '500g', 'price' => 130000, 'sale_price' => 118000, 'stock' => 120],
                ],
            ],
            [
                'category' => 'Dầu ăn & Bơ',
                'name' => 'Dầu mè (Vừng) rang', 'name_en' => 'Toasted Sesame Oil',
                'description' => 'Dầu mè rang thơm lừng, dùng hoàn thiện món salad và mì.',
                'description_en' => 'Fragrant toasted sesame oil, for finishing salads and noodles.',
                'min_price' => 35000,
                'variants' => [
                    ['name' => 'Chai 150ml', 'name_en' => '150ml', 'price' => 35000, 'stock' => 200],
                    ['name' => 'Chai 300ml', 'name_en' => '300ml', 'price' => 60000, 'sale_price' => 54000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Dầu ăn & Bơ',
                'name' => 'Dầu dừa nguyên chất', 'name_en' => 'Virgin Coconut Oil',
                'description' => 'Dầu dừa Virgin ép lạnh, thơm nhẹ, dùng nấu ăn và làm đẹp.',
                'description_en' => 'Cold-pressed virgin coconut oil, lightly aromatic.',
                'min_price' => 80000, 'is_new' => true,
                'variants' => [
                    ['name' => 'Hũ 200ml', 'name_en' => '200ml jar', 'price' => 80000, 'stock' => 150],
                    ['name' => 'Hũ 500ml', 'name_en' => '500ml jar', 'price' => 180000, 'sale_price' => 165000, 'stock' => 80],
                ],
            ],
            [
                'category' => 'Dầu ăn & Bơ',
                'name' => 'Mỡ heo xay', 'name_en' => 'Rendered Pork Fat (Lard)',
                'description' => 'Mỡ heo thắng sẵn, thơm béo, xào rau ngon.',
                'description_en' => 'Rendered pork lard, fragrant and rich, enhances stir-fried vegetables.',
                'min_price' => 30000,
                'variants' => [
                    ['name' => 'Hũ 300g', 'name_en' => '300g jar', 'price' => 30000, 'stock' => 200],
                    ['name' => 'Hũ 600g', 'name_en' => '600g jar', 'price' => 55000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Dầu ăn & Bơ',
                'name' => 'Bơ thực vật Tường An', 'name_en' => 'Tuong An Margarine',
                'description' => 'Bơ thực vật Tường An, dùng phết bánh mì hoặc làm bánh.',
                'description_en' => 'Tuong An margarine, for spreading or baking.',
                'min_price' => 22000,
                'variants' => [
                    ['name' => 'Hộp 200g', 'name_en' => '200g tub', 'price' => 22000, 'stock' => 300],
                    ['name' => 'Hộp 400g', 'name_en' => '400g tub', 'price' => 38000, 'sale_price' => 34000, 'stock' => 200],
                ],
            ],
            [
                'category' => 'Dầu ăn & Bơ',
                'name' => 'Dầu phộng (lạc) ép lạnh', 'name_en' => 'Cold-pressed Peanut Oil',
                'description' => 'Dầu phộng ép lạnh, vàng trong, thơm đặc trưng.',
                'description_en' => 'Cold-pressed peanut oil, clear golden, distinctively fragrant.',
                'min_price' => 55000,
                'variants' => [
                    ['name' => 'Chai 500ml', 'name_en' => '500ml', 'price' => 55000, 'stock' => 200],
                    ['name' => 'Chai 1 lít',  'name_en' => '1L', 'price' => 100000, 'sale_price' => 90000, 'stock' => 120],
                ],
            ],
            [
                'category' => 'Dầu ăn & Bơ',
                'name' => 'Bơ nhạt Kiri', 'name_en' => 'Kiri Cream Cheese Spread',
                'description' => 'Kem phô mai Kiri Pháp, dùng phết bánh mì hoặc làm nhân bánh.',
                'description_en' => 'French Kiri cream cheese for spreading or pastry filling.',
                'min_price' => 65000, 'is_new' => true,
                'variants' => [
                    ['name' => 'Hộp 8 miếng (140g)', 'name_en' => '8-piece box (140g)', 'price' => 65000, 'stock' => 150],
                    ['name' => 'Hộp 16 miếng (280g)', 'name_en' => '16-piece box (280g)', 'price' => 125000, 'sale_price' => 112000, 'stock' => 80],
                ],
            ],
            [
                'category' => 'Dầu ăn & Bơ',
                'name' => 'Dầu hướng dương Simply', 'name_en' => 'Simply Sunflower Oil',
                'description' => 'Dầu hướng dương Simply, nhẹ, chịu nhiệt tốt cho chiên rán.',
                'description_en' => 'Simply sunflower oil, light, high smoke point for deep-frying.',
                'min_price' => 42000,
                'variants' => [
                    ['name' => 'Chai 1 lít', 'name_en' => '1L', 'price' => 42000, 'stock' => 250],
                    ['name' => 'Chai 2 lít', 'name_en' => '2L', 'price' => 78000, 'sale_price' => 70000, 'stock' => 150],
                ],
            ],

            // ═══════════════ GẠO & NGŨ CỐC (10) ═══════════════
            [
                'category' => 'Gạo & Ngũ cốc',
                'name' => 'Gạo ST25', 'name_en' => 'ST25 Rice',
                'description' => 'Gạo ST25 Sóc Trăng, ngon nhất thế giới, dẻo thơm tự nhiên.',
                'description_en' => 'ST25 Soc Trang rice, world top rice, naturally fragrant and sticky.',
                'min_price' => 28000, 'is_hot' => true,
                'variants' => [
                    ['name' => 'Túi 2kg',  'name_en' => '2kg', 'price' => 28000, 'stock' => 300],
                    ['name' => 'Túi 5kg',  'name_en' => '5kg', 'price' => 68000, 'sale_price' => 62000, 'stock' => 200],
                    ['name' => 'Túi 10kg', 'name_en' => '10kg', 'price' => 132000, 'stock' => 100],
                ],
            ],
            [
                'category' => 'Gạo & Ngũ cốc',
                'name' => 'Gạo lứt đỏ', 'name_en' => 'Red Brown Rice',
                'description' => 'Gạo lứt đỏ nguyên cám, giàu chất xơ và khoáng.',
                'description_en' => 'Red whole-grain rice, high in fiber and minerals.',
                'min_price' => 32000, 'is_new' => true,
                'variants' => [
                    ['name' => 'Túi 1kg', 'name_en' => '1kg', 'price' => 32000, 'stock' => 200],
                    ['name' => 'Túi 5kg', 'name_en' => '5kg', 'price' => 150000, 'sale_price' => 135000, 'stock' => 100],
                ],
            ],
            [
                'category' => 'Gạo & Ngũ cốc',
                'name' => 'Yến mạch cán dẹt Quaker', 'name_en' => 'Quaker Rolled Oats',
                'description' => 'Yến mạch Quaker cán dẹt, ăn sáng nhanh hoặc làm granola.',
                'description_en' => 'Quaker rolled oats, quick breakfast or granola base.',
                'min_price' => 48000, 'is_hot' => true,
                'variants' => [
                    ['name' => 'Hộp 500g',  'name_en' => '500g box', 'price' => 48000, 'stock' => 250],
                    ['name' => 'Hộp 1.2kg', 'name_en' => '1.2kg box', 'price' => 108000, 'sale_price' => 98000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Gạo & Ngũ cốc',
                'name' => 'Hạt quinoa', 'name_en' => 'Quinoa Seeds',
                'description' => 'Hạt quinoa nhập khẩu, siêu hạt giàu protein hoàn chỉnh.',
                'description_en' => 'Imported quinoa seeds, superfood with complete protein.',
                'min_price' => 95000, 'is_new' => true,
                'variants' => [
                    ['name' => '300g', 'price' => 95000, 'stock' => 150],
                    ['name' => '500g', 'price' => 150000, 'sale_price' => 135000, 'stock' => 100],
                ],
            ],
            [
                'category' => 'Gạo & Ngũ cốc',
                'name' => 'Hạt chia', 'name_en' => 'Chia Seeds',
                'description' => 'Hạt chia nhập khẩu Mexico, giàu omega-3 và chất xơ.',
                'description_en' => 'Imported Mexican chia seeds, omega-3 and fiber-rich.',
                'min_price' => 65000, 'is_hot' => true,
                'variants' => [
                    ['name' => '200g', 'price' => 65000, 'stock' => 200],
                    ['name' => '500g', 'price' => 150000, 'sale_price' => 135000, 'stock' => 120],
                ],
            ],
            [
                'category' => 'Gạo & Ngũ cốc',
                'name' => 'Bắp (ngô) xay nghiền', 'name_en' => 'Cornmeal',
                'description' => 'Bột bắp nghiền thô, dùng nấu polenta hoặc bánh ngô.',
                'description_en' => 'Coarse cornmeal for polenta or corn bread.',
                'min_price' => 25000,
                'variants' => [
                    ['name' => '500g', 'price' => 25000, 'stock' => 200],
                    ['name' => '1kg',  'price' => 45000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Gạo & Ngũ cốc',
                'name' => 'Gạo nếp cái hoa vàng', 'name_en' => 'Sticky Rice (Nep Cai Hoa Vang)',
                'description' => 'Nếp cái hoa vàng Hải Hậu, dẻo thơm, gói bánh chưng.',
                'description_en' => 'Hai Hau sticky rice, fragrant and sticky, for Banh Chung.',
                'min_price' => 30000,
                'variants' => [
                    ['name' => 'Túi 1kg', 'name_en' => '1kg', 'price' => 30000, 'stock' => 250],
                    ['name' => 'Túi 5kg', 'name_en' => '5kg', 'price' => 138000, 'sale_price' => 125000, 'stock' => 100],
                ],
            ],
            [
                'category' => 'Gạo & Ngũ cốc',
                'name' => 'Lúa mì nguyên cám', 'name_en' => 'Whole Wheat Flour',
                'description' => 'Bột mì nguyên cám, giàu chất xơ, làm bánh healthy.',
                'description_en' => 'Whole wheat flour, fiber-rich, for healthy baking.',
                'min_price' => 35000, 'is_new' => true,
                'variants' => [
                    ['name' => '500g', 'price' => 35000, 'stock' => 200],
                    ['name' => '1kg',  'price' => 62000, 'sale_price' => 56000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Gạo & Ngũ cốc',
                'name' => 'Hạt kê (Millet)', 'name_en' => 'Millet',
                'description' => 'Hạt kê vàng tự nhiên, nấu cháo hoặc trộn ngũ cốc.',
                'description_en' => 'Natural golden millet, for porridge or mixed grain.',
                'min_price' => 28000,
                'variants' => [
                    ['name' => '500g', 'price' => 28000, 'stock' => 150],
                    ['name' => '1kg',  'price' => 52000, 'stock' => 100],
                ],
            ],
            [
                'category' => 'Gạo & Ngũ cốc',
                'name' => 'Gạo jasmine Thái Lan', 'name_en' => 'Thai Jasmine Rice',
                'description' => 'Gạo jasmine Thái thơm hương hoa lài, hạt dài trong.',
                'description_en' => 'Thai jasmine rice, jasmine-scented, long and clear grains.',
                'min_price' => 35000, 'is_hot' => true,
                'variants' => [
                    ['name' => 'Túi 2kg', 'name_en' => '2kg', 'price' => 35000, 'stock' => 250],
                    ['name' => 'Túi 5kg', 'name_en' => '5kg', 'price' => 82000, 'sale_price' => 75000, 'stock' => 150],
                ],
            ],

            // ═══════════════ MÌ & BÚN KHÔ (10) ═══════════════
            [
                'category' => 'Mì & Bún khô',
                'name' => 'Mì trứng khô', 'name_en' => 'Dried Egg Noodles',
                'description' => 'Mì trứng sấy khô, dùng xào hoặc nấu súp.',
                'description_en' => 'Dried egg noodles, for stir-fry or soup.',
                'min_price' => 20000,
                'variants' => [
                    ['name' => 'Gói 300g', 'name_en' => '300g', 'price' => 20000, 'stock' => 300],
                    ['name' => 'Gói 500g', 'name_en' => '500g', 'price' => 30000, 'sale_price' => 27000, 'stock' => 200],
                ],
            ],
            [
                'category' => 'Mì & Bún khô',
                'name' => 'Bún khô gạo', 'name_en' => 'Dried Rice Vermicelli',
                'description' => 'Bún gạo khô trắng tinh, trụng nhanh, dùng bún bò bún riêu.',
                'description_en' => 'White dried rice vermicelli, quick to cook, for various Vietnamese noodle dishes.',
                'min_price' => 18000,
                'variants' => [
                    ['name' => 'Gói 400g', 'name_en' => '400g', 'price' => 18000, 'stock' => 400],
                    ['name' => 'Gói 800g', 'name_en' => '800g', 'price' => 32000, 'sale_price' => 29000, 'stock' => 250],
                ],
            ],
            [
                'category' => 'Mì & Bún khô',
                'name' => 'Mì Ý (Spaghetti)', 'name_en' => 'Spaghetti',
                'description' => 'Mì Ý Barilla số 5, thon dài, nấu vừa chín al dente.',
                'description_en' => 'Barilla no.5 spaghetti, perfectly cooks al dente.',
                'min_price' => 35000, 'is_new' => true,
                'variants' => [
                    ['name' => 'Hộp 500g',  'name_en' => '500g', 'price' => 35000, 'stock' => 200],
                    ['name' => 'Hộp 1kg',   'name_en' => '1kg', 'price' => 65000, 'sale_price' => 58000, 'stock' => 120],
                ],
            ],
            [
                'category' => 'Mì & Bún khô',
                'name' => 'Bánh phở khô', 'name_en' => 'Dried Pho Noodles',
                'description' => 'Bánh phở khô size M, trụng mềm đẹp, nấu phở gia đình.',
                'description_en' => 'Medium dried pho noodles, perfect for homemade pho.',
                'min_price' => 22000,
                'variants' => [
                    ['name' => 'Gói 400g', 'name_en' => '400g', 'price' => 22000, 'stock' => 350],
                    ['name' => 'Gói 800g', 'name_en' => '800g', 'price' => 40000, 'stock' => 200],
                ],
            ],
            [
                'category' => 'Mì & Bún khô',
                'name' => 'Miến dong', 'name_en' => 'Glass Noodles (Dong Vermicelli)',
                'description' => 'Miến dong Bắc Giang trong suốt, không bở, miến gà tuyệt vời.',
                'description_en' => 'Clear Bac Giang dong noodles, firm, excellent in chicken glass noodle soup.',
                'min_price' => 25000,
                'variants' => [
                    ['name' => 'Gói 200g', 'name_en' => '200g', 'price' => 25000, 'stock' => 300],
                    ['name' => 'Gói 400g', 'name_en' => '400g', 'price' => 45000, 'sale_price' => 40000, 'stock' => 200],
                ],
            ],
            [
                'category' => 'Mì & Bún khô',
                'name' => 'Mì ramen khô', 'name_en' => 'Dried Ramen Noodles',
                'description' => 'Mì ramen khô Nhật Bản, sợi mì xoắn, nấu cùng nước dùng tonkotsu.',
                'description_en' => 'Japanese dried ramen noodles, wavy, ideal with tonkotsu broth.',
                'min_price' => 45000, 'is_new' => true,
                'variants' => [
                    ['name' => 'Gói 250g (2 phần)', 'name_en' => '250g (2 servings)', 'price' => 45000, 'stock' => 200],
                    ['name' => 'Gói 500g (4 phần)', 'name_en' => '500g (4 servings)', 'price' => 85000, 'sale_price' => 76000, 'stock' => 120],
                ],
            ],
            [
                'category' => 'Mì & Bún khô',
                'name' => 'Bánh canh bột lọc', 'name_en' => 'Tapioca Thick Noodles',
                'description' => 'Bánh canh bột lọc trong, dai, nấu giò heo ngon.',
                'description_en' => 'Clear tapioca thick noodles, chewy, great with pork knuckle soup.',
                'min_price' => 15000,
                'variants' => [
                    ['name' => 'Gói 300g', 'name_en' => '300g', 'price' => 15000, 'stock' => 250],
                    ['name' => 'Gói 500g', 'name_en' => '500g', 'price' => 22000, 'stock' => 180],
                ],
            ],
            [
                'category' => 'Mì & Bún khô',
                'name' => 'Mì udon khô', 'name_en' => 'Dried Udon Noodles',
                'description' => 'Mì udon khô Nhật, sợi dày mềm mịn, nấu kitsune udon.',
                'description_en' => 'Japanese dried udon, thick and soft, for kitsune udon.',
                'min_price' => 50000, 'is_new' => true,
                'variants' => [
                    ['name' => 'Gói 300g', 'name_en' => '300g', 'price' => 50000, 'stock' => 150],
                    ['name' => 'Gói 600g', 'name_en' => '600g', 'price' => 92000, 'sale_price' => 82000, 'stock' => 80],
                ],
            ],
            [
                'category' => 'Mì & Bún khô',
                'name' => 'Bún bò Huế khô (dẹt)', 'name_en' => 'Dried Bun Bo Hue Noodles',
                'description' => 'Bún bò Huế khô sợi dẹt dày, đặc trưng miền Trung.',
                'description_en' => 'Flat thick dried noodles, specialty of Central Vietnam.',
                'min_price' => 20000,
                'variants' => [
                    ['name' => 'Gói 400g', 'name_en' => '400g', 'price' => 20000, 'stock' => 300],
                    ['name' => 'Gói 800g', 'name_en' => '800g', 'price' => 36000, 'stock' => 180],
                ],
            ],
            [
                'category' => 'Mì & Bún khô',
                'name' => 'Mì soba (kiều mạch)', 'name_en' => 'Soba Noodles (Buckwheat)',
                'description' => 'Mì soba kiều mạch Nhật, ăn lạnh hoặc nóng đều ngon.',
                'description_en' => 'Japanese buckwheat soba, delicious hot or cold.',
                'min_price' => 55000, 'is_new' => true,
                'variants' => [
                    ['name' => 'Gói 270g', 'name_en' => '270g', 'price' => 55000, 'stock' => 120],
                    ['name' => 'Gói 540g', 'name_en' => '540g', 'price' => 100000, 'sale_price' => 90000, 'stock' => 80],
                ],
            ],

            // ═══════════════ TRỨNG & SỮA (10) ═══════════════
            [
                'category' => 'Trứng & Sữa',
                'name' => 'Trứng gà ta', 'name_en' => 'Free-range Chicken Eggs',
                'description' => 'Trứng gà ta tươi, lòng đỏ vàng ươm, dinh dưỡng cao.',
                'description_en' => 'Fresh free-range eggs, deep yellow yolk, nutritious.',
                'min_price' => 35000, 'is_hot' => true,
                'variants' => [
                    ['name' => 'Vỉ 10 quả', 'name_en' => '10-egg tray', 'price' => 35000, 'stock' => 500],
                    ['name' => 'Vỉ 30 quả', 'name_en' => '30-egg tray', 'price' => 98000, 'sale_price' => 89000, 'stock' => 200],
                ],
            ],
            [
                'category' => 'Trứng & Sữa',
                'name' => 'Trứng vịt muối', 'name_en' => 'Salted Duck Eggs',
                'description' => 'Trứng vịt muối truyền thống, lòng đỏ cứng dầu chảy.',
                'description_en' => 'Traditional salted duck eggs, oily firm yolk.',
                'min_price' => 12000,
                'variants' => [
                    ['name' => '1 quả',  'name_en' => '1 egg', 'price' => 12000, 'stock' => 400],
                    ['name' => '6 quả',  'name_en' => '6 eggs', 'price' => 68000, 'sale_price' => 60000, 'stock' => 200],
                    ['name' => '12 quả', 'name_en' => '12 eggs', 'price' => 130000, 'stock' => 100],
                ],
            ],
            [
                'category' => 'Trứng & Sữa',
                'name' => 'Sữa tươi Vinamilk', 'name_en' => 'Vinamilk Fresh Milk',
                'description' => 'Sữa tươi tiệt trùng Vinamilk 100%, không đường.',
                'description_en' => 'Vinamilk UHT fresh milk 100%, unsweetened.',
                'min_price' => 28000, 'is_hot' => true,
                'variants' => [
                    ['name' => 'Hộp 1 lít',   'name_en' => '1L carton', 'price' => 28000, 'stock' => 400],
                    ['name' => 'Lốc 4 hộp 1L', 'name_en' => '4x1L pack', 'price' => 105000, 'sale_price' => 95000, 'stock' => 200],
                ],
            ],
            [
                'category' => 'Trứng & Sữa',
                'name' => 'Phô mai mozzarella', 'name_en' => 'Mozzarella Cheese',
                'description' => 'Mozzarella block tươi, kéo dài, dùng pizza và pasta.',
                'description_en' => 'Fresh mozzarella block, stretchy, for pizza and pasta.',
                'min_price' => 75000, 'is_hot' => true,
                'variants' => [
                    ['name' => '200g', 'price' => 75000, 'stock' => 150],
                    ['name' => '400g', 'price' => 140000, 'sale_price' => 125000, 'stock' => 100],
                ],
            ],
            [
                'category' => 'Trứng & Sữa',
                'name' => 'Sữa chua Vinamilk có đường', 'name_en' => 'Vinamilk Sweetened Yogurt',
                'description' => 'Sữa chua Vinamilk creamy, đặc mịn, lên men tự nhiên.',
                'description_en' => 'Vinamilk creamy yogurt, smooth, naturally fermented.',
                'min_price' => 8000,
                'variants' => [
                    ['name' => 'Hũ 100g',       'name_en' => '100g cup', 'price' => 8000, 'stock' => 500],
                    ['name' => 'Lốc 4 hũ 100g', 'name_en' => '4x100g', 'price' => 30000, 'sale_price' => 27000, 'stock' => 300],
                ],
            ],
            [
                'category' => 'Trứng & Sữa',
                'name' => 'Bơ nhạt Lurpak', 'name_en' => 'Lurpak Unsalted Butter',
                'description' => 'Bơ Lurpak Đan Mạch, vị kem nhẹ thanh, chuyên làm bánh.',
                'description_en' => 'Danish Lurpak butter, light creamy taste, professional baker choice.',
                'min_price' => 65000, 'is_new' => true,
                'variants' => [
                    ['name' => '200g', 'price' => 65000, 'stock' => 150],
                    ['name' => '400g', 'price' => 125000, 'sale_price' => 112000, 'stock' => 80],
                ],
            ],
            [
                'category' => 'Trứng & Sữa',
                'name' => 'Phô mai Parmesan bào', 'name_en' => 'Grated Parmesan',
                'description' => 'Parmesan bào sẵn Galbani, rắc pasta và risotto.',
                'description_en' => 'Galbani grated parmesan, ready for pasta and risotto.',
                'min_price' => 85000, 'is_new' => true,
                'variants' => [
                    ['name' => '100g', 'price' => 85000, 'stock' => 100],
                    ['name' => '200g', 'price' => 160000, 'sale_price' => 145000, 'stock' => 70],
                ],
            ],
            [
                'category' => 'Trứng & Sữa',
                'name' => 'Kem whipping cream', 'name_en' => 'Whipping Cream',
                'description' => 'Kem tươi Anchor 35% fat, đánh bông trang trí bánh.',
                'description_en' => 'Anchor whipping cream 35% fat, for whipping cake decoration.',
                'min_price' => 55000, 'is_hot' => true,
                'variants' => [
                    ['name' => 'Hộp 250ml', 'name_en' => '250ml', 'price' => 55000, 'stock' => 200],
                    ['name' => 'Hộp 500ml', 'name_en' => '500ml', 'price' => 100000, 'sale_price' => 90000, 'stock' => 120],
                ],
            ],
            [
                'category' => 'Trứng & Sữa',
                'name' => 'Trứng cút luộc sẵn', 'name_en' => 'Pre-cooked Quail Eggs',
                'description' => 'Trứng cút luộc sẵn, bóc vỏ, tiện làm lẩu hoặc kho.',
                'description_en' => 'Pre-cooked peeled quail eggs, convenient for hotpot or braising.',
                'min_price' => 25000,
                'variants' => [
                    ['name' => 'Túi 20 quả', 'name_en' => '20 eggs', 'price' => 25000, 'stock' => 300],
                    ['name' => 'Túi 40 quả', 'name_en' => '40 eggs', 'price' => 45000, 'sale_price' => 40000, 'stock' => 200],
                ],
            ],
            [
                'category' => 'Trứng & Sữa',
                'name' => 'Sữa đặc có đường Ông Thọ', 'name_en' => 'Condensed Milk (Ong Tho)',
                'description' => 'Sữa đặc có đường Ông Thọ, pha cà phê hoặc làm bánh.',
                'description_en' => 'Sweetened condensed milk, for coffee or baking.',
                'min_price' => 18000,
                'variants' => [
                    ['name' => 'Lon 380g', 'name_en' => '380g can', 'price' => 18000, 'stock' => 400],
                    ['name' => 'Lon 1.2kg', 'name_en' => '1.2kg can', 'price' => 52000, 'sale_price' => 47000, 'stock' => 200],
                ],
            ],

            // ═══════════════ ĐỒ HỘP & CHẾ BIẾN (10) ═══════════════
            [
                'category' => 'Đồ hộp & Chế biến',
                'name' => 'Cá hộp Hạ Long', 'name_en' => 'Ha Long Canned Fish',
                'description' => 'Cá thu sốt cà Hạ Long, tiện lợi, ăn ngay.',
                'description_en' => 'Ha Long mackerel in tomato sauce, convenient and ready-to-eat.',
                'min_price' => 20000,
                'variants' => [
                    ['name' => 'Hộp 155g', 'name_en' => '155g can', 'price' => 20000, 'stock' => 400],
                    ['name' => 'Thùng 24 hộp', 'name_en' => '24-can case', 'price' => 440000, 'sale_price' => 399000, 'stock' => 50],
                ],
            ],
            [
                'category' => 'Đồ hộp & Chế biến',
                'name' => 'Đậu xanh đóng hộp', 'name_en' => 'Canned Green Peas',
                'description' => 'Đậu xanh nấu sẵn đóng hộp, tiện thêm vào salad hoặc súp.',
                'description_en' => 'Pre-cooked canned green peas, convenient for salads or soups.',
                'min_price' => 18000,
                'variants' => [
                    ['name' => 'Lon 400g', 'name_en' => '400g can', 'price' => 18000, 'stock' => 300],
                    ['name' => 'Lon 800g', 'name_en' => '800g can', 'price' => 32000, 'stock' => 200],
                ],
            ],
            [
                'category' => 'Đồ hộp & Chế biến',
                'name' => 'Cà chua hộp xay nhuyễn', 'name_en' => 'Canned Crushed Tomatoes',
                'description' => 'Cà chua Ý xay nhuyễn, làm sốt pasta hoặc pizza.',
                'description_en' => 'Italian crushed tomatoes, for pasta sauce or pizza base.',
                'min_price' => 28000, 'is_new' => true,
                'variants' => [
                    ['name' => 'Lon 400g', 'name_en' => '400g can', 'price' => 28000, 'stock' => 250],
                    ['name' => 'Lon 800g', 'name_en' => '800g can', 'price' => 50000, 'sale_price' => 45000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Đồ hộp & Chế biến',
                'name' => 'Dứa (Thơm) đóng hộp', 'name_en' => 'Canned Pineapple',
                'description' => 'Dứa khoanh đóng hộp nước đường, tiện làm bánh hoặc salad.',
                'description_en' => 'Sliced pineapple in syrup, convenient for baking or salad.',
                'min_price' => 22000,
                'variants' => [
                    ['name' => 'Lon 565g', 'name_en' => '565g can', 'price' => 22000, 'stock' => 300],
                ],
            ],
            [
                'category' => 'Đồ hộp & Chế biến',
                'name' => 'Ngô (Bắp) ngọt đóng hộp', 'name_en' => 'Canned Sweet Corn',
                'description' => 'Bắp ngọt nguyên hạt đóng hộp, tiện trộn salad.',
                'description_en' => 'Whole kernel canned sweet corn, great for salads.',
                'min_price' => 20000,
                'variants' => [
                    ['name' => 'Lon 340g', 'name_en' => '340g can', 'price' => 20000, 'stock' => 350],
                    ['name' => 'Lon 580g', 'name_en' => '580g can', 'price' => 32000, 'sale_price' => 29000, 'stock' => 200],
                ],
            ],
            [
                'category' => 'Đồ hộp & Chế biến',
                'name' => 'Patê gan hộp', 'name_en' => 'Canned Pate',
                'description' => 'Patê gan hộp Hạ Long, thoa bánh mì ăn sáng.',
                'description_en' => 'Ha Long canned pate, spread on bread for breakfast.',
                'min_price' => 20000,
                'variants' => [
                    ['name' => 'Hộp 180g', 'name_en' => '180g', 'price' => 20000, 'stock' => 400],
                    ['name' => 'Hộp 350g', 'name_en' => '350g', 'price' => 35000, 'sale_price' => 31000, 'stock' => 250],
                ],
            ],
            [
                'category' => 'Đồ hộp & Chế biến',
                'name' => 'Thịt hộp Spam', 'name_en' => 'Spam Canned Meat',
                'description' => 'Thịt hộp Spam nhập khẩu, chiên hoặc kẹp sandwich.',
                'description_en' => 'Imported Spam canned meat, fry or use in sandwiches.',
                'min_price' => 75000, 'is_new' => true,
                'variants' => [
                    ['name' => 'Hộp 200g', 'name_en' => '200g', 'price' => 75000, 'stock' => 200],
                    ['name' => 'Hộp 340g', 'name_en' => '340g', 'price' => 120000, 'sale_price' => 108000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Đồ hộp & Chế biến',
                'name' => 'Đậu phụ non đóng gói', 'name_en' => 'Packaged Silken Tofu',
                'description' => 'Đậu phụ non mịn, đóng gói hút chân không, hạn sử dụng dài.',
                'description_en' => 'Smooth silken tofu, vacuum-packed for longer shelf life.',
                'min_price' => 15000,
                'variants' => [
                    ['name' => 'Hộp 300g', 'name_en' => '300g', 'price' => 15000, 'stock' => 300],
                    ['name' => 'Hộp 600g', 'name_en' => '600g', 'price' => 25000, 'stock' => 200],
                ],
            ],
            [
                'category' => 'Đồ hộp & Chế biến',
                'name' => 'Nước cốt dừa đóng lon', 'name_en' => 'Canned Coconut Milk',
                'description' => 'Nước cốt dừa Aroy-D, béo thơm, nấu cà ri và chè.',
                'description_en' => 'Aroy-D coconut milk, rich and fragrant, for curry and desserts.',
                'min_price' => 22000, 'is_hot' => true,
                'variants' => [
                    ['name' => 'Lon 400ml', 'name_en' => '400ml can', 'price' => 22000, 'stock' => 300],
                    ['name' => 'Hộp 1L',   'name_en' => '1L pack', 'price' => 45000, 'sale_price' => 40000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Đồ hộp & Chế biến',
                'name' => 'Đậu đỏ nấu sẵn', 'name_en' => 'Canned Kidney Beans',
                'description' => 'Đậu đỏ đóng hộp chín mềm, dùng nấu chè hoặc hầm súp.',
                'description_en' => 'Pre-cooked canned kidney beans, for dessert soup or stew.',
                'min_price' => 20000,
                'variants' => [
                    ['name' => 'Lon 400g', 'name_en' => '400g can', 'price' => 20000, 'stock' => 250],
                ],
            ],

            // ═══════════════ SNACK & BÁNH KẸO (10) ═══════════════
            [
                'category' => 'Snack & Bánh kẹo',
                'name' => 'Bánh quy bơ Danisa', 'name_en' => 'Danisa Butter Cookies',
                'description' => 'Bánh quy bơ Danisa Đan Mạch, tan trên đầu lưỡi.',
                'description_en' => 'Danish Danisa butter cookies, melt-in-your-mouth.',
                'min_price' => 55000, 'is_hot' => true,
                'variants' => [
                    ['name' => 'Hộp 200g', 'name_en' => '200g tin', 'price' => 55000, 'stock' => 200],
                    ['name' => 'Hộp 454g', 'name_en' => '454g tin', 'price' => 120000, 'sale_price' => 108000, 'stock' => 120],
                ],
            ],
            [
                'category' => 'Snack & Bánh kẹo',
                'name' => 'Snack khoai tây Pringles', 'name_en' => 'Pringles Potato Crisps',
                'description' => 'Pringles vị Original, giòn tan, không vỡ nát.',
                'description_en' => 'Pringles Original flavor, crispy, no crumbling.',
                'min_price' => 48000, 'is_hot' => true,
                'variants' => [
                    ['name' => 'Lon 110g', 'name_en' => '110g can', 'price' => 48000, 'stock' => 300],
                    ['name' => 'Lon 165g', 'name_en' => '165g can', 'price' => 72000, 'sale_price' => 64000, 'stock' => 200],
                ],
            ],
            [
                'category' => 'Snack & Bánh kẹo',
                'name' => 'Kẹo dẻo Haribo', 'name_en' => 'Haribo Gummy Bears',
                'description' => 'Kẹo dẻo gấu Haribo Goldbaeren nhiều màu sắc.',
                'description_en' => 'Haribo Goldbaeren colorful gummy bears.',
                'min_price' => 35000,
                'variants' => [
                    ['name' => 'Gói 100g', 'name_en' => '100g', 'price' => 35000, 'stock' => 300],
                    ['name' => 'Gói 200g', 'name_en' => '200g', 'price' => 65000, 'sale_price' => 58000, 'stock' => 200],
                ],
            ],
            [
                'category' => 'Snack & Bánh kẹo',
                'name' => 'Chocolate đen Lindt 70%', 'name_en' => 'Lindt Dark Chocolate 70%',
                'description' => 'Lindt Excellence 70% cacao, đắng nhẹ, thơm ngon.',
                'description_en' => 'Lindt Excellence 70% dark chocolate, mildly bitter and aromatic.',
                'min_price' => 85000, 'is_new' => true,
                'variants' => [
                    ['name' => 'Thanh 100g', 'name_en' => '100g bar', 'price' => 85000, 'stock' => 150],
                    ['name' => 'Thanh 200g', 'name_en' => '200g bar', 'price' => 155000, 'sale_price' => 139000, 'stock' => 80],
                ],
            ],
            [
                'category' => 'Snack & Bánh kẹo',
                'name' => 'Bánh xốp Orion', 'name_en' => 'Orion Wafer',
                'description' => 'Bánh xốp kem sô cô la Orion, giòn ngọt.',
                'description_en' => 'Orion chocolate cream wafer, crispy and sweet.',
                'min_price' => 20000,
                'variants' => [
                    ['name' => 'Gói 187g', 'name_en' => '187g', 'price' => 20000, 'stock' => 400],
                    ['name' => 'Hộp 350g', 'name_en' => '350g box', 'price' => 38000, 'sale_price' => 34000, 'stock' => 250],
                ],
            ],
            [
                'category' => 'Snack & Bánh kẹo',
                'name' => 'Bỏng ngô caramel', 'name_en' => 'Caramel Popcorn',
                'description' => 'Bỏng ngô caramel giòn tan, không chất bảo quản.',
                'description_en' => 'Crispy caramel popcorn, preservative-free.',
                'min_price' => 25000,
                'variants' => [
                    ['name' => 'Gói 80g',  'name_en' => '80g', 'price' => 25000, 'stock' => 300],
                    ['name' => 'Gói 150g', 'name_en' => '150g', 'price' => 42000, 'sale_price' => 38000, 'stock' => 200],
                ],
            ],
            [
                'category' => 'Snack & Bánh kẹo',
                'name' => 'Bánh mì nướng giòn Kinh Đô', 'name_en' => 'Kinh Do Crispy Toast',
                'description' => 'Bánh mì nướng giòn Kinh Đô, ăn sáng hoặc ăn vặt.',
                'description_en' => 'Kinh Do crispy toasted bread, for breakfast or snack.',
                'min_price' => 18000,
                'variants' => [
                    ['name' => 'Hộp 200g', 'name_en' => '200g', 'price' => 18000, 'stock' => 400],
                    ['name' => 'Hộp 400g', 'name_en' => '400g', 'price' => 32000, 'stock' => 250],
                ],
            ],
            [
                'category' => 'Snack & Bánh kẹo',
                'name' => 'Hạt điều rang muối', 'name_en' => 'Salted Roasted Cashews',
                'description' => 'Hạt điều rang muối W240, hạt to đều, giòn bùi.',
                'description_en' => 'W240 salted roasted cashews, large and crunchy.',
                'min_price' => 65000, 'is_hot' => true,
                'variants' => [
                    ['name' => '100g', 'price' => 65000, 'stock' => 200],
                    ['name' => '200g', 'price' => 120000, 'sale_price' => 108000, 'stock' => 150],
                    ['name' => '500g', 'price' => 280000, 'stock' => 80],
                ],
            ],
            [
                'category' => 'Snack & Bánh kẹo',
                'name' => 'Kẹo cao su Extra', 'name_en' => 'Extra Chewing Gum',
                'description' => 'Kẹo cao su Extra vị bạc hà, trắng răng, hơi thở thơm mát.',
                'description_en' => 'Extra peppermint gum, whitening, fresh breath.',
                'min_price' => 12000,
                'variants' => [
                    ['name' => 'Vỉ 10 viên', 'name_en' => '10-piece pack', 'price' => 12000, 'stock' => 500],
                    ['name' => 'Hộp 46 viên', 'name_en' => '46-piece box', 'price' => 45000, 'sale_price' => 40000, 'stock' => 300],
                ],
            ],
            [
                'category' => 'Snack & Bánh kẹo',
                'name' => 'Bánh tráng nướng Tây Ninh', 'name_en' => 'Tay Ninh Grilled Rice Paper',
                'description' => 'Bánh tráng nướng giòn đặc sản Tây Ninh, phết mỡ hành, tôm khô.',
                'description_en' => 'Crispy Tay Ninh specialty rice paper with scallion oil and dried shrimp.',
                'min_price' => 15000, 'is_hot' => true,
                'variants' => [
                    ['name' => 'Gói 5 cái',  'name_en' => '5-piece bag', 'price' => 15000, 'stock' => 300],
                    ['name' => 'Gói 10 cái', 'name_en' => '10-piece bag', 'price' => 28000, 'sale_price' => 25000, 'stock' => 200],
                ],
            ],

            // ═══════════════ ĐỒ UỐNG (10) ═══════════════
            [
                'category' => 'Đồ uống',
                'name' => 'Nước ép cam tươi', 'name_en' => 'Fresh Orange Juice',
                'description' => 'Nước cam ép tươi 100%, không thêm đường, đóng chai.',
                'description_en' => '100% fresh-squeezed orange juice, no added sugar.',
                'min_price' => 28000, 'is_hot' => true,
                'variants' => [
                    ['name' => 'Chai 330ml', 'name_en' => '330ml', 'price' => 28000, 'stock' => 200],
                    ['name' => 'Chai 1 lít',  'name_en' => '1L', 'price' => 75000, 'sale_price' => 68000, 'stock' => 120],
                ],
            ],
            [
                'category' => 'Đồ uống',
                'name' => 'Trà xanh 0 độ C', 'name_en' => '0 Degrees Green Tea',
                'description' => 'Trà xanh 0 độ C không đường, thanh mát tự nhiên.',
                'description_en' => '0-degree unsweetened green tea, naturally refreshing.',
                'min_price' => 10000,
                'variants' => [
                    ['name' => 'Chai 350ml', 'name_en' => '350ml', 'price' => 10000, 'stock' => 500],
                    ['name' => 'Lốc 6 chai', 'name_en' => '6-pack', 'price' => 55000, 'sale_price' => 49000, 'stock' => 200],
                ],
            ],
            [
                'category' => 'Đồ uống',
                'name' => 'Cà phê hòa tan G7', 'name_en' => 'G7 Instant Coffee',
                'description' => 'G7 3in1 đậm đà, tiện lợi, cà phê Buôn Ma Thuột.',
                'description_en' => 'G7 3-in-1 instant coffee, rich Buon Ma Thuot blend.',
                'min_price' => 35000, 'is_hot' => true,
                'variants' => [
                    ['name' => 'Hộp 18 gói', 'name_en' => '18-sachet box', 'price' => 35000, 'stock' => 300],
                    ['name' => 'Hộp 50 gói', 'name_en' => '50-sachet box', 'price' => 88000, 'sale_price' => 79000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Đồ uống',
                'name' => 'Nước dừa tươi nguyên trái', 'name_en' => 'Fresh Whole Coconut',
                'description' => 'Dừa tươi Bến Tre nguyên trái, nước ngọt thanh, cơm dày.',
                'description_en' => 'Ben Tre fresh coconut, sweet water, thick flesh.',
                'min_price' => 20000, 'is_hot' => true,
                'variants' => [
                    ['name' => '1 trái ~500ml', 'name_en' => '1 coconut ~500ml', 'price' => 20000, 'stock' => 300],
                    ['name' => '3 trái',        'name_en' => '3 coconuts', 'price' => 55000, 'sale_price' => 49000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Đồ uống',
                'name' => 'Sữa đậu nành Fami', 'name_en' => 'Fami Soy Milk',
                'description' => 'Sữa đậu nành Fami nguyên chất, giàu protein thực vật.',
                'description_en' => 'Fami pure soy milk, rich in plant protein.',
                'min_price' => 8000,
                'variants' => [
                    ['name' => 'Hộp 200ml',   'name_en' => '200ml carton', 'price' => 8000, 'stock' => 500],
                    ['name' => 'Lốc 4 hộp',   'name_en' => '4-pack 200ml', 'price' => 29000, 'sale_price' => 26000, 'stock' => 300],
                    ['name' => 'Chai 1 lít',   'name_en' => '1L bottle', 'price' => 28000, 'stock' => 200],
                ],
            ],
            [
                'category' => 'Đồ uống',
                'name' => 'Nước ép ổi Twister', 'name_en' => 'Twister Guava Juice',
                'description' => 'Nước ép ổi Twister chua ngọt, mát lạnh giải nhiệt.',
                'description_en' => 'Twister guava juice, sweet-sour, refreshing.',
                'min_price' => 12000,
                'variants' => [
                    ['name' => 'Chai 400ml', 'name_en' => '400ml', 'price' => 12000, 'stock' => 400],
                ],
            ],
            [
                'category' => 'Đồ uống',
                'name' => 'Matcha bột Nhật Uji', 'name_en' => 'Uji Japanese Matcha Powder',
                'description' => 'Matcha Uji grade A, màu xanh tươi đẹp, pha latte hoặc làm bánh.',
                'description_en' => 'Grade A Uji matcha, vibrant green, for lattes or baking.',
                'min_price' => 120000, 'is_new' => true,
                'variants' => [
                    ['name' => 'Hộp 50g',  'name_en' => '50g tin', 'price' => 120000, 'stock' => 100],
                    ['name' => 'Hộp 100g', 'name_en' => '100g tin', 'price' => 220000, 'sale_price' => 198000, 'stock' => 60],
                ],
            ],
            [
                'category' => 'Đồ uống',
                'name' => 'Bột ca cao nguyên chất', 'name_en' => 'Pure Cocoa Powder',
                'description' => 'Bột ca cao Bensdorp Hà Lan, đắng đậm, không đường.',
                'description_en' => 'Dutch Bensdorp pure cocoa powder, rich and unsweetened.',
                'min_price' => 55000,
                'variants' => [
                    ['name' => 'Hộp 250g', 'name_en' => '250g', 'price' => 55000, 'stock' => 150],
                    ['name' => 'Hộp 500g', 'name_en' => '500g', 'price' => 100000, 'sale_price' => 89000, 'stock' => 100],
                ],
            ],
            [
                'category' => 'Đồ uống',
                'name' => 'Trà nhúng Lipton', 'name_en' => 'Lipton Tea Bags',
                'description' => 'Trà túi nhúng Lipton Yellow Label, hương vị truyền thống.',
                'description_en' => 'Lipton Yellow Label tea bags, classic flavor.',
                'min_price' => 28000,
                'variants' => [
                    ['name' => 'Hộp 25 túi', 'name_en' => '25-bag box', 'price' => 28000, 'stock' => 300],
                    ['name' => 'Hộp 100 túi', 'name_en' => '100-bag box', 'price' => 95000, 'sale_price' => 85000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Đồ uống',
                'name' => 'Sinh tố bơ đóng chai', 'name_en' => 'Bottled Avocado Smoothie',
                'description' => 'Sinh tố bơ Đắk Lắk đóng chai tươi mỗi ngày, sữa tươi nguyên kem.',
                'description_en' => 'Daily fresh bottled Dak Lak avocado smoothie with whole milk.',
                'min_price' => 35000, 'is_new' => true,
                'variants' => [
                    ['name' => 'Chai 300ml', 'name_en' => '300ml', 'price' => 35000, 'stock' => 150],
                    ['name' => 'Chai 500ml', 'name_en' => '500ml', 'price' => 55000, 'sale_price' => 49000, 'stock' => 100],
                ],
            ],

            // ═══════════════ THỰC PHẨM KHÔ (10) ═══════════════
            [
                'category' => 'Thực phẩm khô',
                'name' => 'Đậu phộng rang muối', 'name_en' => 'Salted Roasted Peanuts',
                'description' => 'Đậu phộng rang muối giòn bùi, ăn vặt hoặc nấu ăn.',
                'description_en' => 'Salted roasted peanuts, crunchy, for snacking or cooking.',
                'min_price' => 20000,
                'variants' => [
                    ['name' => '200g', 'price' => 20000, 'stock' => 300],
                    ['name' => '500g', 'price' => 45000, 'sale_price' => 40000, 'stock' => 200],
                ],
            ],
            [
                'category' => 'Thực phẩm khô',
                'name' => 'Đậu đen hữu cơ', 'name_en' => 'Organic Black Beans',
                'description' => 'Đậu đen hữu cơ, nấu xôi đậu đen hoặc chè.',
                'description_en' => 'Organic black beans for sticky rice or sweet soup.',
                'min_price' => 30000,
                'variants' => [
                    ['name' => '500g', 'price' => 30000, 'stock' => 200],
                    ['name' => '1kg',  'price' => 55000, 'sale_price' => 50000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Thực phẩm khô',
                'name' => 'Hạt sen khô', 'name_en' => 'Dried Lotus Seeds',
                'description' => 'Hạt sen khô Đồng Tháp, nấu chè hoặc hầm canh.',
                'description_en' => 'Dong Thap dried lotus seeds for dessert soup or stew.',
                'min_price' => 45000, 'is_hot' => true,
                'variants' => [
                    ['name' => '200g', 'price' => 45000, 'stock' => 150],
                    ['name' => '500g', 'price' => 105000, 'sale_price' => 95000, 'stock' => 80],
                ],
            ],
            [
                'category' => 'Thực phẩm khô',
                'name' => 'Mộc nhĩ (Nấm mèo) khô', 'name_en' => 'Dried Wood Ear Mushroom',
                'description' => 'Mộc nhĩ khô ngâm nở to, dai giòn, dùng xào hoặc nhân nem.',
                'description_en' => 'Dried wood ear mushrooms, crispy when rehydrated, for stir-fry.',
                'min_price' => 30000,
                'variants' => [
                    ['name' => '100g', 'price' => 30000, 'stock' => 200],
                    ['name' => '200g', 'price' => 55000, 'sale_price' => 49000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Thực phẩm khô',
                'name' => 'Nấm đông cô khô', 'name_en' => 'Dried Shiitake Mushrooms',
                'description' => 'Nấm đông cô Hàn Quốc khô, hương thơm đậm đà.',
                'description_en' => 'Korean dried shiitake mushrooms, intensely fragrant.',
                'min_price' => 55000, 'is_hot' => true,
                'variants' => [
                    ['name' => '100g', 'price' => 55000, 'stock' => 150],
                    ['name' => '200g', 'price' => 100000, 'sale_price' => 90000, 'stock' => 100],
                ],
            ],
            [
                'category' => 'Thực phẩm khô',
                'name' => 'Đậu lăng đỏ', 'name_en' => 'Red Lentils',
                'description' => 'Đậu lăng đỏ nhập khẩu, nấu dal hoặc súp dinh dưỡng.',
                'description_en' => 'Imported red lentils for dal or nutritious soups.',
                'min_price' => 35000, 'is_new' => true,
                'variants' => [
                    ['name' => '400g', 'price' => 35000, 'stock' => 200],
                    ['name' => '800g', 'price' => 65000, 'sale_price' => 58000, 'stock' => 120],
                ],
            ],
            [
                'category' => 'Thực phẩm khô',
                'name' => 'Khoai tây chiên đông lạnh', 'name_en' => 'Frozen French Fries',
                'description' => 'Khoai tây lát chiên đông lạnh McCain, giòn vàng không cần giải đông.',
                'description_en' => 'McCain frozen French fries, crispy golden, no thawing needed.',
                'min_price' => 50000,
                'variants' => [
                    ['name' => 'Túi 750g', 'name_en' => '750g', 'price' => 50000, 'stock' => 200],
                    ['name' => 'Túi 1.5kg', 'name_en' => '1.5kg', 'price' => 92000, 'sale_price' => 82000, 'stock' => 100],
                ],
            ],
            [
                'category' => 'Thực phẩm khô',
                'name' => 'Vừng (Mè) trắng rang', 'name_en' => 'Toasted White Sesame Seeds',
                'description' => 'Mè trắng rang sẵn, rắc cơm, salad và làm bánh.',
                'description_en' => 'Pre-toasted white sesame seeds for rice, salads or baking.',
                'min_price' => 20000,
                'variants' => [
                    ['name' => '100g', 'price' => 20000, 'stock' => 300],
                    ['name' => '250g', 'price' => 45000, 'sale_price' => 40000, 'stock' => 200],
                ],
            ],
            [
                'category' => 'Thực phẩm khô',
                'name' => 'Bột mì đa dụng Bakers Choice', 'name_en' => 'Bakers Choice All-purpose Flour',
                'description' => 'Bột mì số 8 đa dụng, làm bánh mì và bánh ngọt.',
                'description_en' => 'No.8 all-purpose flour, for bread and pastry.',
                'min_price' => 22000,
                'variants' => [
                    ['name' => 'Túi 1kg', 'name_en' => '1kg', 'price' => 22000, 'stock' => 400],
                    ['name' => 'Túi 5kg', 'name_en' => '5kg', 'price' => 100000, 'sale_price' => 90000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Thực phẩm khô',
                'name' => 'Bột bánh mì Anchor', 'name_en' => 'Anchor Bread Flour',
                'description' => 'Bột mì số 13 protein cao, làm bánh mì dai giòn.',
                'description_en' => 'High-protein bread flour, for chewy crusty bread.',
                'min_price' => 28000,
                'variants' => [
                    ['name' => 'Túi 1kg', 'name_en' => '1kg', 'price' => 28000, 'stock' => 300],
                    ['name' => 'Túi 5kg', 'name_en' => '5kg', 'price' => 128000, 'sale_price' => 115000, 'stock' => 120],
                ],
            ],

            // ═══════════════ NẤM & ĐẬU PHỤ (10) ═══════════════
            [
                'category' => 'Nấm & Đậu phụ',
                'name' => 'Nấm kim châm tươi', 'name_en' => 'Enoki Mushrooms',
                'description' => 'Nấm kim châm tươi trắng giòn, nhúng lẩu hoặc xào.',
                'description_en' => 'Fresh enoki mushrooms, white and crispy, for hotpot or stir-fry.',
                'min_price' => 15000, 'is_hot' => true,
                'variants' => [
                    ['name' => 'Gói 150g', 'name_en' => '150g pack', 'price' => 15000, 'stock' => 300],
                    ['name' => 'Gói 300g', 'name_en' => '300g pack', 'price' => 28000, 'sale_price' => 25000, 'stock' => 200],
                ],
            ],
            [
                'category' => 'Nấm & Đậu phụ',
                'name' => 'Nấm hương tươi', 'name_en' => 'Fresh Shiitake Mushrooms',
                'description' => 'Nấm hương tươi Đà Lạt, mũ dày, mùi thơm đặc trưng.',
                'description_en' => 'Fresh Da Lat shiitake, thick cap, distinctive aroma.',
                'min_price' => 35000, 'is_hot' => true,
                'variants' => [
                    ['name' => '200g', 'price' => 35000, 'stock' => 200],
                    ['name' => '400g', 'price' => 65000, 'sale_price' => 58000, 'stock' => 120],
                ],
            ],
            [
                'category' => 'Nấm & Đậu phụ',
                'name' => 'Nấm rơm tươi', 'name_en' => 'Fresh Straw Mushrooms',
                'description' => 'Nấm rơm tươi búp nhỏ, nước dùng ngọt trong.',
                'description_en' => 'Fresh small button straw mushrooms, sweet clear broth.',
                'min_price' => 20000,
                'variants' => [
                    ['name' => '200g', 'price' => 20000, 'stock' => 250],
                    ['name' => '400g', 'price' => 38000, 'sale_price' => 34000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Nấm & Đậu phụ',
                'name' => 'Đậu phụ cứng', 'name_en' => 'Firm Tofu',
                'description' => 'Đậu phụ cứng truyền thống, chiên vàng hoặc kho.',
                'description_en' => 'Traditional firm tofu, great fried golden or braised.',
                'min_price' => 12000,
                'variants' => [
                    ['name' => 'Miếng ~200g', 'name_en' => '~200g block', 'price' => 12000, 'stock' => 500],
                    ['name' => '2 miếng ~400g', 'name_en' => '2 blocks ~400g', 'price' => 22000, 'sale_price' => 20000, 'stock' => 300],
                ],
            ],
            [
                'category' => 'Nấm & Đậu phụ',
                'name' => 'Đậu phụ chiên sẵn', 'name_en' => 'Pre-fried Tofu',
                'description' => 'Đậu phụ chiên vàng sẵn, tiện nhúng lẩu hoặc xào.',
                'description_en' => 'Pre-fried golden tofu, convenient for hotpot or stir-fry.',
                'min_price' => 18000,
                'variants' => [
                    ['name' => 'Gói 250g', 'name_en' => '250g', 'price' => 18000, 'stock' => 300],
                    ['name' => 'Gói 500g', 'name_en' => '500g', 'price' => 32000, 'sale_price' => 29000, 'stock' => 200],
                ],
            ],
            [
                'category' => 'Nấm & Đậu phụ',
                'name' => 'Nấm đùi gà tươi', 'name_en' => 'King Oyster Mushrooms',
                'description' => 'Nấm đùi gà tươi cuống dày giòn, xào bơ tỏi ngon.',
                'description_en' => 'Fresh king oyster mushrooms, thick crunchy stem, great sautéed in garlic butter.',
                'min_price' => 30000, 'is_new' => true,
                'variants' => [
                    ['name' => '200g', 'price' => 30000, 'stock' => 200],
                    ['name' => '400g', 'price' => 55000, 'sale_price' => 49000, 'stock' => 120],
                ],
            ],
            [
                'category' => 'Nấm & Đậu phụ',
                'name' => 'Nấm linh chi đỏ', 'name_en' => 'Red Reishi Mushroom',
                'description' => 'Nấm linh chi đỏ sấy khô, hãm trà dưỡng sinh.',
                'description_en' => 'Dried red reishi mushroom, steep as health tea.',
                'min_price' => 120000, 'is_new' => true,
                'variants' => [
                    ['name' => '100g', 'price' => 120000, 'stock' => 100],
                    ['name' => '200g', 'price' => 220000, 'sale_price' => 198000, 'stock' => 60],
                ],
            ],
            [
                'category' => 'Nấm & Đậu phụ',
                'name' => 'Đậu hũ ky (Váng đậu)', 'name_en' => 'Tofu Skin (Bean Curd Sheet)',
                'description' => 'Váng đậu khô thành phẩm, ngâm mềm dùng cuộn hoặc hầm.',
                'description_en' => 'Dried tofu skin, rehydrate for wrapping or stewing.',
                'min_price' => 25000,
                'variants' => [
                    ['name' => '100g', 'price' => 25000, 'stock' => 200],
                    ['name' => '200g', 'price' => 45000, 'sale_price' => 40000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Nấm & Đậu phụ',
                'name' => 'Nấm bào ngư xám', 'name_en' => 'Grey Oyster Mushrooms',
                'description' => 'Nấm bào ngư xám tươi, hương thơm nhẹ, mọng nước.',
                'description_en' => 'Fresh grey oyster mushrooms, mild aroma, juicy texture.',
                'min_price' => 22000,
                'variants' => [
                    ['name' => '200g', 'price' => 22000, 'stock' => 250],
                    ['name' => '400g', 'price' => 40000, 'sale_price' => 36000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Nấm & Đậu phụ',
                'name' => 'Đậu hũ trứng chiên giòn', 'name_en' => 'Crispy Egg Tofu',
                'description' => 'Đậu hũ trứng chiên vàng giòn, mềm bên trong, chấm tương ngon.',
                'description_en' => 'Crispy-fried egg tofu, soft inside, great with dipping sauce.',
                'min_price' => 25000, 'is_hot' => true,
                'variants' => [
                    ['name' => 'Ống 150g', 'name_en' => '150g tube', 'price' => 25000, 'stock' => 300],
                    ['name' => 'Ống 300g', 'name_en' => '300g tube', 'price' => 45000, 'sale_price' => 40000, 'stock' => 200],
                ],
            ],

            // ═══════════════ TRÁI CÂY (15) ═══════════════
            [
                'category' => 'Trái cây',
                'name' => 'Chuối tiêu', 'name_en' => 'Cavendish Banana',
                'description' => 'Chuối tiêu chín vàng đều, ngọt mềm, giàu kali.',
                'description_en' => 'Ripe Cavendish bananas, sweet and soft, potassium-rich.',
                'min_price' => 20000, 'is_hot' => true,
                'variants' => [
                    ['name' => 'Nải ~1kg', 'name_en' => 'Bunch ~1kg', 'price' => 20000, 'stock' => 400],
                    ['name' => 'Nải ~2kg', 'name_en' => 'Bunch ~2kg', 'price' => 38000, 'sale_price' => 34000, 'stock' => 250],
                ],
            ],
            [
                'category' => 'Trái cây',
                'name' => 'Xoài cát Hòa Lộc', 'name_en' => 'Hoa Loc Mango',
                'description' => 'Xoài Hòa Lộc vàng óng, thịt dày ngọt thơm, hạt lép.',
                'description_en' => 'Hoa Loc mango, golden yellow, thick sweet flesh, small seed.',
                'min_price' => 45000, 'is_hot' => true,
                'variants' => [
                    ['name' => '500g (~1-2 trái)', 'name_en' => '500g (~1-2 fruits)', 'price' => 45000, 'stock' => 200],
                    ['name' => '1kg (~3-4 trái)',  'name_en' => '1kg (~3-4 fruits)', 'price' => 85000, 'sale_price' => 75000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Trái cây',
                'name' => 'Bưởi Năm Roi', 'name_en' => 'Nam Roi Pomelo',
                'description' => 'Bưởi Năm Roi Vĩnh Long, múi ráo ngọt thanh, ít hạt.',
                'description_en' => 'Vinh Long Nam Roi pomelo, dry sweet segments, few seeds.',
                'min_price' => 35000,
                'variants' => [
                    ['name' => '~1 trái 1kg', 'name_en' => '~1 fruit 1kg', 'price' => 35000, 'stock' => 200],
                    ['name' => '~1 trái 1.5kg', 'name_en' => '~1 fruit 1.5kg', 'price' => 50000, 'sale_price' => 44000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Trái cây',
                'name' => 'Dưa hấu không hạt', 'name_en' => 'Seedless Watermelon',
                'description' => 'Dưa hấu không hạt ruột đỏ giòn, ngọt mát.',
                'description_en' => 'Seedless watermelon, crispy red flesh, sweet and cool.',
                'min_price' => 35000,
                'variants' => [
                    ['name' => '~1.5kg (1/2 quả)', 'name_en' => '~1.5kg (half)', 'price' => 35000, 'stock' => 150],
                    ['name' => '~3kg (cả quả)',    'name_en' => '~3kg (whole)', 'price' => 65000, 'sale_price' => 58000, 'stock' => 100],
                ],
            ],
            [
                'category' => 'Trái cây',
                'name' => 'Thanh long ruột đỏ', 'name_en' => 'Red Dragon Fruit',
                'description' => 'Thanh long ruột đỏ Long An, ngọt sắc giàu antioxidant.',
                'description_en' => 'Long An red dragon fruit, sharp sweetness, antioxidant-rich.',
                'min_price' => 30000, 'is_hot' => true,
                'variants' => [
                    ['name' => '500g (~1-2 trái)', 'name_en' => '500g (~1-2 fruits)', 'price' => 30000, 'stock' => 200],
                    ['name' => '1kg (~3-4 trái)',  'name_en' => '1kg (~3-4 fruits)', 'price' => 55000, 'sale_price' => 49000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Trái cây',
                'name' => 'Sầu riêng Ri6', 'name_en' => 'Ri6 Durian',
                'description' => 'Sầu riêng Ri6 Cái Mơn, cơm vàng khô ráo, béo ngậy.',
                'description_en' => 'Cai Mon Ri6 durian, dry yellow flesh, rich and creamy.',
                'min_price' => 85000, 'is_hot' => true,
                'variants' => [
                    ['name' => '~1kg cơm (đã tách)', 'name_en' => '~1kg pulp', 'price' => 85000, 'stock' => 100],
                    ['name' => '~2kg cơm',           'name_en' => '~2kg pulp', 'price' => 165000, 'sale_price' => 149000, 'stock' => 60],
                ],
            ],
            [
                'category' => 'Trái cây',
                'name' => 'Nhãn lồng Hưng Yên', 'name_en' => 'Hung Yen Longan',
                'description' => 'Nhãn lồng Hưng Yên mùa, cơm dày ngọt đậm.',
                'description_en' => 'Seasonal Hung Yen longan, thick flesh, intensely sweet.',
                'min_price' => 50000, 'is_new' => true,
                'variants' => [
                    ['name' => '500g', 'price' => 50000, 'stock' => 150],
                    ['name' => '1kg',  'price' => 95000, 'sale_price' => 85000, 'stock' => 100],
                ],
            ],
            [
                'category' => 'Trái cây',
                'name' => 'Dâu tây Đà Lạt', 'name_en' => 'Da Lat Strawberry',
                'description' => 'Dâu tây Đà Lạt đỏ hồng, thơm ngọt nhẹ chua.',
                'description_en' => 'Da Lat strawberries, rosy red, sweet and slightly tart.',
                'min_price' => 45000, 'is_hot' => true,
                'variants' => [
                    ['name' => '250g', 'price' => 45000, 'stock' => 150],
                    ['name' => '500g', 'price' => 85000, 'sale_price' => 75000, 'stock' => 100],
                ],
            ],
            [
                'category' => 'Trái cây',
                'name' => 'Vú sữa Lò Rèn', 'name_en' => 'Lo Ren Star Apple',
                'description' => 'Vú sữa Lò Rèn Vĩnh Kim, tím ngát ngọt dịu, cực đặc sản.',
                'description_en' => 'Vinh Kim Lo Ren star apple, deep purple, mildly sweet.',
                'min_price' => 65000, 'is_new' => true,
                'variants' => [
                    ['name' => '500g (~3-4 trái)', 'name_en' => '500g (~3-4 fruits)', 'price' => 65000, 'stock' => 80],
                    ['name' => '1kg (~6-8 trái)',  'name_en' => '1kg (~6-8 fruits)', 'price' => 120000, 'sale_price' => 108000, 'stock' => 60],
                ],
            ],
            [
                'category' => 'Trái cây',
                'name' => 'Mít Thái siêu sớm', 'name_en' => 'Thai Jackfruit',
                'description' => 'Mít Thái sớm ngọt, múi vàng dày, giòn ráo.',
                'description_en' => 'Early Thai jackfruit, sweet yellow flesh, crispy.',
                'min_price' => 40000,
                'variants' => [
                    ['name' => 'Múi đóng gói 400g', 'name_en' => '400g packed segments', 'price' => 40000, 'stock' => 150],
                    ['name' => 'Múi đóng gói 800g', 'name_en' => '800g packed segments', 'price' => 75000, 'sale_price' => 68000, 'stock' => 100],
                ],
            ],
            [
                'category' => 'Trái cây',
                'name' => 'Cam sành Hàm Yên', 'name_en' => 'Ham Yen Green Tangerine',
                'description' => 'Cam sành Hàm Yên vỏ xanh nhiều nước chua ngọt.',
                'description_en' => 'Ham Yen green-skinned tangerine, juicy and balanced sweet-sour.',
                'min_price' => 28000,
                'variants' => [
                    ['name' => '500g (~4-5 quả)', 'name_en' => '500g (~4-5 fruits)', 'price' => 28000, 'stock' => 300],
                    ['name' => '1kg (~8-10 quả)',  'name_en' => '1kg (~8-10 fruits)', 'price' => 52000, 'sale_price' => 46000, 'stock' => 200],
                ],
            ],
            [
                'category' => 'Trái cây',
                'name' => 'Ổi không hạt', 'name_en' => 'Seedless Guava',
                'description' => 'Ổi không hạt giòn ngọt, ăn liền hoặc làm sinh tố.',
                'description_en' => 'Seedless guava, crispy and sweet, eat fresh or blend.',
                'min_price' => 20000,
                'variants' => [
                    ['name' => '500g', 'price' => 20000, 'stock' => 250],
                    ['name' => '1kg',  'price' => 36000, 'sale_price' => 32000, 'stock' => 180],
                ],
            ],
            [
                'category' => 'Trái cây',
                'name' => 'Chanh leo (Passion fruit)', 'name_en' => 'Passion Fruit',
                'description' => 'Chanh leo Sơn La chua ngọt, thơm đặc trưng.',
                'description_en' => 'Son La passion fruit, sweet-sour, distinctively aromatic.',
                'min_price' => 25000,
                'variants' => [
                    ['name' => '500g (~6-8 trái)', 'name_en' => '500g (~6-8 fruits)', 'price' => 25000, 'stock' => 200],
                    ['name' => '1kg (~12-16 trái)', 'name_en' => '1kg (~12-16 fruits)', 'price' => 45000, 'sale_price' => 40000, 'stock' => 150],
                ],
            ],
            [
                'category' => 'Trái cây',
                'name' => 'Bơ Booth', 'name_en' => 'Booth Avocado',
                'description' => 'Bơ Booth Đắk Lắk, vỏ đen bóng khi chín, thịt vàng béo.',
                'description_en' => 'Dak Lak Booth avocado, black skin when ripe, yellow creamy flesh.',
                'min_price' => 30000, 'is_hot' => true,
                'variants' => [
                    ['name' => '1 trái ~200g', 'name_en' => '1 fruit ~200g', 'price' => 30000, 'stock' => 200],
                    ['name' => '3 trái ~600g', 'name_en' => '3 fruits ~600g', 'price' => 85000, 'sale_price' => 75000, 'stock' => 120],
                ],
            ],
            [
                'category' => 'Trái cây',
                'name' => 'Măng cụt', 'name_en' => 'Mangosteen',
                'description' => 'Măng cụt Bình Dương chín đều, vỏ đỏ thẫm, múi trắng ngọt.',
                'description_en' => 'Binh Duong mangosteen, deep red skin, sweet white segments.',
                'min_price' => 55000, 'is_new' => true,
                'variants' => [
                    ['name' => '500g', 'price' => 55000, 'stock' => 120],
                    ['name' => '1kg',  'price' => 105000, 'sale_price' => 94000, 'stock' => 80],
                ],
            ],
        ];
    }
}