<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductVariant;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin SFood',
            'email' => 'admin@sfood.vn',
            'password' => Hash::make('password'),
            'phone' => '0901234567',
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Nguyễn Văn A',
            'email' => 'user@sfood.vn',
            'password' => Hash::make('password'),
            'phone' => '0912345678',
            'role' => 'user',
        ]);

        User::create([
            'name' => 'Nhân viên SFood',
            'email' => 'staff@sfood.vn',
            'password' => Hash::make('password'),
            'phone' => '0923456789',
            'role' => 'staff',
        ]);

        User::create([
            'name' => 'Chăm sóc khách hàng SFood',
            'email' => 'cskh@sfood.vn',
            'password' => Hash::make('password'),
            'phone' => '0934567890',
            'role' => 'customer_service',
        ]);

        $categories = [
            ['name' => 'Rau củ', 'slug' => 'rau-cu', 'sort_order' => 1],
            ['name' => 'Thịt cá', 'slug' => 'thit-ca', 'sort_order' => 2],
            ['name' => 'Đồ uống', 'slug' => 'do-uong', 'sort_order' => 3],
            ['name' => 'Gia vị', 'slug' => 'gia-vi', 'sort_order' => 4],
        ];

        foreach ($categories as $cat) {
            Category::create(array_merge($cat, [
                'description' => 'Danh mục '.$cat['name'],
                'is_active' => true,
            ]));
        }

        $products = [
            ['name' => 'Cà chua bi', 'category' => 'rau-cu', 'variants' => [
                ['name' => '500g', 'price' => 25000, 'stock' => 100],
                ['name' => '1kg', 'price' => 45000, 'sale_price' => 40000, 'stock' => 80],
            ]],
            ['name' => 'Rau muống', 'category' => 'rau-cu', 'variants' => [
                ['name' => 'Bó 300g', 'price' => 8000, 'stock' => 200],
            ]],
            ['name' => 'Thịt ba chỉ heo', 'category' => 'thit-ca', 'variants' => [
                ['name' => '500g', 'price' => 85000, 'stock' => 50],
                ['name' => '1kg', 'price' => 165000, 'stock' => 30],
            ]],
            ['name' => 'Cá hồi Na Uy', 'category' => 'thit-ca', 'variants' => [
                ['name' => '300g', 'price' => 180000, 'stock' => 25],
            ]],
            ['name' => 'Sữa tươi TH true MILK', 'category' => 'do-uong', 'variants' => [
                ['name' => 'Hộp 1L', 'price' => 32000, 'stock' => 150],
            ]],
            ['name' => 'Nước mắm Phú Quốc', 'category' => 'gia-vi', 'variants' => [
                ['name' => 'Chai 500ml', 'price' => 55000, 'stock' => 90],
                ['name' => 'Chai 1L', 'price' => 95000, 'stock' => 60],
            ]],
            ['name' => 'Hành tây', 'category' => 'rau-cu', 'variants' => [
                ['name' => '500g', 'price' => 18000, 'stock' => 120],
            ]],
            ['name' => 'Trứng gà ta', 'category' => 'thit-ca', 'variants' => [
                ['name' => 'Hộp 10 quả', 'price' => 45000, 'stock' => 100],
            ]],
        ];

        $variantMap = [];

        foreach ($products as $p) {
            $category = Category::where('slug', $p['category'])->first();
            $product = Product::create([
                'category_id' => $category->id,
                'name' => $p['name'],
                'slug' => Str::slug($p['name']),
                'description' => 'Sản phẩm '.$p['name'].' tươi ngon, chất lượng cao từ SFood.',
                'is_active' => true,
                'view_count' => rand(50, 500),
            ]);

            foreach ($p['variants'] as $i => $v) {
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'name' => $v['name'],
                    'sku' => 'SKU-'.strtoupper(Str::random(6)),
                    'price' => $v['price'],
                    'sale_price' => $v['sale_price'] ?? null,
                    'stock' => $v['stock'],
                    'is_active' => true,
                ]);
                $variantMap[$product->name][$i] = $variant;
            }
        }

        // Seed reviews for Cà chua bi
        $caChuaBi = Product::where('slug', 'ca-chua-bi')->first();
        if ($caChuaBi) {
            $reviewers = [
                [
                    'name' => 'Phạm Minh Trí',
                    'email' => 'tri.pham@example.com',
                    'rating' => 5,
                    'comment' => 'Cà chua bi rất tươi, chín đều và ngọt thanh. Tôi hay mua về làm salad hoặc cho con ăn sống rất tốt.',
                    'days_ago' => 2,
                ],
                [
                    'name' => 'Lê Thị Hồng',
                    'email' => 'hong.le@example.com',
                    'rating' => 5,
                    'comment' => 'Đóng gói cẩn thận, cà chua tươi rói không bị dập nát quả nào. Sẽ tiếp tục ủng hộ shop.',
                    'days_ago' => 5,
                ],
                [
                    'name' => 'Nguyễn Hoàng Nam',
                    'email' => 'nam.nguyen@example.com',
                    'rating' => 4,
                    'comment' => 'Cà chua ngon, giao hàng hơi lâu một chút nhưng bù lại chất lượng rất ổn định. Đáng mua nhé mọi người.',
                    'days_ago' => 7,
                ],
                [
                    'name' => 'Trần Thu Thảo',
                    'email' => 'thao.tran@example.com',
                    'rating' => 5,
                    'comment' => 'Quả nào quả nấy mọng nước, ăn giòn ngọt cực kỳ. Làm nước ép hay xào đều ngon.',
                    'days_ago' => 3,
                ],
            ];

            foreach ($reviewers as $rev) {
                $user = User::firstOrCreate(
                    ['email' => $rev['email']],
                    [
                        'name' => $rev['name'],
                        'password' => Hash::make('password'),
                        'phone' => '09' . rand(10000000, 99999999),
                        'role' => 'user',
                    ]
                );

                ProductReview::create([
                    'user_id' => $user->id,
                    'product_id' => $caChuaBi->id,
                    'rating' => $rev['rating'],
                    'comment' => $rev['comment'],
                    'created_at' => now()->subDays($rev['days_ago']),
                    'updated_at' => now()->subDays($rev['days_ago']),
                ]);
            }
        }

        $recipes = [
            [
                'title' => 'Canh chua cá hồi',
                'slug' => 'canh-chua-ca-hoi',
                'description' => "Bước 1: Sơ chế cá hồi, cắt miếng vừa ăn.\nBước 2: Phi thơm hành tây, cho cà chua vào xào.\nBước 3: Đổ nước sôi, nêm gia vị, cho cá vào nấu 10 phút.\nBước 4: Thêm rau muống, tắt bếp và thưởng thức.",
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'cook_time' => 30,
                'ingredients' => [
                    ['product' => 'Cá hồi Na Uy', 'variant' => 0, 'label' => '300g'],
                    ['product' => 'Cà chua bi', 'variant' => 0, 'label' => '200g'],
                    ['product' => 'Rau muống', 'variant' => 0, 'label' => '1 bó'],
                    ['product' => 'Hành tây', 'variant' => 0, 'label' => '1 củ'],
                    ['product' => 'Nước mắm Phú Quốc', 'variant' => 0, 'label' => '2 muỗng'],
                ],
            ],
            [
                'title' => 'Thịt ba chỉ rim cà chua',
                'slug' => 'thit-ba-chi-rim-ca-chua',
                'description' => "Bước 1: Thịt ba chỉ cắt miếng vuông, ướp nước mắm.\nBước 2: Xào thịt săn lại.\nBước 3: Cho cà chua vào rim nhỏ lửa 25 phút.\nBước 4: Dọn ra đĩa, ăn kèm cơm nóng.",
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'cook_time' => 45,
                'ingredients' => [
                    ['product' => 'Thịt ba chỉ heo', 'variant' => 0, 'label' => '500g'],
                    ['product' => 'Cà chua bi', 'variant' => 0, 'label' => '300g'],
                    ['product' => 'Hành tây', 'variant' => 0, 'label' => '1 củ'],
                    ['product' => 'Nước mắm Phú Quốc', 'variant' => 0, 'label' => '3 muỗng'],
                ],
            ],
            [
                'title' => 'Trứng chiên sữa',
                'slug' => 'trung-chien-sua',
                'description' => "Bước 1: Đánh trứng với sữa tươi.\nBước 2: Đổ vào chảo, chiên vàng hai mặt.\nBước 3: Cắt miếng, dọn ra đĩa.",
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'cook_time' => 15,
                'ingredients' => [
                    ['product' => 'Trứng gà ta', 'variant' => 0, 'label' => '4 quả'],
                    ['product' => 'Sữa tươi TH true MILK', 'variant' => 0, 'label' => '100ml'],
                ],
            ],
        ];

        foreach ($recipes as $r) {
            $recipe = Recipe::create([
                'title' => $r['title'],
                'slug' => $r['slug'],
                'description' => $r['description'],
                'video_url' => $r['video_url'],
                'cook_time' => $r['cook_time'],
                'is_published' => true,
                'view_count' => rand(100, 1000),
            ]);

            foreach ($r['ingredients'] as $ing) {
                $variant = $variantMap[$ing['product']][$ing['variant']] ?? null;
                if ($variant) {
                    RecipeIngredient::create([
                        'recipe_id' => $recipe->id,
                        'product_variant_id' => $variant->id,
                        'quantity_label' => $ing['label'],
                        'quantity' => 1,
                    ]);
                }
            }
        }

        Coupon::create([
            'code' => 'SFOOD10',
            'type' => 'percent',
            'value' => 10,
            'min_order_amount' => 100000,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonths(3),
            'usage_limit' => 100,
            'is_active' => true,
        ]);

        Coupon::create([
            'code' => 'FREESHIP',
            'type' => 'free_shipping',
            'value' => 0,
            'min_order_amount' => 50000,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonths(1),
            'is_active' => true,
        ]);
    }
}
