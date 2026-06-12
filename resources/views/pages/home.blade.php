@extends('layouts.app')
@section('title', 'SFood - Thực phẩm tươi sống')

@push('scripts')
    <style>
        @keyframes float {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
            }

            50% {
                transform: translateY(-20px) rotate(2deg);
            }
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        .animate-float-delayed {
            animation: float 6s ease-in-out 3s infinite;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .dark .glass-card {
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
@endpush

@section('content')
    {{-- Hero --}}
    <section
        class="relative bg-gradient-to-br from-leaf-800 via-leaf-600 to-earth-800 text-cream-50 overflow-hidden min-h-[80vh] flex items-center">
        <!-- Animated Background Orbs -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-1/4 left-1/4 w-96 h-96 rounded-full bg-leaf-400/20 blur-[100px] animate-pulse"></div>
            <div class="absolute bottom-1/4 right-1/4 w-[500px] h-[500px] rounded-full bg-earth-500/20 blur-[120px] animate-pulse"
                style="animation-delay: 2s;"></div>
            <div class="absolute top-1/2 left-1/2 w-64 h-64 rounded-full bg-cream-100/10 blur-[80px] animate-pulse"
                style="animation-delay: 4s;"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 py-20 relative z-10 w-full">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="space-y-8 animate-fade-in-up">
                    <div
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass-card text-sm font-medium text-cream-100 shadow-lg border-white/20">
                        <span class="relative flex h-3 w-3">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                        </span>
                        {{ __('app.home_badge') ?? 'Tươi mới mỗi ngày · Giao hàng trong 2h' }}
                    </div>

                    <h1 class="text-5xl md:text-6xl lg:text-7xl font-extrabold leading-[1.1] tracking-tight">
                        {!! __('app.home_heading_1') ?? 'Thực phẩm sạch<br>' !!}
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-cream-100 to-leaf-200">
                            {{ __('app.home_heading_2') ?? 'cho gia đình Việt' }}
                        </span>
                    </h1>

                    <p class="text-cream-100/80 text-lg md:text-xl max-w-lg leading-relaxed font-medium">
                        {{ __('app.home_description') ?? 'Khám phá trải nghiệm mua sắm thông minh với AI. Nhận diện nguyên liệu bằng hình ảnh, nấu ăn qua video trực quan.' }}
                    </p>

                    <div class="flex flex-wrap gap-4 pt-4">
                        <a href="{{ route('products.index') }}"
                            class="inline-flex items-center justify-center gap-2 bg-cream-50 text-leaf-800 hover:bg-white hover:scale-105 hover:shadow-xl hover:shadow-cream-50/20 font-bold px-8 py-4 rounded-2xl transition-all duration-300">
                            🛒 {{ __('app.explore_now') ?? 'Khám phá ngay' }}
                        </a>
                        <a href="{{ route('recipes.index') }}"
                            class="inline-flex items-center justify-center gap-2 glass-card hover:bg-white/10 text-cream-50 font-semibold px-8 py-4 rounded-2xl transition-all duration-300 hover:scale-105">
                            👩‍🍳 {{ __('app.view_recipes') ?? 'Xem công thức' }}
                        </a>
                    </div>
                </div>

                <div class="hidden lg:flex justify-center items-center relative">
                    <!-- Floating visual elements -->
                    <div class="relative w-full max-w-md animate-float">
                        <div
                            class="glass-card rounded-[2.5rem] p-8 shadow-2xl border-white/20 backdrop-blur-md relative z-10">
                            @include('partials.search-box', ['size' => 'lg', 'placeholder' => __('app.search_placeholder') ?? 'Hôm nay bạn muốn ăn gì?'])

                            <div class="mt-8 grid grid-cols-3 gap-6 text-center">
                                <div class="p-4 rounded-2xl bg-white/5 hover:bg-white/10 transition cursor-default">
                                    <p class="text-3xl font-bold text-cream-50 mb-1">500+</p>
                                    <p class="text-sm text-cream-200 font-medium">{{ __('app.products') }}</p>
                                </div>
                                <div class="p-4 rounded-2xl bg-white/5 hover:bg-white/10 transition cursor-default">
                                    <p class="text-3xl font-bold text-cream-50 mb-1">50+</p>
                                    <p class="text-sm text-cream-200 font-medium">{{ __('app.recipes') ?? 'Công thức' }}</p>
                                </div>
                                <div class="p-4 rounded-2xl bg-white/5 hover:bg-white/10 transition cursor-default">
                                    <p class="text-3xl font-bold text-cream-50 mb-1">2h</p>
                                    <p class="text-sm text-cream-200 font-medium">{{ __('app.delivery') ?? 'Giao hàng' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Floating badges behind -->
                        <div
                            class="absolute -top-10 -right-10 bg-earth-500 text-white p-4 rounded-2xl shadow-xl rotate-12 animate-float-delayed z-0 border-4 border-leaf-800">
                            <span class="text-3xl">🥩</span>
                        </div>
                        <div
                            class="absolute -bottom-10 -left-10 bg-leaf-500 text-white p-4 rounded-2xl shadow-xl -rotate-12 animate-float z-20 border-4 border-leaf-800">
                            <span class="text-3xl">🥬</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom wave -->
        <div class="absolute bottom-0 left-0 right-0 w-full overflow-hidden leading-none">
            <svg class="relative block w-[calc(100%+1.3px)] h-[50px] lg:h-[100px]" data-name="Layer 1"
                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path
                    d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V120H0V95.8C59.71,118.08,130.83,119.29,200.9,105.9Z"
                    class="fill-cream-50 dark:fill-[#18181b]"></path>
            </svg>
        </div>
    </section>

    {{-- Trust badges --}}
    <section class="bg-cream-50 dark:bg-[#121212] relative z-20 border-b border-cream-200 dark:border-gray-800">
        <div class="max-w-7xl mx-auto px-4 py-10 -mt-10 relative z-30 grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach ([['🚚', 'Giao nhanh 2h', 'Nội thành TP.HCM'], ['🌱', 'Nguồn gốc rõ', '100% truy xuất'], ['💳', 'Thanh toán an toàn', 'VNPay · MoMo · COD'], ['🤖', 'AI thông minh', 'Tìm ảnh & giọng nói']] as $badge)
                <div
                    class="bg-cream-50 dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-cream-200 dark:border-gray-700 hover:shadow-md hover:-translate-y-1 transition duration-300 flex flex-col md:flex-row items-center md:items-start text-center md:text-left gap-4 group">
                    <div
                        class="w-12 h-12 rounded-xl bg-leaf-100 text-leaf-700 flex items-center justify-center text-2xl group-hover:scale-110 group-hover:bg-leaf-200 transition duration-300 flex-shrink-0">
                        {{ $badge[0] }}
                    </div>
                    <div>
                        <p class="font-bold text-earth-800">{{ $badge[1] }}</p>
                        <p class="text-xs text-earth-500 mt-1">{{ $badge[2] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Banners Bento Grid --}}
    @if(isset($banners) && $banners->count() > 0)
        <section class="bg-cream-50 dark:bg-gray-900 py-8 border-b border-cream-200 dark:border-gray-800">
            <div class="max-w-7xl mx-auto px-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 auto-rows-[200px]">
                    @foreach($banners->take(4) as $index => $banner)
                        @php
                            // Bento logic
                            $colSpan = 'col-span-1 md:col-span-2';
                            $rowSpan = 'row-span-1';

                            if ($banners->count() == 1) {
                                $colSpan = 'col-span-1 md:col-span-4';
                                $rowSpan = 'row-span-2';
                            } elseif ($banners->count() == 2) {
                                $colSpan = 'col-span-1 md:col-span-2';
                                $rowSpan = 'row-span-2';
                            } elseif ($banners->count() == 3) {
                                if ($index == 0) {
                                    $colSpan = 'col-span-1 md:col-span-2';
                                    $rowSpan = 'row-span-2';
                                } else {
                                    $colSpan = 'col-span-1 md:col-span-2';
                                    $rowSpan = 'row-span-1';
                                }
                            } elseif ($banners->count() >= 4) {
                                if ($index == 0) {
                                    $colSpan = 'col-span-1 md:col-span-2';
                                    $rowSpan = 'row-span-2';
                                } else {
                                    $colSpan = 'col-span-1 md:col-span-2';
                                    $rowSpan = 'row-span-1';
                                    if ($index == 3) {
                                        $colSpan = 'col-span-1 md:col-span-4 lg:col-span-2';
                                    }
                                }
                            }
                        @endphp
                        <a href="{{ $banner->link ?? '#' }}"
                            class="{{ $colSpan }} {{ $rowSpan }} block relative group overflow-hidden rounded-3xl shadow-sm hover:shadow-xl transition-all duration-500 bg-earth-800">
                            <img src="{{ asset('storage/' . $banner->image_path) }}" alt="{{ $banner->title }}"
                                class="w-full h-full object-cover group-hover:scale-105 group-hover:opacity-90 transition-all duration-700">
                            @if($banner->title)
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent flex flex-col justify-end p-6 md:p-8">
                                    <h3
                                        class="text-white text-xl md:text-2xl font-extrabold tracking-tight transform translate-y-2 group-hover:translate-y-0 transition-transform duration-500">
                                        {{ $banner->title }}
                                    </h3>
                                    <p
                                        class="text-cream-200 text-sm mt-2 opacity-0 group-hover:opacity-100 transform translate-y-4 group-hover:translate-y-0 transition-all duration-500 delay-75">
                                        Khám phá ngay →</p>
                                </div>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif


    {{-- Flash sale --}}
    @if(!empty($flashSales) && $flashSales->count())
        @php
            $earliestEnd = null;
            foreach ($flashSales as $p) {
                foreach ($p->variants as $v) {
                    if ($v->isFlashActive()) {
                        if (!$earliestEnd || $v->flash_sale_ends_at < $earliestEnd) {
                            $earliestEnd = $v->flash_sale_ends_at;
                        }
                    }
                }
            }
        @endphp
        <section
            class="bg-cream-50 dark:bg-gray-900 py-12 relative overflow-hidden border-b border-cream-200 dark:border-gray-800">
            <div class="max-w-7xl mx-auto px-4 relative z-10">
                <div class="flex flex-col sm:flex-row justify-between sm:items-end gap-4 mb-6 bg-gradient-to-r from-red-500 to-orange-500 p-6 rounded-3xl text-white shadow-lg shadow-red-500/20"
                    @if($earliestEnd) data-countdown="{{ $earliestEnd->toIso8601String() }}" @endif>
                    <div>
                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                            <span class="animate-pulse text-2xl">⚡</span>
                            <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight">Flash Sale</h2>
                            @if($earliestEnd)
                                <div
                                    class="ml-0 sm:ml-2 flex items-center gap-2 bg-black/20 px-3 py-1.5 rounded-xl border border-white/10">
                                    <span class="text-xs font-medium uppercase opacity-90">Kết thúc sau</span>
                                    <span class="countdown-timer font-mono font-bold tracking-wider">00:00:00</span>
                                </div>
                            @endif
                        </div>
                        <p class="text-red-100 font-medium mt-1">Giá sốc trong thời gian có hạn — mua ngay kẻo lỡ!</p>
                    </div>
                    <a href="{{ route('vouchers.index') }}"
                        class="bg-white text-red-600 px-6 py-2.5 rounded-xl font-bold hover:bg-red-50 hover:scale-105 transition-all shadow-sm text-center inline-block">Nhận
                        mã & phí ship →</a>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-5">
                    @foreach($flashSales as $p)
                        @include('partials.product-card', ['product' => $p])
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Categories --}}
    <section class="bg-cream-50 dark:bg-[#121212] py-16 border-b border-cream-200 dark:border-gray-800">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-extrabold text-earth-800 tracking-tight">Danh mục nổi bật</h2>
                <p class="text-earth-500 mt-3 font-medium text-lg">Hôm nay bạn cần gì? Chúng tôi đều có tươi mới.</p>
            </div>
            <div class="flex gap-6 overflow-x-auto justify-center scrollbar-hide pb-4">
                @foreach ($categories as $cat)
                    <a href="{{ route('products.index', ['category' => $cat->slug]) }}"
                        class="flex-shrink-0 w-40 group bg-cream-50 dark:bg-gray-800 rounded-[2rem] p-6 text-center border border-cream-200 dark:border-gray-700 shadow-sm hover:border-leaf-400 hover:shadow-xl hover:shadow-leaf-100 hover:-translate-y-2 transition-all duration-300">
                        <div
                            class="w-20 h-20 mx-auto rounded-[1.5rem] bg-gradient-to-br from-leaf-100 to-cream-200 group-hover:from-leaf-200 group-hover:to-leaf-300 flex items-center justify-center text-4xl group-hover:scale-110 transition duration-500 shadow-inner mb-4">
                            {{ $cat->icon ?? '🍱' }}
                        </div>
                        <p class="font-bold text-earth-800 text-base group-hover:text-leaf-700 transition">{{ $cat->name }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Gợi ý dành riêng cho bạn --}}
    @if(isset($suggestedProductsHabits) && $suggestedProductsHabits->count() > 0)
        <section class="bg-cream-100 dark:bg-[#121212] py-16 border-b border-cream-200 dark:border-gray-800">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between items-end mb-8">
                    <div>
                        <h2 class="text-3xl font-extrabold text-earth-800 tracking-tight">Gợi ý dành riêng cho bạn</h2>
                        <p class="text-earth-500 mt-2 font-medium">Lựa chọn hàng đầu dựa trên sở thích của bạn</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach ($suggestedProductsHabits as $product)
                        @include('partials.product-card', ['product' => $product])
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Best sellers --}}
    @if(isset($bestSellers) && $bestSellers->count() > 0)
        <section class="bg-white dark:bg-[#121212] py-16 border-b border-cream-200 dark:border-gray-800">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between items-end mb-8">
                    <div>
                        <h2 class="text-3xl font-extrabold text-earth-800 tracking-tight">Sản phẩm bán chạy nhất</h2>
                        <p class="text-earth-500 mt-2 font-medium">Những mặt hàng được yêu thích nhất bởi khách hàng</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach ($bestSellers as $product)
                        @include('partials.product-card', ['product' => $product])
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Nổi bật --}}
    <section class="bg-cream-50 dark:bg-[#121212] py-16 relative overflow-hidden">
        <!-- Background decorative blur -->
        <div
            class="absolute top-0 right-0 w-96 h-96 bg-leaf-200/40 rounded-full blur-[120px] -translate-y-1/2 translate-x-1/3 pointer-events-none">
        </div>

        <div class="max-w-7xl mx-auto px-4 relative z-10">
            <div class="mt-8 text-center sm:hidden">
                <a href="{{ route('products.index') }}" class="btn-outline w-full rounded-full">Xem tất cả sản phẩm</a>
            </div>
        </div>
    </section>

    {{-- Gợi ý món ăn --}}
    <section class="bg-cream-100 dark:bg-[#121212] py-20 border-y border-cream-200 dark:border-gray-800">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-10 gap-4">
                <div class="max-w-2xl">
                    <span class="text-leaf-600 font-bold tracking-wider text-sm uppercase mb-2 block">Thực đơn mỗi
                        ngày</span>
                    <h2 class="text-3xl md:text-4xl font-extrabold text-earth-800 tracking-tight mb-3">Hôm nay ăn gì?</h2>
                    <p class="text-earth-500 font-medium text-lg">Khám phá các công thức nấu ăn ngon, nguyên liệu có sẵn.
                        Mua đầy đủ gia vị chỉ với 1 thao tác.</p>
                </div>
                <a href="{{ route('recipes.index') }}"
                    class="btn-primary rounded-full !px-6 whitespace-nowrap hidden md:inline-flex shadow-leaf-500/30">
                    Khám phá bếp SFood →
                </a>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach ($suggestedRecipes as $recipe)
                    @include('partials.recipe-card', ['recipe' => $recipe])
                @endforeach
            </div>
        </div>
    </section>

    {{-- Video nấu ăn --}}
    <section class="bg-earth-900 py-20 relative overflow-hidden">
        <div
            class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] mix-blend-overlay">
        </div>
        <div class="absolute top-1/2 left-0 w-64 h-64 bg-leaf-600/30 rounded-full blur-[100px] -translate-y-1/2"></div>

        <div class="max-w-7xl mx-auto px-4 relative z-10">
            <div class="flex justify-between items-end mb-10">
                <div>
                    <h2 class="text-3xl md:text-4xl font-extrabold text-cream-50 tracking-tight mb-2">Video Hướng Dẫn</h2>
                    <p class="text-cream-300 font-medium text-lg">Học mẹo vặt và nấu ăn trực quan từ chuyên gia</p>
                </div>
                <a href="{{ route('recipes.index') }}"
                    class="text-leaf-400 font-bold hover:text-leaf-300 transition-all hidden sm:inline-flex items-center gap-2 hover:gap-3 bg-white/5 px-5 py-2.5 rounded-full backdrop-blur">
                    Xem toàn bộ <span>→</span>
                </a>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ($recipes as $recipe)
                    @include('partials.recipe-card', ['recipe' => $recipe])
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-cream-50 dark:bg-gray-900 py-20">
        <div class="max-w-5xl mx-auto px-4">
            <div
                class="bg-gradient-to-br from-leaf-600 via-leaf-700 to-earth-800 rounded-[3rem] p-10 md:p-16 text-center text-cream-50 relative overflow-hidden shadow-2xl shadow-leaf-900/20 group">
                <!-- Hover animations -->
                <div
                    class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/food.png')] opacity-10 group-hover:opacity-20 transition-opacity duration-700">
                </div>
                <div
                    class="absolute top-0 right-0 w-64 h-64 bg-cream-50/10 rounded-full -translate-y-1/2 translate-x-1/3 blur-2xl group-hover:scale-150 transition-transform duration-1000">
                </div>
                <div
                    class="absolute bottom-0 left-0 w-64 h-64 bg-leaf-400/20 rounded-full translate-y-1/2 -translate-x-1/3 blur-2xl group-hover:scale-150 transition-transform duration-1000 delay-100">
                </div>

                <div class="relative z-10">
                    <span
                        class="inline-block p-4 rounded-3xl bg-white/10 backdrop-blur border border-white/20 mb-6 group-hover:scale-110 group-hover:-translate-y-2 transition-transform duration-500 shadow-xl">
                        <span class="text-4xl">🎁</span>
                    </span>
                    <h2 class="text-3xl md:text-5xl font-extrabold mb-6 tracking-tight leading-tight">
                        Trở thành thành viên SFood<br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-cream-100 to-leaf-200">Nhận vô vàn
                            ưu đãi</span>
                    </h2>
                    <p class="text-cream-100 mb-10 max-w-xl mx-auto font-medium text-lg">
                        Đăng ký ngay hôm nay để nhận mã giảm giá 50K cho đơn hàng đầu tiên, miễn phí giao hàng và tích điểm
                        không giới hạn!
                    </p>
                    <div class="flex flex-col sm:flex-row justify-center gap-4">
                        <a href="{{ route('register') }}"
                            class="inline-flex items-center justify-center gap-2 bg-white text-leaf-700 hover:bg-cream-50 hover:scale-105 hover:shadow-xl font-bold px-8 py-4 rounded-2xl transition-all duration-300">
                            ✨ Đăng ký nhận quà
                        </a>
                        <a href="{{ route('about') }}"
                            class="inline-flex items-center justify-center gap-2 border-2 border-white/30 text-white hover:bg-white/10 font-bold px-8 py-4 rounded-2xl transition-all duration-300">
                            Tìm hiểu SFood
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Mini Features Module --}}
    <section class="max-w-7xl mx-auto px-4 -mt-8 relative z-20 mb-12">
        <div
            class="bg-white rounded-3xl p-6 shadow-xl border border-cream-200 grid grid-cols-4 md:grid-cols-7 gap-4 justify-center text-center">
            <a href="{{ route('products.index') }}" class="group block space-y-2">
                <div
                    class="w-14 h-14 mx-auto bg-green-100 text-green-600 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 group-hover:bg-green-500 group-hover:text-white transition duration-300 shadow-sm">
                    🛒</div>
                <p class="text-xs font-bold text-earth-700">Chợ tươi</p>
            </a>
            <a href="{{ route('recipes.index') }}" class="group block space-y-2">
                <div
                    class="w-14 h-14 mx-auto bg-orange-100 text-orange-600 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 group-hover:bg-orange-500 group-hover:text-white transition duration-300 shadow-sm">
                    👩‍🍳</div>
                <p class="text-xs font-bold text-earth-700">Vào bếp</p>
            </a>
            <a href="{{ route('vouchers.index') }}" class="group block space-y-2">
                <div
                    class="w-14 h-14 mx-auto bg-red-100 text-red-600 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 group-hover:bg-red-500 group-hover:text-white transition duration-300 shadow-sm">
                    🎫</div>
                <p class="text-xs font-bold text-earth-700">Mã giảm giá</p>
            </a>

            <a href="{{ route('favorites.index') }}" class="group block space-y-2">
                <div
                    class="w-14 h-14 mx-auto bg-pink-100 text-pink-600 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 group-hover:bg-pink-500 group-hover:text-white transition duration-300 shadow-sm">
                    ❤️</div>
                <p class="text-xs font-bold text-earth-700">Yêu thích</p>
            </a>
            <a href="{{ route('orders.index') }}" class="group block space-y-2">
                <div
                    class="w-14 h-14 mx-auto bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 group-hover:bg-blue-500 group-hover:text-white transition duration-300 shadow-sm">
                    📦</div>
                <p class="text-xs font-bold text-earth-700">Đơn hàng</p>
            </a>
            <a href="{{ route('faq') }}" class="group block space-y-2">
                <div
                    class="w-14 h-14 mx-auto bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 group-hover:bg-purple-500 group-hover:text-white transition duration-300 shadow-sm">
                    ❓</div>
                <p class="text-xs font-bold text-earth-700">Hỏi đáp</p>
            </a>
            <a href="{{ route('contact') }}" class="group block space-y-2">
                <div
                    class="w-14 h-14 mx-auto bg-teal-100 text-teal-600 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 group-hover:bg-teal-500 group-hover:text-white transition duration-300 shadow-sm">
                    📞</div>
                <p class="text-xs font-bold text-earth-700">Liên hệ</p>
            </a>
        </div>
    </section>
@endsection