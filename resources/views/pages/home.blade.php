@extends('layouts.app')
@section('title', 'SFood - Thực phẩm tươi sống')
@section('content')
    {{-- Hero --}}
    <section class="relative bg-gradient-to-br from-leaf-700 via-leaf-600 to-earth-700 text-cream-50 overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-10 left-10 w-64 h-64 rounded-full bg-cream-200 blur-3xl"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 rounded-full bg-leaf-300 blur-3xl"></div>
        </div>
        <div class="max-w-7xl mx-auto px-4 py-16 md:py-24 relative">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <span class="badge bg-cream-50/20 text-cream-100 mb-4">🌿 Tươi mỗi ngày · Giao trong 2h</span>
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold leading-tight mb-6">
                        Thực phẩm sạch<br>
                        <span class="text-cream-200">cho căn bếp Việt</span>
                    </h1>
                    <p class="text-cream-200 text-lg mb-8 max-w-lg leading-relaxed">
                        Mua sắm thông minh với AI nhận diện hình ảnh, gợi ý món ăn và mua nhanh nguyên liệu từ video nấu ăn.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('products.index') }}" class="btn-primary bg-cream-50 !text-leaf-700 hover:!bg-white">Khám phá sản phẩm</a>
                        <a href="{{ route('recipes.index') }}" class="btn-outline !border-cream-200 !text-cream-50 hover:!bg-cream-50/10">Xem công thức</a>
                    </div>
                </div>
                <div class="hidden lg:block">
                    <div class="bg-cream-50/10 backdrop-blur rounded-3xl p-8 border border-cream-200/20">
                        @include('partials.search-box', ['size' => 'lg', 'placeholder' => 'Tìm rau củ, thịt cá, món ăn...'])
                        <div class="mt-6 grid grid-cols-3 gap-4 text-center">
                            <div><p class="text-2xl font-bold">500+</p><p class="text-xs text-cream-300">Sản phẩm</p></div>
                            <div><p class="text-2xl font-bold">50+</p><p class="text-xs text-cream-300">Công thức</p></div>
                            <div><p class="text-2xl font-bold">2h</p><p class="text-xs text-cream-300">Giao hàng</p></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Trust badges --}}
    <section class="bg-cream-50 border-b border-cream-300">
        <div class="max-w-7xl mx-auto px-4 py-6 grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach ([['🚚', 'Giao nhanh 2h', 'Nội thành TP.HCM'], ['🌱', 'Nguồn gốc rõ', '100% truy xuất'], ['💳', 'Thanh toán an toàn', 'VNPay · MoMo · COD'], ['🤖', 'AI thông minh', 'Tìm ảnh & giọng nói']] as $badge)
                <div class="flex items-center gap-3 p-3">
                    <span class="text-2xl">{{ $badge[0] }}</span>
                    <div>
                        <p class="font-semibold text-sm text-earth-800">{{ $badge[1] }}</p>
                        <p class="text-xs text-earth-500">{{ $badge[2] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Categories --}}
    <section class="max-w-7xl mx-auto px-4 py-14">
        <div class="text-center mb-10">
            <h2 class="section-title">Danh mục nổi bật</h2>
            <p class="section-subtitle">Chọn loại thực phẩm bạn cần hôm nay</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            @php $icons = ['🥬', '🥩', '🥤', '🧂', '🍎', '🐟']; @endphp
            @foreach ($categories as $i => $cat)
                <a href="{{ route('products.index', ['category' => $cat->slug]) }}"
                    class="group bg-cream-50 rounded-2xl p-5 text-center border border-cream-300 hover:border-leaf-500 hover:shadow-lg transition">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-leaf-100 group-hover:bg-leaf-600 flex items-center justify-center text-2xl group-hover:scale-110 transition duration-300 mb-3">
                        {{ $icons[$i % count($icons)] }}
                    </div>
                    <p class="font-semibold text-earth-800 text-sm">{{ $cat->name }}</p>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Best sellers --}}
    <section class="bg-cream-50 py-14">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h2 class="section-title">Sản phẩm bán chạy</h2>
                    <p class="section-subtitle">Được khách hàng tin chọn nhiều nhất</p>
                </div>
                <a href="{{ route('products.index') }}" class="text-leaf-700 font-semibold hover:text-leaf-800 transition hidden sm:inline">Xem tất cả →</a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
                @foreach ($products as $product)
                    @include('partials.product-card', ['product' => $product])
                @endforeach
            </div>
        </div>
    </section>

    {{-- Gợi ý món ăn --}}
    <section class="max-w-7xl mx-auto px-4 py-14">
        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="section-title">Gợi ý món ăn hôm nay</h2>
                <p class="section-subtitle">Công thức ngon, nguyên liệu sẵn có — mua nhanh 1 nút</p>
            </div>
            <a href="{{ route('recipes.index') }}" class="text-leaf-700 font-semibold hover:text-leaf-800 transition hidden sm:inline">Tất cả công thức →</a>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($suggestedRecipes as $recipe)
                @include('partials.recipe-card', ['recipe' => $recipe])
            @endforeach
        </div>
    </section>

    {{-- Video nấu ăn --}}
    <section class="bg-earth-800 py-14">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h2 class="text-2xl md:text-3xl font-bold text-cream-50">Video nấu ăn</h2>
                    <p class="text-cream-300 mt-2">Học nấu ăn và mua nguyên liệu ngay trên SFood</p>
                </div>
                <a href="{{ route('recipes.index') }}" class="text-leaf-400 font-semibold hover:text-leaf-300 transition hidden sm:inline">Xem thêm →</a>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach ($recipes as $recipe)
                    @include('partials.recipe-card', ['recipe' => $recipe])
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="max-w-7xl mx-auto px-4 py-14">
        <div class="bg-gradient-to-r from-leaf-600 to-leaf-700 rounded-3xl p-8 md:p-12 text-center text-cream-50 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-48 h-48 bg-cream-50/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
            <h2 class="text-2xl md:text-3xl font-bold mb-4 relative">Bắt đầu mua sắm thực phẩm tươi</h2>
            <p class="text-cream-200 mb-6 max-w-lg mx-auto relative">Đăng ký tài khoản để lưu địa chỉ, theo dõi đơn hàng và nhận ưu đãi độc quyền.</p>
            <div class="flex flex-wrap justify-center gap-4 relative">
                <a href="{{ route('register') }}" class="btn-primary bg-cream-50 !text-leaf-700 hover:!bg-white">Đăng ký miễn phí</a>
                <a href="{{ route('about') }}" class="btn-outline !border-cream-200 !text-cream-50">Tìm hiểu thêm</a>
            </div>
        </div>
    </section>
@endsection
