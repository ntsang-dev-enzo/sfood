@extends('layouts.app')
@section('title', 'Sản phẩm - SFood')
@section('content')
    <div class="bg-earth-800 text-cream-50 py-12">
        <div class="max-w-7xl mx-auto px-4">
            <h1 class="text-3xl font-bold">Sản phẩm</h1>
            <p class="text-cream-300 mt-2">{{ __('app.home_subtitle') }}</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-8 flex flex-col md:flex-row gap-8">
        <!-- Sidebar Left -->
        <div class="w-full md:w-1/4 flex flex-col gap-6 sticky top-20 h-fit">
            <!-- Categories -->
            <div class="card p-5 dark:bg-gray-800 dark:border-gray-700 bg-white border border-cream-200 shadow-sm rounded-xl">
                <h3 class="font-bold text-lg text-earth-800 dark:text-gray-300 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-leaf-600 dark:text-leaf-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    {{ __('app.categories') }}
                </h3>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ request()->fullUrlWithQuery(['category' => null, 'page' => null]) }}" class="block px-3 py-2.5 rounded-lg transition-colors {{ !$categorySlug ? 'bg-leaf-600 text-white font-medium' : 'text-earth-700 dark:text-gray-300 hover:bg-cream-100 dark:hover:bg-gray-700' }}">
                            {{ __('app.all_products') }}
                        </a>
                    </li>
                    @foreach ($categories as $cat)
                    <li>
                    <li>
                        <a href="{{ request()->fullUrlWithQuery(['category' => $cat->slug, 'page' => null]) }}" class="block px-3 py-2.5 rounded-lg transition-colors {{ $categorySlug === $cat->slug ? 'bg-leaf-600 text-white font-medium' : 'text-earth-700 dark:text-gray-300 hover:bg-cream-100 dark:hover:bg-gray-700' }}">
                            {{ $cat->name }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>

            <!-- Filter Form -->
            <div class="card p-5 dark:bg-gray-800 dark:border-gray-700 bg-white border border-cream-200 shadow-sm rounded-xl">
                <h3 class="font-bold text-lg text-earth-800 dark:text-gray-300 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-leaf-600 dark:text-leaf-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    {{ __('app.filter') }}
                </h3>
                <form method="GET" action="{{ route('products.index') }}" id="filter-form" class="flex flex-col gap-5">
                    @if($categorySlug)
                        <input type="hidden" name="category" value="{{ $categorySlug }}">
                    @endif
                    <input type="hidden" name="sort" id="sort-hidden" value="{{ $sort }}">
                    <input type="hidden" name="search" value="{{ $search }}">

                    <!-- Price Filter -->
                    <div class="pt-5 border-t border-cream-200 dark:border-gray-700">
                        <label class="block text-sm font-medium text-earth-700 dark:text-gray-300 mb-3">{{ __('app.price_range') }}</label>
                        
                        <div class="flex flex-col gap-2 mb-4">
                            @php
                                $ranges = [
                                    ['label' => __('app.under_100k'), 'min' => 0, 'max' => 100000],
                                    ['label' => __('app.100k_200k'), 'min' => 100000, 'max' => 200000],
                                    ['label' => __('app.200k_500k'), 'min' => 200000, 'max' => 500000],
                                    ['label' => __('app.over_500k'), 'min' => 500000, 'max' => ''],
                                ];
                            @endphp
                            @foreach ($ranges as $range)
                                @php
                                    $isActive = ($minPrice == $range['min'] && $maxPrice == $range['max'] && $maxPrice !== '')
                                        || ($minPrice == $range['min'] && $range['max'] === '' && ($maxPrice === null || $maxPrice === ''));
                                @endphp
                                <button type="button" onclick="applyPriceRange('{{ $range['min'] }}', '{{ $range['max'] }}')"
                                    class="text-left px-3 py-2 rounded-lg text-sm transition-colors border {{ $isActive ? 'border-leaf-600 bg-leaf-50 dark:bg-leaf-900/30 text-leaf-700 dark:text-leaf-400 font-medium' : 'border-transparent text-earth-600 dark:text-gray-300 hover:bg-cream-100 dark:hover:bg-gray-700' }}">
                                    {{ $range['label'] }}
                                </button>
                            @endforeach
                        </div>

                        <div class="flex flex-col gap-3">
                            <div class="relative w-full">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-earth-400 dark:text-gray-500 text-sm">đ</span>
                                <input type="number" name="min_price" id="min_price" value="{{ $minPrice }}" placeholder="{{ __('app.price_from') }}"
                                    class="input-field !pl-7 !py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:placeholder-gray-400" min="0">
                            </div>
                            <div class="relative w-full">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-earth-400 dark:text-gray-500 text-sm">đ</span>
                                <input type="number" name="max_price" id="max_price" value="{{ $maxPrice }}" placeholder="{{ __('app.price_to') }}"
                                    class="input-field !pl-7 !py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:placeholder-gray-400" min="0">
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2 mt-4">
                            <button type="submit" class="btn-primary flex-1 !py-2 !px-4 !text-sm">{{ __('app.apply') }}</button>
                            @if($minPrice || $maxPrice || $search)
                            <a href="{{ route('products.index', ['category' => $categorySlug, 'sort' => $sort]) }}" class="btn-outline flex-1 !py-2 !px-4 !text-sm text-center dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">{{ __('app.clear_filter') }}</a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

        </div>

        <!-- Main Content Right -->
        <div class="w-full md:w-3/4 flex flex-col gap-6">
            <!-- Search & AI Search Unified Block -->
            <div class="relative z-30 bg-white dark:bg-gray-800 border border-cream-200 dark:border-gray-700 shadow-sm rounded-xl flex flex-col">
                <!-- Normal Search (Top) -->
                <div class="p-5 border-b border-cream-200 dark:border-gray-700">
                    <div class="search-autocomplete relative" data-suggest-url="{{ route('search.suggest') }}">
                        <form method="GET" action="{{ route('products.index') }}" class="flex flex-col justify-center">
                            <input type="hidden" name="category" value="{{ $categorySlug }}">
                            <input type="hidden" name="min_price" value="{{ $minPrice }}">
                            <input type="hidden" name="max_price" value="{{ $maxPrice }}">
                            <input type="hidden" name="sort" value="{{ $sort }}">
                            
                            <label class="block text-sm font-bold text-earth-800 dark:text-gray-200 mb-3">{{ __('app.search_products') }}</label>
                            <div class="relative flex items-center">
                                <svg class="absolute left-4 w-5 h-5 text-earth-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input type="text" name="search" value="{{ $search }}" autocomplete="off"
                                    placeholder="{{ __('app.enter_keyword') }}"
                                    class="search-input input-field pl-12 pr-28 py-2.5 text-sm w-full dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:placeholder-gray-400"
                                    data-autocomplete>
                                <div class="absolute right-1 flex items-center gap-1">
                                    <button type="button" class="voice-search-btn p-1.5 rounded-lg text-leaf-600 hover:bg-leaf-50 dark:hover:bg-gray-600 transition" title="Tìm bằng giọng nói">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3zm5-3c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/></svg>
                                    </button>
                                    <label class="cursor-pointer p-1.5 rounded-lg text-leaf-600 hover:bg-leaf-50 dark:hover:bg-gray-600 transition" title="Tìm bằng hình ảnh">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        <input type="file" accept="image/*" class="hidden" onchange="submitGlobalImageSearch(this, '{{ route('products.ai-search') }}')">
                                    </label>
                                    <button type="submit" class="bg-leaf-600 hover:bg-leaf-700 text-white px-3 py-1.5 rounded-lg text-sm font-medium transition">
                                        {{ __('app.search') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div class="suggest-dropdown" data-suggest-dropdown></div>
                    </div>
                </div>
            </div>

            @if(isset($aiResults) && count($aiResults) > 0)
                <div class="bg-gradient-to-br from-leaf-50 to-cream-50 dark:from-gray-800 dark:to-gray-900 border border-leaf-200 dark:border-gray-700 shadow-sm rounded-xl p-5 mb-2">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="text-2xl">✨</span>
                        <h2 class="text-lg font-bold text-leaf-800 dark:text-leaf-400">Kết quả tìm kiếm bằng hình ảnh</h2>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach(collect($aiResults)->take(8) as $res)
                            @php
                                $p = null;
                                if ($res instanceof \App\Models\Product) {
                                    $p = $res;
                                } elseif (is_array($res)) {
                                    $pId = $res['id'] ?? $res['product_id'] ?? null;
                                    if ($pId) $p = \App\Models\Product::find($pId);
                                } elseif (is_numeric($res) || is_string($res)) {
                                    $p = \App\Models\Product::find($res);
                                }
                            @endphp
                            @if($p)
                                @include('partials.product-card', ['product' => $p])
                            @else
                                <div class="p-4 bg-red-100 text-red-800">
                                    <p>Debug: Không thể hiển thị sản phẩm.</p>
                                    <p>Type of res: {{ gettype($res) }}</p>
                                    <p>Class of res: {{ is_object($res) ? get_class($res) : 'N/A' }}</p>
                                    <p>Dump: {{ json_encode($res) }}</p>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Toolbar -->
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 bg-cream-50 dark:bg-gray-800 p-4 rounded-xl border border-cream-200 dark:border-gray-700 shadow-sm">
                <p class="text-earth-600 dark:text-gray-300 font-medium">
                    {{ __('app.showing') }} <span class="text-earth-800 dark:text-gray-300 font-bold">{{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }}</span> {{ __('app.out_of') }} <span class="text-earth-800 dark:text-gray-300 font-bold">{{ $products->total() }}</span> {{ __('app.products') }}
                </p>
                <div class="flex items-center gap-3">
                    <label class="text-sm text-earth-600 dark:text-gray-300 whitespace-nowrap font-medium">{{ __('app.sort_by') }}</label>
                    <select name="sort" onchange="document.getElementById('sort-hidden').value = this.value; document.getElementById('filter-form').submit();" class="input-field !py-2 !w-auto min-w-[180px] font-medium text-earth-800 dark:text-gray-300 shadow-sm cursor-pointer border-earth-300 dark:bg-gray-700 dark:border-gray-600">
                        <option value="popular" @selected($sort === 'popular')>{{ __('app.sort_popular') }}</option>
                        <option value="newest" @selected($sort === 'newest')>{{ __('app.sort_newest') }}</option>
                        <option value="price_asc" @selected($sort === 'price_asc')>{{ __('app.sort_price_asc') }}</option>
                        <option value="price_desc" @selected($sort === 'price_desc')>{{ __('app.sort_price_desc') }}</option>
                        <option value="name_asc" @selected($sort === 'name_asc')>{{ __('app.sort_name_asc') }}</option>
                        <option value="name_desc" @selected($sort === 'name_desc')>{{ __('app.sort_name_desc') }}</option>
                    </select>
                </div>
            </div>

            <!-- Product Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
                @forelse ($products as $product)
                    @include('partials.product-card', ['product' => $product])
                @empty
                    <div class="col-span-full py-16 text-center bg-cream-50 dark:bg-[#121212] rounded-2xl border border-dashed border-cream-300 dark:border-gray-700">
                        <div class="text-5xl mb-4">🔍</div>
                        <h3 class="text-xl font-bold text-earth-800 mb-2">Không tìm thấy sản phẩm</h3>
                        <p class="text-earth-500">Vui lòng thử lại với từ khóa hoặc bộ lọc khác.</p>
                        <a href="{{ route('products.index') }}" class="btn-outline mt-6 inline-flex">Xóa tất cả bộ lọc</a>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($products->hasPages())
                <div class="mt-10">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
    
    <script>
        function applyPriceRange(min, max) {
            document.getElementById('min_price').value = min;
            document.getElementById('max_price').value = max;
            document.getElementById('filter-form').submit();
        }
    </script>
@endsection

