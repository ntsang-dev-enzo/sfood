<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class RecipeSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('recipe_ingredients')->truncate();
        DB::table('recipes')->truncate();
        DB::table('recipe_categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $now = Carbon::now();

        // ── Recipe Categories ──────────────────────────────────────
        $catIds = [];
        $categories = [
            ['name' => 'Món canh & súp',   'slug' => 'mon-canh-sup'],
            ['name' => 'Món xào',           'slug' => 'mon-xao'],
            ['name' => 'Món kho & hầm',     'slug' => 'mon-kho-ham'],
            ['name' => 'Món chiên & nướng', 'slug' => 'mon-chien-nuong'],
            ['name' => 'Món luộc & hấp',   'slug' => 'mon-luoc-hap'],
            ['name' => 'Món lẩu',           'slug' => 'mon-lau'],
        ];
        foreach ($categories as $cat) {
            $catIds[$cat['name']] = DB::table('recipe_categories')->insertGetId([
                'name'       => $cat['name'],
                'slug'       => $cat['slug'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // ── Helper: lấy variant_id theo tên sản phẩm ──────────────
        $variantCache = [];
        $getVariant = function (string $productName) use (&$variantCache): ?int {
            if (!isset($variantCache[$productName])) {
                $row = DB::table('product_variants as pv')
                    ->join('products as p', 'p.id', '=', 'pv.product_id')
                    ->where('p.name', $productName)
                    ->orderBy('pv.price')
                    ->select('pv.id')
                    ->first();
                $variantCache[$productName] = $row?->id;
            }
            return $variantCache[$productName];
        };

        // ── 30 Recipes ────────────────────────────────────────────
        $recipes = $this->recipeData($catIds);

        foreach ($recipes as $idx => $r) {
            $recipeId = DB::table('recipes')->insertGetId([
                'title'              => $r['title'],
                'title_en'           => $r['title_en'],
                'slug'               => Str::slug($r['title']) . '-' . ($idx + 1),
                'description'        => $r['description'],
                'description_en'     => $r['description_en'],
                'thumbnail'          => $r['thumbnail'],
                'video_url'          => $r['video_url'],
                'cook_time'          => $r['cook_time'],
                'recipe_category_id' => $r['category_id'],
                'is_published'       => true,
                'view_count'         => rand(100, 5000),
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);

            foreach ($r['ingredients'] as $ing) {
                $variantId = $getVariant($ing['product']);
                if (!$variantId) continue;
                DB::table('recipe_ingredients')->insert([
                    'recipe_id'          => $recipeId,
                    'product_variant_id' => $variantId,
                    'quantity_label'     => $ing['label'],
                    'quantity'           => $ing['qty'] ?? 1,
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ]);
            }
        }

        $this->command->info('✅  Đã seed ' . count($recipes) . ' công thức nấu ăn thành công.');
    }

    // ──────────────────────────────────────────────────────────────
    private function recipeData(array $catIds): array
    {
        $canh  = $catIds['Món canh & súp'];
        $xao   = $catIds['Món xào'];
        $kho   = $catIds['Món kho & hầm'];
        $chien = $catIds['Món chiên & nướng'];
        $luoc  = $catIds['Món luộc & hấp'];
        $lau   = $catIds['Món lẩu'];

        return [
            // 1 ── Canh chua cá hồi
            [
                'title'       => 'Canh chua cá hồi',
                'title_en'    => 'Salmon Sour Soup',
                'description' => "Bước 1: Cá hồi rửa sạch, cắt khúc vừa ăn, ướp muối nhẹ 5 phút.\nBước 2: Phi thơm hành tím, cho cà chua vào xào chín mềm.\nBước 3: Đổ 1.5 lít nước sôi, nêm nước mắm, đường, muối.\nBước 4: Cho cá vào nấu 8-10 phút đến khi chín.\nBước 5: Thêm giá đỗ, rau thơm, tắt bếp. Dùng nóng kèm cơm.",
                'description_en' => "Step 1: Clean and cut salmon, lightly salt for 5 mins.\nStep 2: Sauté shallots, add tomatoes and cook until soft.\nStep 3: Add 1.5L boiling water, season with fish sauce.\nStep 4: Add salmon, cook 8-10 mins.\nStep 5: Add bean sprouts and herbs, serve hot.",
                'thumbnail'   => 'https://images.unsplash.com/photo-1547592180-85f173990554?w=600',
                'video_url'   => 'https://www.youtube.com/watch?v=wq2sCPnkHFA',
                'cook_time'   => 25,
                'category_id' => $canh,
                'ingredients' => [
                    ['product' => 'Cà chua',      'label' => '2 quả (300g)',   'qty' => 1],
                    ['product' => 'Giá đỗ',       'label' => '200g',           'qty' => 1],
                    ['product' => 'Hành tây',     'label' => '½ củ',           'qty' => 1],
                    ['product' => 'Ngò rí (Mùi tàu)', 'label' => '1 nắm nhỏ', 'qty' => 1],
                ],
            ],
            // 2 ── Canh bắp cải nấu thịt
            [
                'title'       => 'Canh bắp cải nấu thịt heo',
                'title_en'    => 'Cabbage Pork Soup',
                'description' => "Bước 1: Thịt heo băm nhỏ, ướp hành, muối, tiêu.\nBước 2: Bắp cải rửa sạch, thái miếng vừa ăn.\nBước 3: Đun sôi nước, cho thịt vào nấu tan, vớt bọt.\nBước 4: Thêm bắp cải vào nấu 10 phút.\nBước 5: Nêm nêm vừa khẩu vị, thêm hành ngò thái nhỏ.",
                'description_en' => "Step 1: Mince pork, marinate with shallots and seasoning.\nStep 2: Wash and chop cabbage.\nStep 3: Bring water to boil, add pork, skim foam.\nStep 4: Add cabbage, cook 10 mins.\nStep 5: Season to taste, garnish with green onions.",
                'thumbnail'   => 'https://images.unsplash.com/photo-1604152135912-04a022e23696?w=600',
                'video_url'   => 'https://www.youtube.com/watch?v=HMzHi0KLGC4',
                'cook_time'   => 25,
                'category_id' => $canh,
                'ingredients' => [
                    ['product' => 'Bắp cải tươi',   'label' => '½ cây (500g)',  'qty' => 1],
                    ['product' => 'Thịt heo xay sẵn', 'label' => '200g',        'qty' => 1],
                    ['product' => 'Hành tây',        'label' => '½ củ',         'qty' => 1],
                    ['product' => 'Gừng tươi',       'label' => '1 lát mỏng',   'qty' => 1],
                ],
            ],
            // 3 ── Canh khổ qua nhồi thịt
            [
                'title'       => 'Canh khổ qua nhồi thịt',
                'title_en'    => 'Stuffed Bitter Melon Soup',
                'description' => "Bước 1: Khổ qua rửa sạch, cắt khúc, bỏ hột.\nBước 2: Thịt xay trộn với mộc nhĩ, miến, nêm gia vị, nhồi vào khổ qua.\nBước 3: Đun nước sôi, cho khổ qua nhồi vào nấu lửa nhỏ 30 phút.\nBước 4: Nêm muối, nước mắm vừa ăn, thêm hành ngò.",
                'description_en' => "Step 1: Clean bitter melon, cut into sections, remove seeds.\nStep 2: Mix minced pork with wood ear mushroom, glass noodles, stuff into melon.\nStep 3: Simmer stuffed melon in boiling water on low heat 30 mins.\nStep 4: Season with salt and fish sauce, garnish.",
                'thumbnail'   => 'https://images.unsplash.com/photo-1569050467447-ce54b3bbc37d?w=600',
                'video_url'   => 'https://www.youtube.com/watch?v=4IfShLaJQes',
                'cook_time'   => 45,
                'category_id' => $canh,
                'ingredients' => [
                    ['product' => 'Khổ qua (Mướp đắng)', 'label' => '3 trái (500g)', 'qty' => 1],
                    ['product' => 'Thịt heo xay sẵn',    'label' => '300g',          'qty' => 1],
                    ['product' => 'Ngò rí (Mùi tàu)',     'label' => '1 nắm',         'qty' => 1],
                ],
            ],
            // 4 ── Canh cải thảo nấu tôm
            [
                'title'       => 'Canh cải thảo nấu tôm',
                'title_en'    => 'Napa Cabbage Shrimp Soup',
                'description' => "Bước 1: Tôm rửa sạch, bóc vỏ, giữ đầu để nấu nước dùng.\nBước 2: Cải thảo rửa sạch, cắt khúc 4cm.\nBước 3: Đun sôi nước từ đầu tôm, vớt bỏ đầu, cho tôm và cải vào.\nBước 4: Nấu 8 phút, nêm vừa ăn.",
                'description_en' => "Step 1: Clean shrimp, shell them, keep heads for broth.\nStep 2: Wash and cut napa cabbage.\nStep 3: Boil water with shrimp heads, discard heads, add shrimp and cabbage.\nStep 4: Cook 8 mins, season to taste.",
                'thumbnail'   => 'https://images.unsplash.com/photo-1563245372-f21724e3856d?w=600',
                'video_url'   => 'https://www.youtube.com/watch?v=E5jJZ3XoKqw',
                'cook_time'   => 20,
                'category_id' => $canh,
                'ingredients' => [
                    ['product' => 'Cải thảo',       'label' => '½ cây (500g)', 'qty' => 1],
                    ['product' => 'Gừng tươi',      'label' => '2 lát',        'qty' => 1],
                    ['product' => 'Hành tây',       'label' => '½ củ',         'qty' => 1],
                ],
            ],
            // 5 ── Canh rau dền tôm khô
            [
                'title'       => 'Canh rau dền nấu tôm',
                'title_en'    => 'Red Amaranth Soup with Shrimp',
                'description' => "Bước 1: Rau dền nhặt sạch, rửa kỹ.\nBước 2: Tôm rửa sạch, giã hoặc băm nhuyễn.\nBước 3: Phi thơm hành, xào tôm, đổ nước sôi vào.\nBước 4: Cho rau dền vào nấu 5 phút, nêm vừa ăn.",
                'description_en' => "Step 1: Clean red amaranth leaves.\nStep 2: Clean and mince shrimp.\nStep 3: Sauté shallots with shrimp, add boiling water.\nStep 4: Add amaranth, cook 5 mins, season.",
                'thumbnail'   => 'https://images.unsplash.com/photo-1466637574441-749b8f19452f?w=600',
                'video_url'   => 'https://www.youtube.com/watch?v=yA3E1PbSI6k',
                'cook_time'   => 15,
                'category_id' => $canh,
                'ingredients' => [
                    ['product' => 'Rau dền đỏ', 'label' => '300g', 'qty' => 1],
                    ['product' => 'Hành tây',   'label' => '¼ củ', 'qty' => 1],
                    ['product' => 'Tỏi tươi',   'label' => '2 tép', 'qty' => 1],
                ],
            ],
            // 6 ── Rau muống xào tỏi
            [
                'title'       => 'Rau muống xào tỏi',
                'title_en'    => 'Stir-fried Morning Glory with Garlic',
                'description' => "Bước 1: Rau muống nhặt bỏ lá già, rửa sạch, để ráo.\nBước 2: Tỏi đập dập, băm nhuyễn.\nBước 3: Đun nóng dầu, phi tỏi vàng thơm.\nBước 4: Cho rau vào xào lửa to 3-4 phút, nêm nước mắm, muối.\nBước 5: Bày ra đĩa, dùng nóng.",
                'description_en' => "Step 1: Pick morning glory leaves, wash and drain.\nStep 2: Crush and mince garlic.\nStep 3: Heat oil, fry garlic until golden.\nStep 4: Add greens, stir-fry on high heat 3-4 mins, season.\nStep 5: Serve immediately.",
                'thumbnail'   => 'https://images.unsplash.com/photo-1540189549336-e6e99c3679fe?w=600',
                'video_url'   => 'https://www.youtube.com/watch?v=OjFEHoKoJgg',
                'cook_time'   => 10,
                'category_id' => $xao,
                'ingredients' => [
                    ['product' => 'Rau muống tươi', 'label' => '500g',   'qty' => 1],
                    ['product' => 'Tỏi tươi',       'label' => '4 tép',  'qty' => 1],
                    ['product' => 'Ớt sừng đỏ',     'label' => '1 trái', 'qty' => 1],
                ],
            ],
            // 7 ── Cải xanh xào thịt bò
            [
                'title'       => 'Cải xanh xào thịt bò',
                'title_en'    => 'Stir-fried Green Mustard with Beef',
                'description' => "Bước 1: Thịt bò thái mỏng, ướp tỏi, nước mắm, dầu hào 15 phút.\nBước 2: Cải xanh rửa sạch, thái khúc.\nBước 3: Xào bò lửa to đến khi tái, cho ra đĩa.\nBước 4: Xào tỏi, thêm cải xanh, xào 3 phút.\nBước 5: Cho bò trở lại, đảo đều, nêm vừa ăn.",
                'description_en' => "Step 1: Thinly slice beef, marinate with garlic, fish sauce, oyster sauce 15 mins.\nStep 2: Wash and cut mustard greens.\nStep 3: Stir-fry beef on high heat until rare, set aside.\nStep 4: Fry garlic, add greens, stir-fry 3 mins.\nStep 5: Return beef, toss together, season.",
                'thumbnail'   => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=600',
                'video_url'   => 'https://www.youtube.com/watch?v=mKuFpkTOA3Q',
                'cook_time'   => 20,
                'category_id' => $xao,
                'ingredients' => [
                    ['product' => 'Cải xanh',       'label' => '400g',  'qty' => 1],
                    ['product' => 'Thịt bò thăn nội', 'label' => '200g', 'qty' => 1],
                    ['product' => 'Tỏi tươi',       'label' => '3 tép', 'qty' => 1],
                    ['product' => 'Ớt sừng đỏ',     'label' => '1 trái','qty' => 1],
                ],
            ],
            // 8 ── Đậu cove xào trứng
            [
                'title'       => 'Đậu cove xào trứng',
                'title_en'    => 'Green Beans Stir-fried with Eggs',
                'description' => "Bước 1: Đậu cove rửa sạch, bẻ 2 đầu, chần sơ qua nước sôi.\nBước 2: Đánh tan trứng với muối, tiêu.\nBước 3: Xào đậu cove với tỏi phi thơm.\nBước 4: Đổ trứng vào đảo đều đến khi trứng chín.\nBước 5: Nêm vừa ăn, dọn ra đĩa.",
                'description_en' => "Step 1: Trim and blanch green beans.\nStep 2: Beat eggs with salt and pepper.\nStep 3: Stir-fry beans with garlic.\nStep 4: Pour in eggs, stir until cooked.\nStep 5: Season and serve.",
                'thumbnail'   => 'https://images.unsplash.com/photo-1563379091339-03246963d7f3?w=600',
                'video_url'   => 'https://www.youtube.com/watch?v=dI-1GDfY6UY',
                'cook_time'   => 15,
                'category_id' => $xao,
                'ingredients' => [
                    ['product' => 'Đậu cove',   'label' => '300g',  'qty' => 1],
                    ['product' => 'Tỏi tươi',   'label' => '2 tép', 'qty' => 1],
                    ['product' => 'Ớt sừng đỏ', 'label' => '½ trái','qty' => 1],
                ],
            ],
            // 9 ── Cà chua xào trứng
            [
                'title'       => 'Cà chua xào trứng',
                'title_en'    => 'Tomato and Egg Stir-fry',
                'description' => "Bước 1: Cà chua rửa, bổ múi cau.\nBước 2: Đánh tan trứng, chiên tơi rồi xúc ra.\nBước 3: Xào cà chua với hành, nêm đường, muối.\nBước 4: Cho trứng trở lại, đảo nhanh, nêm vừa ăn.\nBước 5: Rắc hành ngò thái nhỏ.",
                'description_en' => "Step 1: Wash and wedge tomatoes.\nStep 2: Beat eggs, scramble and set aside.\nStep 3: Stir-fry tomatoes with shallots, season.\nStep 4: Return eggs, toss quickly.\nStep 5: Garnish with green onions.",
                'thumbnail'   => 'https://images.unsplash.com/photo-1590301157890-4810ed352733?w=600',
                'video_url'   => 'https://www.youtube.com/watch?v=K6gw8rjiqd4',
                'cook_time'   => 12,
                'category_id' => $xao,
                'ingredients' => [
                    ['product' => 'Cà chua',            'label' => '3 quả (400g)', 'qty' => 1],
                    ['product' => 'Hành tây',           'label' => '½ củ',         'qty' => 1],
                    ['product' => 'Ngò rí (Mùi tàu)',   'label' => '1 nắm',        'qty' => 1],
                ],
            ],
            // 10 ── Ngô xào bơ
            [
                'title'       => 'Ngô xào bơ tỏi',
                'title_en'    => 'Garlic Butter Sweet Corn',
                'description' => "Bước 1: Tách hạt ngô ra khỏi bắp.\nBước 2: Phi thơm tỏi với bơ lửa vừa.\nBước 3: Cho ngô vào xào đều 5-7 phút.\nBước 4: Nêm muối, tiêu, thêm chút ớt.\nBước 5: Tắt bếp, rắc ngò rí.",
                'description_en' => "Step 1: Shuck corn kernels.\nStep 2: Sauté garlic in butter over medium heat.\nStep 3: Add corn, stir-fry 5-7 mins.\nStep 4: Season with salt, pepper, chili.\nStep 5: Turn off heat, garnish with cilantro.",
                'thumbnail'   => 'https://images.unsplash.com/photo-1551754655-cd27e38d2076?w=600',
                'video_url'   => 'https://www.youtube.com/watch?v=bnH55BOqOCk',
                'cook_time'   => 15,
                'category_id' => $xao,
                'ingredients' => [
                    ['product' => 'Ngô (Bắp) ngọt',    'label' => '3 trái',  'qty' => 3],
                    ['product' => 'Tỏi tươi',           'label' => '3 tép',   'qty' => 1],
                    ['product' => 'Ngò rí (Mùi tàu)',   'label' => '1 nắm',   'qty' => 1],
                    ['product' => 'Ớt sừng đỏ',         'label' => '1 trái',  'qty' => 1],
                ],
            ],
            // 11 ── Thịt ba chỉ kho trứng
            [
                'title'       => 'Thịt ba chỉ kho trứng',
                'title_en'    => 'Braised Pork Belly with Eggs',
                'description' => "Bước 1: Thịt ba chỉ cắt khối 3cm, chần sơ qua nước sôi.\nBước 2: Ướp thịt với nước mắm, đường, tiêu 30 phút.\nBước 3: Thắng đường làm màu, cho thịt vào xào đều.\nBước 4: Thêm nước dừa, hành, nấu lửa nhỏ 45 phút.\nBước 5: Cho trứng luộc vào kho thêm 15 phút.",
                'description_en' => "Step 1: Cut pork belly into 3cm cubes, blanch.\nStep 2: Marinate with fish sauce, sugar, pepper 30 mins.\nStep 3: Caramelize sugar, add pork and coat.\nStep 4: Add coconut water, shallots, simmer 45 mins.\nStep 5: Add boiled eggs, braise 15 more mins.",
                'thumbnail'   => 'https://images.unsplash.com/photo-1529042410759-befb1204b468?w=600',
                'video_url'   => 'https://www.youtube.com/watch?v=3SjF3NjvBjY',
                'cook_time'   => 90,
                'category_id' => $kho,
                'ingredients' => [
                    ['product' => 'Thịt ba chỉ heo', 'label' => '500g',   'qty' => 1],
                    ['product' => 'Hành tây',         'label' => '1 củ',   'qty' => 1],
                    ['product' => 'Tỏi tươi',         'label' => '4 tép',  'qty' => 1],
                ],
            ],
            // 12 ── Gà kho gừng
            [
                'title'       => 'Gà kho gừng',
                'title_en'    => 'Ginger Braised Chicken',
                'description' => "Bước 1: Gà chặt miếng, rửa sạch, ướp gừng, nước mắm, tỏi 20 phút.\nBước 2: Đun nóng dầu, xào gà đến khi vàng đều.\nBước 3: Thêm nước xâm xấp, nêm đường, kho lửa vừa 30 phút.\nBước 4: Kho cạn dần đến khi sốt sánh, múc ra đĩa.",
                'description_en' => "Step 1: Chop chicken, marinate with ginger, fish sauce, garlic 20 mins.\nStep 2: Heat oil, fry chicken until golden.\nStep 3: Add water just to cover, season with sugar, braise 30 mins.\nStep 4: Cook down until sauce thickens.",
                'thumbnail'   => 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?w=600',
                'video_url'   => 'https://www.youtube.com/watch?v=JNqeHSe9e_E',
                'cook_time'   => 50,
                'category_id' => $kho,
                'ingredients' => [
                    ['product' => 'Đùi gà tươi',  'label' => '4 đùi (500g)', 'qty' => 1],
                    ['product' => 'Gừng tươi',    'label' => '50g',           'qty' => 1],
                    ['product' => 'Tỏi tươi',     'label' => '4 tép',         'qty' => 1],
                    ['product' => 'Ớt sừng đỏ',   'label' => '2 trái',        'qty' => 1],
                ],
            ],
            // 13 ── Bò hầm cà rốt khoai tây
            [
                'title'       => 'Bò hầm cà rốt khoai tây',
                'title_en'    => 'Beef Stew with Carrot and Potato',
                'description' => "Bước 1: Bò bắp cắt khối, chần sơ. Cà rốt, khoai tây gọt vỏ cắt khối.\nBước 2: Xào bò với hành, tỏi, cà chua.\nBước 3: Thêm nước, tiêu, hầm 60 phút.\nBước 4: Cho cà rốt, khoai tây vào hầm tiếp 20 phút.\nBước 5: Nêm vừa ăn, ăn kèm bánh mì.",
                'description_en' => "Step 1: Cube beef shank, blanch. Peel and cube carrots and potatoes.\nStep 2: Sauté beef with onion, garlic, tomato.\nStep 3: Add water, pepper, simmer 60 mins.\nStep 4: Add carrots and potatoes, cook 20 more mins.\nStep 5: Season and serve with bread.",
                'thumbnail'   => 'https://images.unsplash.com/photo-1534939561126-855b8675edd7?w=600',
                'video_url'   => 'https://www.youtube.com/watch?v=8f_DhBpS9ow',
                'cook_time'   => 90,
                'category_id' => $kho,
                'ingredients' => [
                    ['product' => 'Thịt bò bắp',  'label' => '500g',  'qty' => 1],
                    ['product' => 'Cà rốt',        'label' => '2 củ',  'qty' => 2],
                    ['product' => 'Khoai tây',     'label' => '2 củ',  'qty' => 2],
                    ['product' => 'Cà chua',       'label' => '2 quả', 'qty' => 2],
                    ['product' => 'Hành tây',      'label' => '1 củ',  'qty' => 1],
                    ['product' => 'Tỏi tươi',      'label' => '4 tép', 'qty' => 1],
                ],
            ],
            // 14 ── Sườn kho đậu hủ
            [
                'title'       => 'Sườn non kho tiêu',
                'title_en'    => 'Braised Pork Ribs with Pepper',
                'description' => "Bước 1: Sườn chặt khúc 5cm, chần sơ bỏ bọt.\nBước 2: Ướp sườn với nước mắm, tiêu, tỏi 30 phút.\nBước 3: Phi thơm hành tỏi, xào sườn vàng.\nBước 4: Thêm nước xâm xấp, kho nhỏ lửa 40 phút đến khi cạn sánh.",
                'description_en' => "Step 1: Chop ribs into 5cm pieces, blanch.\nStep 2: Marinate with fish sauce, pepper, garlic 30 mins.\nStep 3: Sauté shallots and garlic, brown the ribs.\nStep 4: Add water, simmer 40 mins until sauce thickens.",
                'thumbnail'   => 'https://images.unsplash.com/photo-1476718406336-bb5a9690ee2a?w=600',
                'video_url'   => 'https://www.youtube.com/watch?v=7TKCHssMJMw',
                'cook_time'   => 60,
                'category_id' => $kho,
                'ingredients' => [
                    ['product' => 'Xương sườn heo non', 'label' => '500g',  'qty' => 1],
                    ['product' => 'Tỏi tươi',           'label' => '4 tép', 'qty' => 1],
                    ['product' => 'Hành tây',           'label' => '½ củ',  'qty' => 1],
                ],
            ],
            // 15 ── Gà luộc lá chanh
            [
                'title'       => 'Gà luộc lá chanh',
                'title_en'    => 'Boiled Chicken with Lime Leaves',
                'description' => "Bước 1: Gà rửa sạch, chà muối gừng khử mùi.\nBước 2: Cho gà vào nồi nước lạnh có gừng, hành, đun sôi rồi vặn nhỏ.\nBước 3: Luộc 25-30 phút, tắt bếp ngâm thêm 10 phút.\nBước 4: Vớt ra, để nguội, chặt miếng.\nBước 5: Pha nước chấm gừng, muối tiêu chanh.",
                'description_en' => "Step 1: Clean chicken, rub with salt and ginger.\nStep 2: Place in cold water with ginger and shallots, bring to boil then simmer.\nStep 3: Cook 25-30 mins, rest in hot water 10 mins.\nStep 4: Remove, cool, chop into pieces.\nStep 5: Prepare ginger dipping sauce.",
                'thumbnail'   => 'https://images.unsplash.com/photo-1600891964092-4316c288032e?w=600',
                'video_url'   => 'https://www.youtube.com/watch?v=m_QvNGZcqXw',
                'cook_time'   => 40,
                'category_id' => $luoc,
                'ingredients' => [
                    ['product' => 'Gà nguyên con', 'label' => '1 con (~1.2kg)', 'qty' => 1],
                    ['product' => 'Gừng tươi',     'label' => '50g',            'qty' => 1],
                    ['product' => 'Hành tây',      'label' => '1 củ',           'qty' => 1],
                ],
            ],
            // 16 ── Rau luộc chấm mắm tỏi
            [
                'title'       => 'Rau củ luộc chấm mắm tỏi ớt',
                'title_en'    => 'Boiled Vegetables with Garlic Fish Sauce',
                'description' => "Bước 1: Rửa sạch các loại rau củ.\nBước 2: Luộc lần lượt theo độ cứng: cà rốt → khoai tây → bắp cải → đậu cove.\nBước 3: Pha nước chấm: tỏi ớt giã nhuyễn, nước mắm, đường, chanh, nước sôi để nguội.\nBước 4: Bày rau ra đĩa, dùng kèm nước chấm.",
                'description_en' => "Step 1: Wash all vegetables.\nStep 2: Boil in order of hardness: carrot → potato → cabbage → beans.\nStep 3: Mix dipping sauce: garlic chili, fish sauce, sugar, lime, cooled boiled water.\nStep 4: Arrange on plate, serve with dipping sauce.",
                'thumbnail'   => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=600',
                'video_url'   => 'https://www.youtube.com/watch?v=pGLkCuGTBhk',
                'cook_time'   => 20,
                'category_id' => $luoc,
                'ingredients' => [
                    ['product' => 'Cà rốt',      'label' => '2 củ',   'qty' => 2],
                    ['product' => 'Khoai tây',   'label' => '2 củ',   'qty' => 2],
                    ['product' => 'Bắp cải tươi','label' => '¼ cây',  'qty' => 1],
                    ['product' => 'Đậu cove',    'label' => '200g',   'qty' => 1],
                    ['product' => 'Tỏi tươi',    'label' => '3 tép',  'qty' => 1],
                    ['product' => 'Ớt sừng đỏ',  'label' => '1 trái', 'qty' => 1],
                ],
            ],
            // 17 ── Cánh gà chiên nước mắm
            [
                'title'       => 'Cánh gà chiên nước mắm',
                'title_en'    => 'Fish Sauce Fried Chicken Wings',
                'description' => "Bước 1: Cánh gà rửa, khứa 2 đường, ướp tỏi, tiêu, nước mắm 30 phút.\nBước 2: Chiên ngập dầu lửa vừa 10-12 phút đến vàng giòn.\nBước 3: Pha sốt nước mắm: đường, tỏi ớt phi, nước mắm.\nBước 4: Áo cánh gà với sốt, xóc đều đến khi sốt sánh.",
                'description_en' => "Step 1: Score wings, marinate with garlic, pepper, fish sauce 30 mins.\nStep 2: Deep fry until golden crispy 10-12 mins.\nStep 3: Make fish sauce glaze: caramelize sugar with garlic chili and fish sauce.\nStep 4: Toss wings in glaze until well coated.",
                'thumbnail'   => 'https://images.unsplash.com/photo-1527477396000-e27163b481c2?w=600',
                'video_url'   => 'https://www.youtube.com/watch?v=BFYV0XNcOSE',
                'cook_time'   => 35,
                'category_id' => $chien,
                'ingredients' => [
                    ['product' => 'Cánh gà',    'label' => '500g',  'qty' => 1],
                    ['product' => 'Tỏi tươi',   'label' => '5 tép', 'qty' => 1],
                    ['product' => 'Ớt sừng đỏ', 'label' => '2 trái','qty' => 1],
                ],
            ],
            // 18 ── Khoai tây chiên bơ tỏi
            [
                'title'       => 'Khoai tây chiên bơ tỏi',
                'title_en'    => 'Garlic Butter French Fries',
                'description' => "Bước 1: Khoai tây gọt vỏ, cắt que, ngâm nước muối 15 phút.\nBước 2: Chần sơ khoai 3 phút, vớt ra thấm khô.\nBước 3: Chiên ngập dầu ở 180°C đến vàng giòn.\nBước 4: Phi bơ + tỏi đến thơm, trộn đều với khoai.\nBước 5: Rắc muối, tiêu, ngò rí.",
                'description_en' => "Step 1: Peel and cut potatoes into sticks, soak in salted water 15 mins.\nStep 2: Blanch 3 mins, drain and dry.\nStep 3: Deep fry at 180°C until golden crispy.\nStep 4: Melt butter with garlic, toss with fries.\nStep 5: Season with salt, pepper, cilantro.",
                'thumbnail'   => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=600',
                'video_url'   => 'https://www.youtube.com/watch?v=3wBkMOSOhI0',
                'cook_time'   => 30,
                'category_id' => $chien,
                'ingredients' => [
                    ['product' => 'Khoai tây',        'label' => '3 củ lớn (600g)', 'qty' => 3],
                    ['product' => 'Tỏi tươi',         'label' => '3 tép',           'qty' => 1],
                    ['product' => 'Ngò rí (Mùi tàu)', 'label' => '1 nắm nhỏ',       'qty' => 1],
                ],
            ],
            // 19 ── Đùi gà nướng mật ong
            [
                'title'       => 'Đùi gà nướng mật ong',
                'title_en'    => 'Honey Glazed Baked Chicken Thigh',
                'description' => "Bước 1: Khứa đùi gà, ướp tỏi, gừng, nước mắm, mật ong, tiêu 1 tiếng.\nBước 2: Xếp vào khay, nướng ở 200°C trong 25 phút.\nBước 3: Lật mặt, phết thêm hỗn hợp ướp, nướng tiếp 10 phút đến vàng sậm.\nBước 4: Để nghỉ 5 phút trước khi dùng.",
                'description_en' => "Step 1: Score chicken thighs, marinate with garlic, ginger, fish sauce, honey, pepper for 1 hour.\nStep 2: Place in baking tray, bake at 200°C for 25 mins.\nStep 3: Flip, brush with more marinade, bake 10 more mins until dark golden.\nStep 4: Rest 5 mins before serving.",
                'thumbnail'   => 'https://images.unsplash.com/photo-1432139509613-5c4255815697?w=600',
                'video_url'   => 'https://www.youtube.com/watch?v=klTfJRiNKaA',
                'cook_time'   => 45,
                'category_id' => $chien,
                'ingredients' => [
                    ['product' => 'Đùi gà tươi', 'label' => '4 đùi',  'qty' => 1],
                    ['product' => 'Tỏi tươi',    'label' => '4 tép',  'qty' => 1],
                    ['product' => 'Gừng tươi',   'label' => '30g',    'qty' => 1],
                    ['product' => 'Ớt sừng đỏ',  'label' => '1 trái', 'qty' => 1],
                ],
            ],
            // 20 ── Đậu bắp chiên giòn
            [
                'title'       => 'Đậu bắp chiên giòn',
                'title_en'    => 'Crispy Fried Okra',
                'description' => "Bước 1: Đậu bắp rửa sạch, để nguyên hoặc thái đôi.\nBước 2: Trộn bột mì, bột gạo, muối, tiêu, ớt bột.\nBước 3: Lăn đậu bắp qua trứng, sau đó qua hỗn hợp bột.\nBước 4: Chiên ngập dầu ở 180°C đến vàng giòn.\nBước 5: Vớt ra, rắc muối chanh.",
                'description_en' => "Step 1: Wash okra, keep whole or halve.\nStep 2: Mix flour, rice flour, salt, pepper, chili powder.\nStep 3: Dip okra in egg then coat with flour mix.\nStep 4: Deep fry at 180°C until golden crispy.\nStep 5: Drain, sprinkle with salt and lime.",
                'thumbnail'   => 'https://images.unsplash.com/photo-1476718406336-bb5a9690ee2a?w=600',
                'video_url'   => 'https://www.youtube.com/watch?v=n8laGm3IEOE',
                'cook_time'   => 20,
                'category_id' => $chien,
                'ingredients' => [
                    ['product' => 'Đậu bắp',    'label' => '300g',  'qty' => 1],
                    ['product' => 'Ớt sừng đỏ', 'label' => '1 trái','qty' => 1],
                ],
            ],
            // 21 ── Lẩu thái hải sản
            [
                'title'       => 'Lẩu thái hải sản',
                'title_en'    => 'Thai Seafood Hotpot',
                'description' => "Bước 1: Nấu nước dùng từ xương gà, sả, gừng, ớt.\nBước 2: Nêm nước mắm, đường, nước chanh tạo vị chua cay.\nBước 3: Chuẩn bị rau ăn lẩu: cải thảo, giá đỗ, rau thơm, cà chua.\nBước 4: Bày hải sản và rau ra đĩa.\nBước 5: Đun sôi nước lẩu, nhúng nguyên liệu khi ăn.",
                'description_en' => "Step 1: Make broth from chicken bones, lemongrass, ginger, chili.\nStep 2: Season with fish sauce, sugar, lime juice for sour-spicy taste.\nStep 3: Prepare vegetables: napa cabbage, bean sprouts, herbs, tomatoes.\nStep 4: Arrange seafood and veggies on plates.\nStep 5: Boil hotpot broth, dip ingredients while eating.",
                'thumbnail'   => 'https://images.unsplash.com/photo-1569050467447-ce54b3bbc37d?w=600',
                'video_url'   => 'https://www.youtube.com/watch?v=QnLKwNFgdoQ',
                'cook_time'   => 45,
                'category_id' => $lau,
                'ingredients' => [
                    ['product' => 'Cải thảo',        'label' => '½ cây',  'qty' => 1],
                    ['product' => 'Giá đỗ',          'label' => '300g',   'qty' => 1],
                    ['product' => 'Cà chua',          'label' => '3 quả',  'qty' => 3],
                    ['product' => 'Rau thơm hỗn hợp','label' => '200g',   'qty' => 1],
                    ['product' => 'Sả (Gừng tươi)',  'label' => '50g gừng','qty' => 1],
                    ['product' => 'Ớt sừng đỏ',      'label' => '3 trái', 'qty' => 3],
                ],
            ],
            // 22 ── Lẩu gà lá giang
            [
                'title'       => 'Lẩu gà lá giang',
                'title_en'    => 'Chicken Hotpot with Giang Leaves',
                'description' => "Bước 1: Gà chặt miếng, ướp gừng, sả, muối.\nBước 2: Nấu nước dùng gà trong 30 phút.\nBước 3: Nêm muối, nước mắm, thêm lá giang tạo vị chua.\nBước 4: Chuẩn bị rau: bắp cải, giá, rau thơm.\nBước 5: Ăn kèm bún tươi hoặc mì.",
                'description_en' => "Step 1: Chop chicken, marinate with ginger, lemongrass, salt.\nStep 2: Simmer chicken broth 30 mins.\nStep 3: Season, add giang leaves for sourness.\nStep 4: Prepare vegetables: cabbage, sprouts, herbs.\nStep 5: Serve with fresh rice noodles.",
                'thumbnail'   => 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?w=600',
                'video_url'   => 'https://www.youtube.com/watch?v=9eMAvGKDHHQ',
                'cook_time'   => 50,
                'category_id' => $lau,
                'ingredients' => [
                    ['product' => 'Gà nguyên con',   'label' => '1 con',  'qty' => 1],
                    ['product' => 'Bắp cải tươi',    'label' => '¼ cây',  'qty' => 1],
                    ['product' => 'Giá đỗ',          'label' => '200g',   'qty' => 1],
                    ['product' => 'Gừng tươi',       'label' => '30g',    'qty' => 1],
                    ['product' => 'Rau thơm hỗn hợp','label' => '200g',  'qty' => 1],
                ],
            ],
            // 23 ── Salad cà rốt dưa leo
            [
                'title'       => 'Salad cà rốt dưa leo',
                'title_en'    => 'Carrot Cucumber Salad',
                'description' => "Bước 1: Cà rốt và dưa leo rửa sạch, bào sợi hoặc thái mỏng.\nBước 2: Ngâm cà rốt trong nước muối nhạt 10 phút.\nBước 3: Pha nước trộn: giấm, đường, muối, tỏi ớt.\nBước 4: Vắt khô cà rốt, trộn đều với dưa leo và nước sốt.\nBước 5: Thêm rau thơm, rắc vừng rang.",
                'description_en' => "Step 1: Wash carrots and cucumber, julienne or slice thin.\nStep 2: Soak carrots in lightly salted water 10 mins.\nStep 3: Mix dressing: vinegar, sugar, salt, garlic chili.\nStep 4: Drain carrots, toss with cucumber and dressing.\nStep 5: Add fresh herbs and toasted sesame.",
                'thumbnail'   => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600',
                'video_url'   => 'https://www.youtube.com/watch?v=J9SFRiEb4mI',
                'cook_time'   => 15,
                'category_id' => $xao,
                'ingredients' => [
                    ['product' => 'Cà rốt',           'label' => '2 củ',   'qty' => 2],
                    ['product' => 'Dưa leo',           'label' => '2 trái', 'qty' => 2],
                    ['product' => 'Tỏi tươi',          'label' => '2 tép',  'qty' => 1],
                    ['product' => 'Ớt sừng đỏ',        'label' => '1 trái', 'qty' => 1],
                    ['product' => 'Ngò rí (Mùi tàu)',  'label' => '1 nắm',  'qty' => 1],
                ],
            ],
            // 24 ── Canh cần tây nấu sườn
            [
                'title'       => 'Canh cần tây nấu sườn',
                'title_en'    => 'Celery Pork Rib Soup',
                'description' => "Bước 1: Sườn chặt khúc, chần sơ bỏ bọt, rửa sạch.\nBước 2: Hầm sườn với nước 45 phút cho ngọt nước.\nBước 3: Cần tây rửa sạch, cắt khúc 3cm.\nBước 4: Cho cần tây vào nấu thêm 5 phút.\nBước 5: Nêm muối vừa ăn, rắc tiêu trắng.",
                'description_en' => "Step 1: Chop ribs, blanch to remove impurities.\nStep 2: Simmer ribs 45 mins for sweet broth.\nStep 3: Wash and cut celery into 3cm pieces.\nStep 4: Add celery, cook 5 more mins.\nStep 5: Season with salt, sprinkle white pepper.",
                'thumbnail'   => 'https://images.unsplash.com/photo-1547592180-85f173990554?w=600',
                'video_url'   => 'https://www.youtube.com/watch?v=VfN0RSSM4Qs',
                'cook_time'   => 55,
                'category_id' => $canh,
                'ingredients' => [
                    ['product' => 'Xương sườn heo non', 'label' => '500g', 'qty' => 1],
                    ['product' => 'Cần tây',            'label' => '300g', 'qty' => 1],
                    ['product' => 'Hành tây',           'label' => '½ củ', 'qty' => 1],
                    ['product' => 'Gừng tươi',          'label' => '2 lát','qty' => 1],
                ],
            ],
            // 25 ── Bí đỏ hầm xương
            [
                'title'       => 'Canh bí đỏ hầm xương',
                'title_en'    => 'Pumpkin Bone Broth Soup',
                'description' => "Bước 1: Xương heo rửa sạch, chần sơ.\nBước 2: Hầm xương 1 tiếng để lấy nước ngọt.\nBước 3: Bí đỏ gọt vỏ, cắt khối 3cm.\nBước 4: Cho bí vào nồi nước hầm, nấu 20 phút đến mềm.\nBước 5: Nêm muối, đường vừa ăn. Ăn kèm cơm.",
                'description_en' => "Step 1: Wash pork bones, blanch.\nStep 2: Simmer bones 1 hour for rich broth.\nStep 3: Peel and cut pumpkin into 3cm cubes.\nStep 4: Add pumpkin to broth, cook 20 mins until tender.\nStep 5: Season to taste. Serve with rice.",
                'thumbnail'   => 'https://images.unsplash.com/photo-1476718406336-bb5a9690ee2a?w=600',
                'video_url'   => 'https://www.youtube.com/watch?v=UNIqEPbS5Wg',
                'cook_time'   => 80,
                'category_id' => $canh,
                'ingredients' => [
                    ['product' => 'Bí đỏ',   'label' => '500g', 'qty' => 1],
                    ['product' => 'Hành tây','label' => '1 củ',  'qty' => 1],
                    ['product' => 'Gừng tươi','label' => '2 lát','qty' => 1],
                ],
            ],
            // 26 ── Broccoli xào tỏi
            [
                'title'       => 'Súp lơ xanh xào tỏi',
                'title_en'    => 'Garlic Stir-fried Broccoli',
                'description' => "Bước 1: Broccoli rửa sạch, tách bông nhỏ, chần sơ 2 phút.\nBước 2: Tỏi băm nhuyễn, phi thơm với dầu ăn.\nBước 3: Cho broccoli vào xào lửa to 3-4 phút.\nBước 4: Nêm nước mắm, muối, tiêu.\nBước 5: Thêm vài giọt dầu mè trước khi tắt bếp.",
                'description_en' => "Step 1: Wash broccoli, cut into small florets, blanch 2 mins.\nStep 2: Mince garlic, fry until golden aromatic.\nStep 3: Add broccoli, stir-fry on high heat 3-4 mins.\nStep 4: Season with fish sauce, salt, pepper.\nStep 5: Add sesame oil before turning off heat.",
                'thumbnail'   => 'https://images.unsplash.com/photo-1600335895229-6e75511892c8?w=600',
                'video_url'   => 'https://www.youtube.com/watch?v=OjFEHoKoJgg',
                'cook_time'   => 12,
                'category_id' => $xao,
                'ingredients' => [
                    ['product' => 'Súp lơ xanh (Broccoli)', 'label' => '400g',  'qty' => 1],
                    ['product' => 'Tỏi tươi',               'label' => '4 tép', 'qty' => 1],
                    ['product' => 'Ớt sừng đỏ',             'label' => '1 trái','qty' => 1],
                ],
            ],
            // 27 ── Chân giò hầm đậu phộng
            [
                'title'       => 'Chân giò hầm đậu phộng',
                'title_en'    => 'Braised Pork Trotter with Peanuts',
                'description' => "Bước 1: Chân giò cạo sạch lông, chặt khúc, chần sơ.\nBước 2: Đậu phộng ngâm nước 2 tiếng.\nBước 3: Cho chân giò, đậu phộng, hành, gừng vào nồi áp suất hầm 40 phút.\nBước 4: Nêm muối, nước mắm vừa ăn.\nBước 5: Múc ra tô, thêm hành ngò thái nhỏ.",
                'description_en' => "Step 1: Clean pork trotter, chop into pieces, blanch.\nStep 2: Soak peanuts in water 2 hours.\nStep 3: Pressure cook trotter, peanuts, shallots, ginger 40 mins.\nStep 4: Season with salt and fish sauce.\nStep 5: Serve in bowl, garnish with green onions.",
                'thumbnail'   => 'https://images.unsplash.com/photo-1518492104633-130d0cc84637?w=600',
                'video_url'   => 'https://www.youtube.com/watch?v=9c7mLTjMmCE',
                'cook_time'   => 70,
                'category_id' => $kho,
                'ingredients' => [
                    ['product' => 'Chân giò heo', 'label' => '500g', 'qty' => 1],
                    ['product' => 'Hành tây',     'label' => '1 củ',  'qty' => 1],
                    ['product' => 'Gừng tươi',    'label' => '30g',   'qty' => 1],
                ],
            ],
            // 28 ── Ngô nướng phô mai
            [
                'title'       => 'Ngô nướng phô mai bơ',
                'title_en'    => 'Grilled Corn with Cheese Butter',
                'description' => "Bước 1: Ngô nướng trực tiếp trên bếp than hoặc chảo gang đến khi có vệt cháy.\nBước 2: Phết hỗn hợp bơ + tỏi + muối lên ngô.\nBước 3: Rắc phô mai bào lên trên.\nBước 4: Nướng thêm 5 phút cho phô mai tan chảy.\nBước 5: Vắt chanh, rắc ớt bột.",
                'description_en' => "Step 1: Grill corn on charcoal or cast iron pan until charred.\nStep 2: Brush with butter garlic salt mixture.\nStep 3: Sprinkle shredded cheese on top.\nStep 4: Grill 5 more mins until cheese melts.\nStep 5: Squeeze lime, sprinkle chili powder.",
                'thumbnail'   => 'https://images.unsplash.com/photo-1551754655-cd27e38d2076?w=600',
                'video_url'   => 'https://www.youtube.com/watch?v=gfNTEYAVjdI',
                'cook_time'   => 20,
                'category_id' => $chien,
                'ingredients' => [
                    ['product' => 'Ngô (Bắp) ngọt', 'label' => '3 trái',  'qty' => 3],
                    ['product' => 'Tỏi tươi',        'label' => '2 tép',   'qty' => 1],
                    ['product' => 'Ớt sừng đỏ',      'label' => '½ trái',  'qty' => 1],
                ],
            ],
            // 29 ── Bắp cải cuộn thịt hấp
            [
                'title'       => 'Bắp cải cuộn thịt hấp',
                'title_en'    => 'Steamed Cabbage Pork Rolls',
                'description' => "Bước 1: Lá bắp cải chần mềm 1 phút.\nBước 2: Thịt heo xay trộn với hành, tỏi, mộc nhĩ, miến, nêm gia vị.\nBước 3: Đặt nhân vào lá bắp cải, cuộn chặt.\nBước 4: Xếp vào khay, hấp 20-25 phút.\nBước 5: Pha nước chấm tỏi ớt nước mắm.",
                'description_en' => "Step 1: Blanch cabbage leaves until soft, 1 min.\nStep 2: Mix ground pork with shallots, garlic, mushroom, glass noodles, season.\nStep 3: Place filling on leaf, roll tightly.\nStep 4: Steam 20-25 mins.\nStep 5: Mix garlic chili fish sauce dip.",
                'thumbnail'   => 'https://images.unsplash.com/photo-1563245372-f21724e3856d?w=600',
                'video_url'   => 'https://www.youtube.com/watch?v=UNIqEPbS5Wg',
                'cook_time'   => 35,
                'category_id' => $luoc,
                'ingredients' => [
                    ['product' => 'Bắp cải tươi',    'label' => '1 cây nhỏ', 'qty' => 1],
                    ['product' => 'Thịt heo xay sẵn','label' => '300g',      'qty' => 1],
                    ['product' => 'Hành tây',        'label' => '½ củ',       'qty' => 1],
                    ['product' => 'Tỏi tươi',        'label' => '3 tép',      'qty' => 1],
                ],
            ],
            // 30 ── Atiso hầm sườn
            [
                'title'       => 'Canh atiso hầm sườn',
                'title_en'    => 'Artichoke Pork Rib Soup',
                'description' => "Bước 1: Sườn chặt khúc, chần sơ rửa sạch.\nBước 2: Atiso bỏ lá ngoài, cắt đôi, ngâm nước muối chanh.\nBước 3: Cho sườn và atiso vào nồi, thêm 2 lít nước.\nBước 4: Hầm 45 phút đến khi sườn và atiso mềm.\nBước 5: Nêm muối, tiêu. Thêm ngò rí trước khi ăn.",
                'description_en' => "Step 1: Chop ribs, blanch and rinse.\nStep 2: Trim artichokes, halve, soak in salted lime water.\nStep 3: Place ribs and artichokes in pot with 2L water.\nStep 4: Simmer 45 mins until tender.\nStep 5: Season with salt, pepper. Add cilantro before serving.",
                'thumbnail'   => 'https://images.unsplash.com/photo-1547592180-85f173990554?w=600',
                'video_url'   => 'https://www.youtube.com/watch?v=tEPlxbbGnU4',
                'cook_time'   => 60,
                'category_id' => $canh,
                'ingredients' => [
                    ['product' => 'Bông atiso',         'label' => '2 bông',  'qty' => 2],
                    ['product' => 'Xương sườn heo non', 'label' => '300g',    'qty' => 1],
                    ['product' => 'Ngò rí (Mùi tàu)',   'label' => '1 nắm',   'qty' => 1],
                    ['product' => 'Hành tây',           'label' => '½ củ',    'qty' => 1],
                ],
            ],
        ];
    }
}
