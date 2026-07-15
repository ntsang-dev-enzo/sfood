<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductVariant;
use App\Models\Recipe;
use App\Models\RecipeCategory;
use App\Models\RecipeIngredient;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SFoodDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Seeding SFood data...');

        // =============================================
        // 1. CATEGORIES (Danh mục sản phẩm)
        // =============================================
        $this->command->info('📁 Seeding categories...');

        $categoriesData = [
            ['name' => 'Rau củ', 'name_en' => 'Vegetables', 'icon' => '🥬', 'description' => 'Rau củ quả tươi sạch mỗi ngày', 'sort_order' => 1],
            ['name' => 'Trái cây', 'name_en' => 'Fruits', 'icon' => '🍎', 'description' => 'Trái cây tươi ngon, nhập khẩu và nội địa', 'sort_order' => 2],
            ['name' => 'Thịt cá', 'name_en' => 'Meat & Seafood', 'icon' => '🥩', 'description' => 'Thịt tươi, hải sản cao cấp', 'sort_order' => 3],
            ['name' => 'Đồ uống', 'name_en' => 'Beverages', 'icon' => '🥛', 'description' => 'Sữa, nước ngọt, nước ép', 'sort_order' => 4],
            ['name' => 'Gia vị', 'name_en' => 'Spices & Sauces', 'icon' => '🧂', 'description' => 'Gia vị, nước chấm, dầu ăn', 'sort_order' => 5],
            ['name' => 'Đồ khô', 'name_en' => 'Dry Goods', 'icon' => '🍜', 'description' => 'Mì, bún, phở, gạo, bột', 'sort_order' => 6],
            ['name' => 'Đồ đông lạnh', 'name_en' => 'Frozen Foods', 'icon' => '🧊', 'description' => 'Thực phẩm đông lạnh tiện lợi', 'sort_order' => 7],
            ['name' => 'Đồ hộp', 'name_en' => 'Canned Goods', 'icon' => '🥫', 'description' => 'Đồ hộp, thực phẩm chế biến sẵn', 'sort_order' => 8],
            ['name' => 'Bánh kẹo', 'name_en' => 'Snacks & Sweets', 'icon' => '🍪', 'description' => 'Bánh, kẹo, snack các loại', 'sort_order' => 9],
            ['name' => 'Chăm sóc nhà cửa', 'name_en' => 'Household', 'icon' => '🧹', 'description' => 'Đồ dùng gia đình, vệ sinh', 'sort_order' => 10],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[] = Category::updateOrCreate(
                ['slug' => Str::slug($cat['name'])],
                array_merge($cat, ['slug' => Str::slug($cat['name']), 'is_active' => true])
            );
        }

        // =============================================
        // 2. PRODUCTS (100 sản phẩm)
        // =============================================
        $this->command->info('🛒 Seeding 100 products...');

        $productsByCategory = [
            // Rau củ (15)
            'Rau củ' => [
                ['name' => 'Cà chua bi', 'price' => 25000, 'desc' => 'Cà chua bi cherry tươi ngon, giàu vitamin C'],
                ['name' => 'Khoai tây', 'price' => 18000, 'desc' => 'Khoai tây Đà Lạt sạch, vỏ mỏng'],
                ['name' => 'Cà rốt', 'price' => 15000, 'desc' => 'Cà rốt Đà Lạt, ngọt tự nhiên'],
                ['name' => 'Bắp cải', 'price' => 12000, 'desc' => 'Bắp cải xanh tươi, giòn ngọt'],
                ['name' => 'Rau muống', 'price' => 8000, 'desc' => 'Rau muống nước sạch, an toàn'],
                ['name' => 'Bông cải xanh', 'price' => 35000, 'desc' => 'Broccoli nhập khẩu, giàu chất xơ'],
                ['name' => 'Đậu bắp', 'price' => 20000, 'desc' => 'Đậu bắp non, ngọt mát'],
                ['name' => 'Bí đỏ', 'price' => 22000, 'desc' => 'Bí đỏ Nhật Bản, thịt dày ngọt bùi'],
                ['name' => 'Mướp đắng', 'price' => 16000, 'desc' => 'Khổ qua tươi, thanh nhiệt giải độc'],
                ['name' => 'Hành tây', 'price' => 14000, 'desc' => 'Hành tây tím, thơm nồng'],
                ['name' => 'Tỏi Lý Sơn', 'price' => 120000, 'desc' => 'Tỏi Lý Sơn chính gốc, thơm cay đặc trưng'],
                ['name' => 'Ớt hiểm', 'price' => 30000, 'desc' => 'Ớt hiểm xanh/đỏ, vị cay vừa'],
                ['name' => 'Rau cải bó xôi', 'price' => 28000, 'desc' => 'Rau bó xôi baby, giàu sắt'],
                ['name' => 'Nấm kim châm', 'price' => 18000, 'desc' => 'Nấm kim châm Hàn Quốc gói 200g'],
                ['name' => 'Ngô ngọt', 'price' => 10000, 'desc' => 'Bắp ngọt tươi, hạt mẩy'],
            ],
            // Trái cây (12)
            'Trái cây' => [
                ['name' => 'Táo Fuji Nhật', 'price' => 85000, 'desc' => 'Táo Fuji nhập Nhật, giòn ngọt'],
                ['name' => 'Chuối Laba', 'price' => 35000, 'desc' => 'Chuối Laba Đà Lạt, ngọt thơm'],
                ['name' => 'Cam sành', 'price' => 45000, 'desc' => 'Cam sành Vĩnh Long, nhiều nước'],
                ['name' => 'Xoài cát Hòa Lộc', 'price' => 65000, 'desc' => 'Xoài cát Hòa Lộc chín tự nhiên'],
                ['name' => 'Bưởi da xanh', 'price' => 55000, 'desc' => 'Bưởi da xanh Bến Tre, múi đều'],
                ['name' => 'Dưa hấu không hạt', 'price' => 40000, 'desc' => 'Dưa hấu không hạt, ruột đỏ ngọt'],
                ['name' => 'Nho xanh Mỹ', 'price' => 120000, 'desc' => 'Nho xanh Thompson nhập Mỹ'],
                ['name' => 'Dâu tây Đà Lạt', 'price' => 90000, 'desc' => 'Dâu tây Đà Lạt tươi, vị chua ngọt'],
                ['name' => 'Thanh long ruột đỏ', 'price' => 38000, 'desc' => 'Thanh long ruột đỏ Bình Thuận'],
                ['name' => 'Kiwi vàng New Zealand', 'price' => 150000, 'desc' => 'Kiwi vàng Zespri, ngọt thanh'],
                ['name' => 'Ổi Đài Loan', 'price' => 32000, 'desc' => 'Ổi Đài Loan ruột hồng, giòn ngọt'],
                ['name' => 'Sầu riêng Ri6', 'price' => 180000, 'desc' => 'Sầu riêng Ri6 loại 1, cơm vàng dày'],
            ],
            // Thịt cá (15)
            'Thịt cá' => [
                ['name' => 'Cá hồi Na Uy', 'price' => 280000, 'desc' => 'Cá hồi Na Uy fillet tươi, giàu Omega-3'],
                ['name' => 'Thịt ba chỉ heo', 'price' => 95000, 'desc' => 'Ba chỉ heo sạch, vân đẹp'],
                ['name' => 'Ức gà phi lê', 'price' => 75000, 'desc' => 'Ức gà phi lê tươi, ít mỡ'],
                ['name' => 'Tôm sú size lớn', 'price' => 220000, 'desc' => 'Tôm sú tươi sống 20 con/kg'],
                ['name' => 'Sườn non heo', 'price' => 125000, 'desc' => 'Sườn non heo có xương, thịt mềm'],
                ['name' => 'Cá thu một nắng', 'price' => 150000, 'desc' => 'Cá thu một nắng Phú Yên'],
                ['name' => 'Thịt bò Úc thăn ngoại', 'price' => 350000, 'desc' => 'Bò Úc thăn ngoại (Striploin) đông lạnh'],
                ['name' => 'Mực ống tươi', 'price' => 180000, 'desc' => 'Mực ống tươi sống, ngọt thịt'],
                ['name' => 'Đùi gà góc tư', 'price' => 55000, 'desc' => 'Đùi gà ta góc tư, thịt chắc'],
                ['name' => 'Cá basa phi lê', 'price' => 65000, 'desc' => 'Cá basa phi lê đông lạnh'],
                ['name' => 'Nghêu trắng', 'price' => 45000, 'desc' => 'Nghêu trắng Bến Tre tươi sống'],
                ['name' => 'Thịt heo xay', 'price' => 85000, 'desc' => 'Thịt heo xay tươi, nạc 80%'],
                ['name' => 'Cá diêu hồng', 'price' => 70000, 'desc' => 'Cá diêu hồng nguyên con, tươi sống'],
                ['name' => 'Tôm thẻ chân trắng', 'price' => 160000, 'desc' => 'Tôm thẻ chân trắng loại 1'],
                ['name' => 'Bò viên thật 100%', 'price' => 95000, 'desc' => 'Bò viên xay tay, 100% thịt bò'],
            ],
            // Đồ uống (10)
            'Đồ uống' => [
                ['name' => 'Sữa tươi TH True Milk', 'price' => 32000, 'desc' => 'Sữa tươi tiệt trùng không đường 1L'],
                ['name' => 'Nước ép cam Tropicana', 'price' => 45000, 'desc' => 'Nước cam ép nguyên chất 1L'],
                ['name' => 'Trà xanh Không Độ', 'price' => 10000, 'desc' => 'Trà xanh đóng chai 500ml'],
                ['name' => 'Sữa đặc Ông Thọ', 'price' => 22000, 'desc' => 'Sữa đặc có đường lon 380g'],
                ['name' => 'Nước dừa tươi Cocoxim', 'price' => 18000, 'desc' => 'Nước dừa xiêm đóng hộp 330ml'],
                ['name' => 'Cà phê G7 3in1', 'price' => 85000, 'desc' => 'Cà phê hòa tan G7 hộp 20 gói'],
                ['name' => 'Sữa chua Vinamilk', 'price' => 28000, 'desc' => 'Sữa chua có đường lốc 4 hộp'],
                ['name' => 'Bia Tiger Crystal', 'price' => 320000, 'desc' => 'Bia Tiger Crystal thùng 24 lon'],
                ['name' => 'Nước suối Lavie', 'price' => 65000, 'desc' => 'Nước khoáng Lavie thùng 24 chai 500ml'],
                ['name' => 'Trà sữa Kirin', 'price' => 15000, 'desc' => 'Trà sữa Kirin Nhật Bản 345ml'],
            ],
            // Gia vị (12)
            'Gia vị' => [
                ['name' => 'Nước mắm Phú Quốc', 'price' => 55000, 'desc' => 'Nước mắm Phú Quốc 40 độ đạm chai 500ml'],
                ['name' => 'Dầu ăn Neptune', 'price' => 48000, 'desc' => 'Dầu ăn Neptune Gold 1L'],
                ['name' => 'Nước tương Maggi', 'price' => 15000, 'desc' => 'Nước tương đậu nành Maggi 300ml'],
                ['name' => 'Bột ngọt Ajinomoto', 'price' => 22000, 'desc' => 'Bột ngọt Ajinomoto gói 400g'],
                ['name' => 'Muối hột Bà Rịa', 'price' => 8000, 'desc' => 'Muối hạt thô tự nhiên 500g'],
                ['name' => 'Đường phèn', 'price' => 35000, 'desc' => 'Đường phèn nguyên chất gói 500g'],
                ['name' => 'Giấm táo Hàn Quốc', 'price' => 65000, 'desc' => 'Giấm táo lên men tự nhiên 500ml'],
                ['name' => 'Tương ớt Chinsu', 'price' => 18000, 'desc' => 'Tương ớt Chinsu chai 250g'],
                ['name' => 'Hạt nêm Knorr', 'price' => 32000, 'desc' => 'Hạt nêm Knorr từ thịt thăn gói 400g'],
                ['name' => 'Bơ mặn Anchor', 'price' => 75000, 'desc' => 'Bơ lạt Anchor New Zealand 227g'],
                ['name' => 'Tiêu đen xay', 'price' => 28000, 'desc' => 'Tiêu đen Phú Quốc xay sẵn 50g'],
                ['name' => 'Sa tế Bà Giáo', 'price' => 25000, 'desc' => 'Sa tế tôm cay Bà Giáo hũ 100g'],
            ],
            // Đồ khô (10)
            'Đồ khô' => [
                ['name' => 'Gạo ST25', 'price' => 28000, 'desc' => 'Gạo ST25 Sóc Trăng gói 2kg, thơm dẻo'],
                ['name' => 'Mì Hảo Hảo', 'price' => 65000, 'desc' => 'Mì Hảo Hảo tôm chua cay thùng 30 gói'],
                ['name' => 'Bún gạo Bích Chi', 'price' => 18000, 'desc' => 'Bún gạo khô 500g'],
                ['name' => 'Phở khô Vifon', 'price' => 22000, 'desc' => 'Phở bò Vifon gói 65g x5'],
                ['name' => 'Miến dong', 'price' => 25000, 'desc' => 'Miến dong Bắc Kạn 500g'],
                ['name' => 'Bột mì đa dụng', 'price' => 20000, 'desc' => 'Bột mì số 13 gói 1kg'],
                ['name' => 'Yến mạch Quaker', 'price' => 85000, 'desc' => 'Yến mạch nguyên hạt Quaker 600g'],
                ['name' => 'Đậu xanh nguyên vỏ', 'price' => 32000, 'desc' => 'Đậu xanh nguyên vỏ gói 500g'],
                ['name' => 'Nui xoắn Barilla', 'price' => 55000, 'desc' => 'Nui ống Fusilli Barilla 500g'],
                ['name' => 'Cháo yến mạch Cốt Dừa', 'price' => 45000, 'desc' => 'Cháo yến mạch hòa tan vị cốt dừa hộp 6 gói'],
            ],
            // Đồ đông lạnh (8)
            'Đồ đông lạnh' => [
                ['name' => 'Há cảo tôm thịt', 'price' => 65000, 'desc' => 'Há cảo nhân tôm thịt gói 500g'],
                ['name' => 'Pizza đông lạnh', 'price' => 89000, 'desc' => 'Pizza phô mai hải sản đông lạnh'],
                ['name' => 'Xúc xích Đức Việt', 'price' => 42000, 'desc' => 'Xúc xích tiệt trùng gói 500g'],
                ['name' => 'Chả giò rế', 'price' => 55000, 'desc' => 'Chả giò rế tôm cua đông lạnh 500g'],
                ['name' => 'Kem Häagen-Dazs', 'price' => 180000, 'desc' => 'Kem Häagen-Dazs Vanilla 473ml'],
                ['name' => 'Đậu hũ non', 'price' => 12000, 'desc' => 'Đậu hũ non Ichiban hộp 300g'],
                ['name' => 'Gyoza Nhật Bản', 'price' => 75000, 'desc' => 'Gyoza nhân thịt heo gói 20 viên'],
                ['name' => 'Khoai tây chiên đông lạnh', 'price' => 48000, 'desc' => 'Khoai tây McCain cắt sợi 750g'],
            ],
            // Đồ hộp (6)
            'Đồ hộp' => [
                ['name' => 'Cá ngừ đóng hộp', 'price' => 28000, 'desc' => 'Cá ngừ ngâm dầu hộp 185g'],
                ['name' => 'Đậu đỏ lon', 'price' => 22000, 'desc' => 'Đậu đỏ đóng lon 400g'],
                ['name' => 'Sốt cà chua Heinz', 'price' => 45000, 'desc' => 'Sốt cà chua Heinz chai 342g'],
                ['name' => 'Nấm đóng lon', 'price' => 25000, 'desc' => 'Nấm mỡ nguyên cây đóng hộp 400g'],
                ['name' => 'Pate Hạ Long', 'price' => 18000, 'desc' => 'Pate gan heo Hạ Long 150g'],
                ['name' => 'Sữa bắp Vinamilk', 'price' => 35000, 'desc' => 'Sữa bắp Vinamilk thùng 6 lon'],
            ],
            // Bánh kẹo (8)
            'Bánh kẹo' => [
                ['name' => 'Bánh Oreo', 'price' => 32000, 'desc' => 'Bánh quy socola Oreo 133g'],
                ['name' => 'Kẹo dẻo Haribo', 'price' => 45000, 'desc' => 'Kẹo dẻo gấu Haribo Goldbears 80g'],
                ['name' => 'Snack Lay\'s Classic', 'price' => 22000, 'desc' => 'Snack khoai tây Lay\'s vị truyền thống 95g'],
                ['name' => 'Socola KitKat', 'price' => 28000, 'desc' => 'KitKat 2 thanh vị trà xanh'],
                ['name' => 'Bánh tráng trộn', 'price' => 15000, 'desc' => 'Bánh tráng trộn sẵn gia vị gói lớn'],
                ['name' => 'Hạt điều rang muối', 'price' => 95000, 'desc' => 'Hạt điều rang muối Bình Phước 250g'],
                ['name' => 'Bánh gạo Nhật', 'price' => 38000, 'desc' => 'Bánh gạo One One Nhật gói 150g'],
                ['name' => 'Rong biển ăn liền', 'price' => 25000, 'desc' => 'Rong biển tẩm gia vị Tao Kae Noi 36g'],
            ],
            // Chăm sóc nhà cửa (4)
            'Chăm sóc nhà cửa' => [
                ['name' => 'Nước rửa chén Sunlight', 'price' => 35000, 'desc' => 'Nước rửa chén Sunlight chanh 750ml'],
                ['name' => 'Nước lau sàn Mỹ Hảo', 'price' => 28000, 'desc' => 'Nước lau sàn Mỹ Hảo hương lavender 1L'],
                ['name' => 'Giấy vệ sinh Pulppy', 'price' => 65000, 'desc' => 'Giấy vệ sinh Pulppy 12 cuộn'],
                ['name' => 'Túi đựng rác', 'price' => 18000, 'desc' => 'Túi đựng rác tự phân hủy 3 cuộn'],
            ],
        ];

        $variantSizes = [
            'Rau củ' => [['500g', 1.0], ['1kg', 1.8]],
            'Trái cây' => [['500g', 1.0], ['1kg', 1.85], ['2kg', 3.5]],
            'Thịt cá' => [['300g', 1.0], ['500g', 1.55], ['1kg', 2.9]],
            'Đồ uống' => [['1 hộp/chai', 1.0], ['Lốc 4', 3.6], ['Thùng', 8.5]],
            'Gia vị' => [['Nhỏ', 1.0], ['Lớn', 1.8]],
            'Đồ khô' => [['Gói nhỏ', 1.0], ['Gói lớn', 2.2]],
            'Đồ đông lạnh' => [['1 gói', 1.0], ['2 gói', 1.85]],
            'Đồ hộp' => [['1 lon/hộp', 1.0], ['3 lon/hộp', 2.7]],
            'Bánh kẹo' => [['Gói nhỏ', 1.0], ['Gói lớn', 2.0]],
            'Chăm sóc nhà cửa' => [['1 chai/cuộn', 1.0], ['3 chai/cuộn', 2.6]],
        ];

        $categoryMap = [];
        foreach ($categories as $cat) {
            $categoryMap[$cat->name] = $cat->id;
        }

        // Reset name attribute accessor issue
        foreach (Category::all() as $c) {
            $categoryMap[$c->getAttributes()['name']] = $c->id;
        }

        $allProductIds = [];

        foreach ($productsByCategory as $catName => $products) {
            $catId = $categoryMap[$catName] ?? null;
            if (!$catId)
                continue;

            $sizes = $variantSizes[$catName] ?? [['Mặc định', 1.0]];

            foreach ($products as $pData) {
                $slug = Str::slug($pData['name']);
                $existingProduct = Product::where('slug', $slug)->first();
                if ($existingProduct) {
                    $allProductIds[] = $existingProduct->id;
                    continue;
                }

                $isHot = rand(0, 10) > 7;
                $isNew = rand(0, 10) > 6;

                $product = Product::create([
                    'category_id' => $catId,
                    'name' => $pData['name'],
                    'slug' => $slug,
                    'short_description' => $pData['desc'],
                    'description' => $pData['desc'] . '. Sản phẩm được chọn lọc kỹ càng, đảm bảo chất lượng tươi ngon nhất đến tay người tiêu dùng. Giao hàng nhanh trong 2h nội thành.',
                    'is_active' => true,
                    'is_hot' => $isHot,
                    'is_new' => $isNew,
                    'view_count' => rand(10, 500),
                ]);

                $allProductIds[] = $product->id;

                // Create variants
                $basePrice = $pData['price'];
                $hasSale = rand(0, 10) > 6;

                foreach ($sizes as $i => [$sizeName, $multiplier]) {
                    $price = round($basePrice * $multiplier, -2);
                    $salePrice = $hasSale ? round($price * (rand(75, 92) / 100), -2) : null;

                    ProductVariant::create([
                        'product_id' => $product->id,
                        'name' => $sizeName,
                        'sku' => strtoupper(Str::slug($pData['name'], '')) . '-' . ($i + 1),
                        'price' => $price,
                        'sale_price' => $salePrice,
                        'stock' => rand(20, 200),
                        'is_active' => true,
                    ]);
                }

                // Update min_price cache
                $minPrice = $product->variants()->min(\Illuminate\Support\Facades\DB::raw('COALESCE(sale_price, price)'));
                $product->update(['min_price' => $minPrice]);
            }
        }

        $this->command->info("✅ Created " . count($allProductIds) . " products with variants.");

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

                ProductReview::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'product_id' => $caChuaBi->id,
                    ],
                    [
                        'rating' => $rev['rating'],
                        'comment' => $rev['comment'],
                        'created_at' => now()->subDays($rev['days_ago']),
                        'updated_at' => now()->subDays($rev['days_ago']),
                    ]
                );
            }
            $this->command->info("⭐ Created reviews for Cà chua bi.");
        }

        // =============================================
        // 3. RECIPE CATEGORIES
        // =============================================
        $this->command->info('📂 Seeding recipe categories...');

        $recipeCategoriesData = [
            'Món canh' => 'mon-canh',
            'Món xào' => 'mon-xao',
            'Món chiên' => 'mon-chien',
            'Món hấp/luộc' => 'mon-hap-luoc',
            'Món kho/rim' => 'mon-kho-rim',
            'Món salad' => 'mon-salad',
            'Món tráng miệng' => 'mon-trang-mieng',
            'Món nướng' => 'mon-nuong',
        ];

        $recipeCategories = [];
        foreach ($recipeCategoriesData as $name => $slug) {
            $recipeCategories[$name] = RecipeCategory::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'slug' => $slug]
            );
        }

        // =============================================
        // 4. RECIPES (30 công thức)
        // =============================================
        $this->command->info('👨‍🍳 Seeding 30 recipes...');

        $recipesData = [
            ['title' => 'Canh chua cá hồi', 'cat' => 'Món canh', 'time' => 35, 'desc' => 'Canh chua cá hồi thơm ngon với cà chua, thơm, đậu bắp. Vị chua nhẹ thanh mát, bổ dưỡng cho cả gia đình.'],
            ['title' => 'Canh bí đỏ nấu tôm', 'cat' => 'Món canh', 'time' => 25, 'desc' => 'Canh bí đỏ ngọt tự nhiên nấu cùng tôm tươi, bổ mắt giàu vitamin A.'],
            ['title' => 'Canh rau muống luộc', 'cat' => 'Món canh', 'time' => 15, 'desc' => 'Canh rau muống luộc đơn giản, nhanh gọn cho bữa cơm hằng ngày.'],
            ['title' => 'Canh khổ qua nhồi thịt', 'cat' => 'Món canh', 'time' => 45, 'desc' => 'Khổ qua nhồi thịt heo xay, nấu nước dùng trong veo, thanh nhiệt giải độc.'],
            ['title' => 'Thịt ba chỉ rim cà chua', 'cat' => 'Món kho/rim', 'time' => 40, 'desc' => 'Thịt ba chỉ rim mặn ngọt cùng cà chua bi, tương ớt. Đơn giản mà rất đưa cơm.'],
            ['title' => 'Cá kho tộ', 'cat' => 'Món kho/rim', 'time' => 50, 'desc' => 'Cá diêu hồng kho tộ đất kiểu miền Nam, đậm đà mặn ngọt với nước màu caramen.'],
            ['title' => 'Sườn non kho tiêu', 'cat' => 'Món kho/rim', 'time' => 55, 'desc' => 'Sườn non heo kho tiêu đen Phú Quốc, thịt mềm thấm gia vị, ăn kèm cơm nóng.'],
            ['title' => 'Gà xào sả ớt', 'cat' => 'Món xào', 'time' => 30, 'desc' => 'Đùi gà phi lê xào sả ớt cay thơm, ăn kèm cơm trắng cực ngon.'],
            ['title' => 'Bông cải xào tỏi', 'cat' => 'Món xào', 'time' => 15, 'desc' => 'Broccoli xào tỏi đơn giản, giữ nguyên độ giòn và dinh dưỡng.'],
            ['title' => 'Mực xào dưa chua', 'cat' => 'Món xào', 'time' => 20, 'desc' => 'Mực ống tươi xào cùng dưa cải chua, vị mặn ngọt hài hòa.'],
            ['title' => 'Bò xào hành tây', 'cat' => 'Món xào', 'time' => 20, 'desc' => 'Thịt bò thăn xào nhanh với hành tây, giòn ngọt đậm vị.'],
            ['title' => 'Trứng chiên sữa', 'cat' => 'Món chiên', 'time' => 10, 'desc' => 'Trứng chiên bông lan mềm xốp nhờ thêm sữa tươi, đơn giản cho bữa sáng.'],
            ['title' => 'Cá phi lê chiên giòn', 'cat' => 'Món chiên', 'time' => 25, 'desc' => 'Cá basa phi lê tẩm bột chiên vàng giòn, chấm nước mắm pha.'],
            ['title' => 'Nem rán Hà Nội', 'cat' => 'Món chiên', 'time' => 60, 'desc' => 'Nem rán truyền thống với nhân thịt heo, mộc nhĩ, miến, cà rốt.'],
            ['title' => 'Đậu hũ chiên sốt cà', 'cat' => 'Món chiên', 'time' => 25, 'desc' => 'Đậu hũ chiên vàng sốt cà chua thịt băm, thanh đạm mà ngon.'],
            ['title' => 'Cá hồi hấp xì dầu', 'cat' => 'Món hấp/luộc', 'time' => 20, 'desc' => 'Cá hồi hấp nước tương Nhật, giữ nguyên vị ngọt béo tự nhiên của cá.'],
            ['title' => 'Tôm hấp bia', 'cat' => 'Món hấp/luộc', 'time' => 15, 'desc' => 'Tôm sú tươi hấp bia, thơm lừng, thịt tôm ngọt dai. Chấm muối tiêu chanh.'],
            ['title' => 'Gà luộc lá chanh', 'cat' => 'Món hấp/luộc', 'time' => 40, 'desc' => 'Gà ta luộc lá chanh da vàng giòn, thịt ngọt mềm. Chấm muối ớt xanh.'],
            ['title' => 'Salad Caesar gà nướng', 'cat' => 'Món salad', 'time' => 25, 'desc' => 'Salad Caesar truyền thống với ức gà nướng thái lát, phô mai Parmesan, crouton.'],
            ['title' => 'Gỏi ngó sen tôm thịt', 'cat' => 'Món salad', 'time' => 30, 'desc' => 'Gỏi ngó sen giòn giòn trộn tôm luộc, thịt heo, rau thơm, nước mắm chua ngọt.'],
            ['title' => 'Salad trái cây sữa chua', 'cat' => 'Món salad', 'time' => 10, 'desc' => 'Salad trái cây tươi trộn sữa chua Hy Lạp, bổ dưỡng giải nhiệt.'],
            ['title' => 'Chè bí đỏ đậu xanh', 'cat' => 'Món tráng miệng', 'time' => 35, 'desc' => 'Chè bí đỏ đậu xanh nấu nước cốt dừa, ngọt thanh bổ dưỡng.'],
            ['title' => 'Rau câu dừa', 'cat' => 'Món tráng miệng', 'time' => 30, 'desc' => 'Rau câu nước cốt dừa thơm béo, mát lạnh cho ngày hè.'],
            ['title' => 'Bánh flan caramel', 'cat' => 'Món tráng miệng', 'time' => 45, 'desc' => 'Bánh flan trứng caramen mềm mịn, tan ngay trong miệng.'],
            ['title' => 'Sườn nướng BBQ', 'cat' => 'Món nướng', 'time' => 60, 'desc' => 'Sườn non heo ướp sốt BBQ nướng than hoa, thịt mềm mọng nước.'],
            ['title' => 'Cá hồi nướng bơ tỏi', 'cat' => 'Món nướng', 'time' => 25, 'desc' => 'Cá hồi fillet nướng bơ tỏi trong lò, da giòn thịt mềm béo ngậy.'],
            ['title' => 'Gà nướng ngũ vị hương', 'cat' => 'Món nướng', 'time' => 70, 'desc' => 'Gà nguyên con ướp ngũ vị hương nướng giòn vàng, thơm lừng.'],
            ['title' => 'Canh ngao mồng tơi', 'cat' => 'Món canh', 'time' => 20, 'desc' => 'Canh ngao nấu mồng tơi, nước dùng ngọt thanh tự nhiên từ ngao.'],
            ['title' => 'Thịt heo xào nấm', 'cat' => 'Món xào', 'time' => 20, 'desc' => 'Thịt heo thái mỏng xào nấm kim châm và nấm đùi gà, gia vị hạt nêm.'],
            ['title' => 'Khoai tây nướng phô mai', 'cat' => 'Món nướng', 'time' => 35, 'desc' => 'Khoai tây xắt lát nướng phô mai mozzarella, béo ngậy thơm lừng.'],
        ];

        foreach ($recipesData as $rData) {
            $slug = Str::slug($rData['title']);
            if (Recipe::where('slug', $slug)->exists())
                continue;

            $catId = $recipeCategories[$rData['cat']]->id ?? null;

            Recipe::create([
                'title' => $rData['title'],
                'slug' => $slug,
                'description' => $rData['desc'],
                'cook_time' => $rData['time'],
                'is_published' => true,
                'view_count' => rand(50, 2000),
                'recipe_category_id' => $catId,
            ]);
        }

        $this->command->info("✅ Created 30 recipes.");

        // =============================================
        // 5. VOUCHERS / COUPONS
        // =============================================
        $this->command->info('🎟️ Seeding vouchers...');

        $vouchersData = [
            ['code' => 'WELCOME10', 'type' => 'percent', 'value' => 10, 'min_order' => 100000, 'limit' => 100, 'desc' => 'Giảm 10% đơn từ 100K cho khách mới'],
            ['code' => 'SALE20K', 'type' => 'fixed', 'value' => 20000, 'min_order' => 200000, 'limit' => 50, 'desc' => 'Giảm 20K đơn từ 200K'],
            ['code' => 'FREESHIP', 'type' => 'free_shipping', 'value' => 0, 'min_order' => 150000, 'limit' => 200, 'desc' => 'Miễn phí giao hàng đơn từ 150K'],
            ['code' => 'MEGA50', 'type' => 'fixed', 'value' => 50000, 'min_order' => 500000, 'limit' => 30, 'desc' => 'Giảm 50K cho đơn từ 500K'],
            ['code' => 'SUMMER15', 'type' => 'percent', 'value' => 15, 'min_order' => 300000, 'limit' => 80, 'desc' => 'Giảm 15% mùa hè đơn từ 300K'],
            ['code' => 'VIP30K', 'type' => 'fixed', 'value' => 30000, 'min_order' => 250000, 'limit' => 40, 'desc' => 'Ưu đãi VIP giảm 30K đơn từ 250K'],
            ['code' => 'FRESH5', 'type' => 'percent', 'value' => 5, 'min_order' => 50000, 'limit' => 500, 'desc' => 'Giảm 5% cho tất cả sản phẩm tươi sống'],
            ['code' => 'NEWYEAR100', 'type' => 'fixed', 'value' => 100000, 'min_order' => 1000000, 'limit' => 10, 'desc' => 'Giảm 100K đơn từ 1 triệu'],
        ];

        foreach ($vouchersData as $v) {
            Coupon::updateOrCreate(
                ['code' => $v['code']],
                [
                    'code' => $v['code'],
                    'type' => $v['type'],
                    'value' => $v['value'],
                    'min_order_amount' => $v['min_order'],
                    'starts_at' => now(),
                    'expires_at' => now()->addMonths(3),
                    'usage_limit' => $v['limit'],
                    'used_count' => 0,
                    'is_active' => true,
                ]
            );
        }

        $this->command->info("✅ Created " . count($vouchersData) . " vouchers.");

        // =============================================
        // 6. FINISHED
        // =============================================
        $this->command->info('🎉 SFood seeding completed!');
    }
}
