@extends('layouts.app')

@section('title', $product->name . ' - SFood')

@section('content')
    @php use App\Support\MediaHelper; @endphp

    <div class="bg-[#f7f7f5] dark:bg-gray-900 min-h-screen">
        {{-- TOÀN BỘ TRANG NHỎ GỌN: Giới hạn độ rộng tối đa max-w-5xl --}}
        <div class="max-w-5xl mx-auto px-4 py-8">

            {{-- Breadcrumb --}}
            <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-8">
                <a href="{{ route('home') }}" class="hover:text-black dark:hover:text-white transition">{{ __('app.home_title') }}</a>
                <span>/</span>
                <a href="{{ route('products.index') }}" class="hover:text-black dark:hover:text-white transition">{{ __('app.products') }}</a>
                <span>/</span>
                <span class="text-black dark:text-gray-300 font-medium">{{ $product->name }}</span>
            </nav>

            {{-- Main Product Section --}}
            <div class="grid lg:grid-cols-2 gap-8 items-start">

                {{-- LEFT: Images --}}
                <div class="lg:sticky lg:top-24">
                    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm overflow-hidden border border-gray-100 dark:border-gray-700 w-full">
                        {{-- MAIN IMAGE --}}
                        <div class="aspect-square bg-white dark:bg-gray-800 p-6">
                            <img id="product-main-image"
                                src="{{ MediaHelper::productImage($product->thumbnail, $product->id) }}"
                                alt="{{ $product->name }}" class="w-full h-full object-contain transition duration-300">
                        </div>

                        {{-- GALLERY --}}
                        @php
                            $gallery = collect();
                            if ($product->thumbnail) {
                                $gallery->push($product->thumbnail);
                            }
                            $product->images->pluck('image_path')->filter()->each(fn($p) => $gallery->push($p));
                            foreach ($product->variants as $v) {
                                if (!empty($v->image)) {
                                    $gallery->push($v->image);
                                }
                            }
                            $gallery = $gallery->unique()->values();
                        @endphp

                    <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                        <div id="thumbs-wrap" class="flex gap-3 overflow-x-auto scrollbar-hide">
                            @foreach ($gallery as $idx => $path)
                                <button type="button"
                                    class="thumb-item flex-shrink-0 rounded-2xl overflow-hidden border-2 border-transparent hover:border-green-500 transition"
                                    data-src="{{ asset('storage/' . $path) }}">
                                    <img src="{{ asset('storage/' . $path) }}" class="w-16 h-16 object-cover">
                                </button>
                            @endforeach
                        </div>
                    </div>
                    </div>
                </div>

                {{-- RIGHT: Purchase Information --}}
                <div class="flex flex-col">
                    {{-- CATEGORY --}}
                    <div class="mb-4">
                        <span
                            class="inline-flex items-center px-4 py-2 rounded-full bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-400 text-sm font-medium">
                            {{ $product->category?->name }}
                        </span>
                    </div>

                    {{-- TITLE --}}
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-300 leading-tight">
                        {{ $product->name }}
                    </h1>

                    {{-- PRICE: Hiển thị giá Sale và giá Gốc của biến thể đầu tiên mặc định --}}
                    @php
                        $firstVariant = $product->variants->where('is_active', true)->firstWhere('id', $defaultVariantId)
                            ?? $product->variants->where('is_active', true)->first();
                    @endphp

                    <div class="mt-4 flex items-end gap-3">
                        @if ($firstVariant)
                            <span class="text-3xl font-bold text-green-600" id="product-display-price">
                                {{ number_format($firstVariant->effectivePrice()) }}đ
                            </span>

                            <span class="text-gray-400 line-through text-base" id="product-display-old-price"
                                style="display: {{ ($firstVariant->effectivePrice() < $firstVariant->price) ? 'inline' : 'none' }}">
                                {{ number_format($firstVariant->price) }}đ
                            </span>
                        @else
                            <span class="text-3xl font-bold text-green-600" id="product-display-price">
                                {{ number_format($product->minPrice()) }}đ
                            </span>
                            <span class="text-gray-400 line-through text-base" id="product-display-old-price"
                                style="display: none;"></span>
                        @endif
                    </div>

                    {{-- FLASH SALE MODULE --}}
                    <div id="flash-sale-module" class="mt-4 flex-col rounded-xl bg-gradient-to-r from-orange-500 to-red-600 text-white shadow-lg overflow-hidden" style="display: {{ $firstVariant && $firstVariant->isFlashActive() ? 'flex' : 'none' }}">
                        <div class="p-4 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-2xl animate-pulse">⚡</span>
                                <span class="font-bold text-lg italic tracking-wider">FLASHSALE</span>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="text-xs uppercase font-medium mb-1 opacity-90">Kết thúc sau</span>
                                <div class="flex gap-1 text-sm font-bold font-mono" id="flash-countdown">
                                    <span class="bg-black/20 px-2 py-1 rounded cd-days">00</span>:
                                    <span class="bg-black/20 px-2 py-1 rounded cd-hours">00</span>:
                                    <span class="bg-black/20 px-2 py-1 rounded cd-mins">00</span>:
                                    <span class="bg-black/20 px-2 py-1 rounded cd-secs">00</span>
                                </div>
                            </div>
                        </div>
                        <div class="px-4 pb-4">
                            <!-- Progress Bar -->
                            <div class="relative w-full h-5 bg-black/20 rounded-full overflow-hidden">
                                <div id="flash-progress-bar" class="absolute top-0 left-0 h-full bg-gradient-to-r from-yellow-400 to-yellow-300 rounded-full transition-all duration-500" style="width: 0%"></div>
                                <div id="flash-progress-text" class="absolute inset-0 flex items-center justify-center text-xs font-bold text-white drop-shadow-md z-10 w-full text-center">
                                    Đang bán chạy
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- AVERAGE RATING & SHORT DESCRIPTION --}}
                    @php
                        $avgRating = collect($product->reviews)->avg('rating');
                        $reviewCount = collect($product->reviews)->count();
                    @endphp
                    @if($reviewCount > 0)
                        <div class="mt-2 flex items-center gap-2">
                            <span class="text-yellow-400 text-lg">★</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200">{{ number_format($avgRating, 1) }}</span>
                            <span class="text-gray-500 dark:text-gray-400 text-sm">({{ $reviewCount }} đánh giá)</span>
                        </div>
                    @endif

                    @if($product->short_description)
                        <div class="mt-4 text-gray-600 dark:text-gray-300 text-sm leading-relaxed border-l-4 border-leaf-500 pl-4">
                            {{ $product->short_description }}
                        </div>
                    @endif

                    {{-- FORM --}}
                    <form method="POST" action="{{ route('products.add-to-cart', $product) }}" class="mt-6">
                        @csrf

                        {{-- VARIANTS --}}
                        <div class="mb-6">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-200 mb-3">{{ __('app.choose_variant') }}</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($product->variants->where('is_active', true) as $variant)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="variant_id" value="{{ $variant->id }}"
                                            class="peer hidden variant-radio" @checked(old('variant_id', $defaultVariantId) == $variant->id)>
                                        <div
                                            class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 peer-checked:border-green-500 peer-checked:bg-green-50 dark:peer-checked:bg-green-900/30 transition relative overflow-hidden">
                                            @if($variant->isFlashActive())
                                                <div class="absolute top-0 right-0 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-bl-lg">⚡ SALE</div>
                                            @endif
                                            <div class="font-medium text-sm text-gray-900 dark:text-gray-300">{{ $variant->name }}</div>
                                            <div class="text-xs text-green-600 dark:text-green-400 mt-0.5">
                                                {{ number_format($variant->effectivePrice()) }}đ
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- QUANTITY --}}
                        <div class="mb-6">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-200 mb-3">{{ __('app.quantity') }}</h3>
                            <div class="flex items-center gap-4">
                                <div class="flex items-center bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl overflow-hidden">
                                    <button type="button" id="qty-decr"
                                        class="w-12 h-12 text-lg hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-300 transition">−</button>
                                    <input id="quantity" type="number" name="quantity" value="1" min="1"
                                        class="w-16 text-center outline-none border-0 text-sm dark:bg-gray-800 dark:text-gray-300">
                                    <button type="button" id="qty-incr"
                                        class="w-12 h-12 text-lg hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-300 transition">+</button>
                                </div>
                            </div>
                        </div>

                        {{-- BUTTONS --}}
                        <div class="flex flex-col sm:flex-row gap-3">
                            <button type="submit" name="action" value="add"
                                class="flex-1 h-14 rounded-xl bg-earth-100 dark:bg-gray-700 hover:bg-earth-200 dark:hover:bg-gray-600 text-earth-800 dark:text-gray-300 font-semibold transition shadow-sm text-sm border border-earth-200 dark:border-gray-600">
                                {{ __('app.add_to_cart') }}
                            </button>
                            <button type="submit" name="action" value="buy_now"
                                class="flex-1 h-14 rounded-xl bg-leaf-600 hover:bg-leaf-700 text-white font-semibold transition shadow-md shadow-leaf-200 text-sm">
                                {{ __('app.buy_now') }}
                            </button>
                            <button type="button" id="favorite-btn" data-url="{{ route('products.favorite', $product) }}"
                                data-favorited="{{ $isFavorited ? 'true' : 'false' }}" data-login-url="{{ route('login') }}"
                                data-authenticated="{{ auth()->check() ? 'true' : 'false' }}"
                                class="favorite-btn h-14 px-6 rounded-xl border transition text-base {{ $isFavorited ? 'bg-red-50 border-red-300 text-red-500' : 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600' }}"
                                @guest title="{{ __('app.login_to_favorite') }}" @endguest>
                                <span class="favorite-icon">{{ $isFavorited ? '❤️' : '🤍' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- LOWER SECTIONS (DESCRIPTION & REVIEWS) --}}
            <div class="max-w-4xl mx-auto">
                {{-- UP NEXT: SUGGESTED PRODUCTS --}}
                <div class="mt-12 pt-6 border-t border-cream-200 dark:border-gray-700">
                    <h2 class="text-xl font-bold text-earth-900 dark:text-gray-300 mb-3">{{ __('app.related_products') }}</h2>

                    <div id="suggestions-grid" class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        @foreach ($suggestions ?? collect() as $s)
                            <div class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden shadow-sm border border-cream-200 dark:border-gray-700 hover:shadow-md transition">
                                @include('partials.product-card', ['product' => $s])
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 text-center">
                        <button id="load-more-suggestions"
                            class="px-5 py-2 text-xs font-medium rounded-xl bg-white dark:bg-gray-800 border border-cream-200 dark:border-gray-700 text-earth-800 dark:text-gray-200 hover:bg-cream-50 dark:hover:bg-gray-700 transition">Tải thêm</button>
                    </div>
                </div>

                {{-- DOWN: PRODUCT DESCRIPTION --}}
                <div class="mt-12 pt-6 border-t border-cream-200 dark:border-gray-700">
                    <h2 class="text-xl font-bold text-earth-900 dark:text-gray-300 mb-3">{{ __('app.description') }}</h2>
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-cream-100 dark:border-gray-700 text-earth-700 dark:text-gray-300 leading-relaxed text-base space-y-8 prose dark:prose-invert max-w-none">
                        <div>
                            {!! $product->description !!}
                        </div>
                    </div>
                </div>

                {{-- REVIEWS SECTION --}}
                <div class="mt-16 pt-10 border-t border-cream-200 dark:border-gray-800">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-2xl font-black text-earth-900 dark:text-gray-100">Khách hàng đánh giá</h2>
                </div>

                @php
                    $totalReviews = $product->reviews->count();
                    $avgRating = $totalReviews > 0 ? $product->reviews->avg('rating') : 0;
                    $ratingCounts = [
                        5 => $product->reviews->where('rating', 5)->count(),
                        4 => $product->reviews->where('rating', 4)->count(),
                        3 => $product->reviews->where('rating', 3)->count(),
                        2 => $product->reviews->where('rating', 2)->count(),
                        1 => $product->reviews->where('rating', 1)->count(),
                    ];
                @endphp

                @if($totalReviews > 0)
                    <div class="grid md:grid-cols-12 gap-8 mb-12 bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
                        <div class="md:col-span-4 flex flex-col items-center justify-center border-b md:border-b-0 md:border-r border-gray-100 dark:border-gray-700 pb-6 md:pb-0">
                            <div class="text-6xl font-black text-gray-900 dark:text-gray-100 mb-2">{{ number_format($avgRating, 1) }}<span class="text-3xl text-gray-400">/5</span></div>
                            <div class="flex text-yellow-400 text-2xl mb-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <span class="{{ $i <= round($avgRating) ? 'text-yellow-400' : 'text-gray-200 dark:text-gray-600' }}">★</span>
                                @endfor
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">{{ $totalReviews }} đánh giá</p>
                        </div>
                        <div class="md:col-span-8 flex flex-col justify-center space-y-3">
                            @foreach([5,4,3,2,1] as $star)
                                @php
                                    $percent = $totalReviews > 0 ? ($ratingCounts[$star] / $totalReviews) * 100 : 0;
                                @endphp
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center gap-1 w-12 text-sm font-bold text-gray-700 dark:text-gray-300">
                                        {{ $star }} <span class="text-yellow-400 text-lg">★</span>
                                    </div>
                                    <div class="flex-1 h-3 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                        <div class="h-full bg-yellow-400 rounded-full" style="width: {{ $percent }}%"></div>
                                    </div>
                                    <div class="w-8 text-right text-xs text-gray-500 dark:text-gray-400 font-medium">{{ $ratingCounts[$star] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="space-y-6">
                    @forelse ($product->reviews as $review)
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-100 dark:border-gray-700 hover:shadow-md transition duration-300">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-green-400 to-green-600 flex items-center justify-center font-bold text-white text-lg shadow-sm">
                                        {{ mb_substr($review->user->name ?? 'K', 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900 dark:text-gray-100">{{ $review->user->name ?? 'Khách hàng' }}</p>
                                        <div class="flex items-center gap-2 mt-1">
                                            <div class="flex text-yellow-400 text-sm">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <span class="{{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-200 dark:text-gray-600' }}">★</span>
                                                @endfor
                                            </div>
                                            <span class="text-xs text-gray-400 dark:text-gray-500">• {{ $review->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="hidden sm:flex items-center gap-1 text-green-600 bg-green-50 dark:bg-green-900/30 px-3 py-1 rounded-full text-xs font-medium">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                    Đã mua hàng
                                </div>
                            </div>
                            <p class="text-gray-700 dark:text-gray-300 text-base leading-relaxed mb-4">
                                {{ $review->comment }}
                            </p>
                            @if(!empty($review->attachments))
                                <div class="flex gap-3 overflow-x-auto scrollbar-hide pb-2">
                                    @foreach($review->attachments as $attachment)
                                        @php
                                            $ext = pathinfo($attachment, PATHINFO_EXTENSION);
                                        @endphp
                                        @if(in_array(strtolower($ext), ['jpg','jpeg','png','gif','webp']))
                                            <a href="{{ asset('storage/'.$attachment) }}" target="_blank" class="shrink-0 block group relative rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-600">
                                                <img src="{{ asset('storage/'.$attachment) }}" class="w-24 h-24 object-cover group-hover:scale-110 transition duration-300">
                                            </a>
                                        @elseif(in_array(strtolower($ext), ['mp4','mov','avi']))
                                            <div class="shrink-0 relative rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-600 w-32 h-24 bg-black flex items-center justify-center">
                                                <video src="{{ asset('storage/'.$attachment) }}" class="w-full h-full object-cover opacity-80"></video>
                                                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                                    <div class="w-8 h-8 bg-white/30 backdrop-blur-sm rounded-full flex items-center justify-center">▶</div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-3xl p-12 text-center border border-gray-100 dark:border-gray-700 border-dashed">
                            <div class="text-5xl mb-4 opacity-50">🌟</div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">Chưa có đánh giá</h3>
                            <p class="text-gray-500 dark:text-gray-400">Trở thành người đầu tiên đánh giá sản phẩm này sau khi mua hàng nhé.</p>
                        </div>
                    @endforelse
                </div>
            </div>
            </div> {{-- END OF max-w-4xl --}}

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const main = document.getElementById('product-main-image');
            const thumbs = document.querySelectorAll('.thumb-item');
            const variantRadios = document.querySelectorAll('.variant-radio');

            // Đường dẫn ảnh đại diện gốc mặc định của sản phẩm
            const defaultProductImage = "{{ MediaHelper::productImage($product->thumbnail, $product->id) }}";

            // Bản đồ dữ liệu: Gộp cả Ảnh, Giá bán mới và Giá gốc của từng biến thể vào đây
            const variantMap = {
                @foreach ($product->variants as $v)
                    "{{ $v->id }}": {
                        image: {!! $v->image ? '"' . asset('storage/' . $v->image) . '"' : 'null' !!},
                        price: "{{ number_format($v->effectivePrice()) }}đ",
                        oldPrice: {!! ($v->effectivePrice() < $v->price) ? '"' . number_format($v->price) . 'đ"' : 'null' !!},
                        isFlash: {{ $v->isFlashActive() ? 'true' : 'false' }},
                        flashEnds: {!! $v->isFlashActive() && $v->flash_sale_ends_at ? '"' . $v->flash_sale_ends_at->toIso8601String() . '"' : 'null' !!},
                        flashSold: {{ $v->flash_sale_sold ?? 0 }},
                        flashStock: {{ $v->flash_sale_stock ?? 'null' }}
                    },
                @endforeach
            };

            // Hàm cập nhật trạng thái viền xanh của hàng Gallery ảnh nhỏ phía dưới
            function updateActiveThumb(src) {
                thumbs.forEach(x => x.classList.remove('border-green-500'));
                thumbs.forEach(thumb => {
                    if (thumb.dataset.src === src) {
                        thumb.classList.add('border-green-500');
                    }
                });
            }

            // Xử lý click chọn ảnh nhỏ (Gallery)
            thumbs.forEach(item => {
                item.addEventListener('click', function () {
                    thumbs.forEach(x => x.classList.remove('border-green-500'));
                    this.classList.add('border-green-500');
                    main.src = this.dataset.src;
                });
            });

            // Xử lý bấm đổi qua lại giữa các Biến Thể liên tục (Đổi mượt cả Ảnh + Giá cùng lúc)
            variantRadios.forEach(radio => {
                radio.addEventListener('click', function () {
                    const variantId = String(this.value);
                    const variantData = variantMap[variantId];

                    if (variantData) {
                        // 1. Logic Thay đổi hình ảnh chính xác
                        if (variantData.image && variantData.image !== 'null') {
                            main.src = variantData.image;
                            updateActiveThumb(variantData.image);
                        } else {
                            // Nếu biến thể không có ảnh riêng, lùi về ảnh mặc định của sản phẩm thay vì giữ ảnh cũ
                            main.src = defaultProductImage;
                            updateActiveThumb(defaultProductImage);
                        }

                        // 2. Tự động cập nhật hiển thị Giá tiền sale_price & price
                        const priceEl = document.getElementById('product-display-price');
                        const oldPriceEl = document.getElementById('product-display-old-price');

                        if (priceEl) priceEl.textContent = variantData.price;
                        if (oldPriceEl) {
                            if (variantData.oldPrice) {
                                oldPriceEl.textContent = variantData.oldPrice;
                                oldPriceEl.style.display = 'inline';
                            } else {
                                oldPriceEl.style.display = 'none';
                            }
                        }

                        // 3. Hiển thị / ẩn Flash Sale module
                        const flashModule = document.getElementById('flash-sale-module');
                        if (flashModule) {
                            if (variantData.isFlash) {
                                flashModule.style.display = 'flex';
                                startCountdown(variantData.flashEnds);
                                
                                // Update progress bar
                                const pBar = document.getElementById('flash-progress-bar');
                                const pText = document.getElementById('flash-progress-text');
                                if (pBar && pText) {
                                    if (variantData.flashStock > 0) {
                                        const pct = Math.min(100, Math.round((variantData.flashSold / variantData.flashStock) * 100));
                                        pBar.style.width = pct + '%';
                                        pText.textContent = `Đã bán ${variantData.flashSold}/${variantData.flashStock}`;
                                    } else {
                                        pBar.style.width = '0%';
                                        pText.textContent = 'Đang bán chạy';
                                    }
                                }
                            } else {
                                flashModule.style.display = 'none';
                                stopCountdown();
                            }
                        }
                    }
                });
            });

            // Countdown Logic
            let countdownInterval = null;
            function stopCountdown() {
                if (countdownInterval) clearInterval(countdownInterval);
            }

            function startCountdown(endTimeString) {
                stopCountdown();
                if (!endTimeString) return;

                const endsAt = new Date(endTimeString).getTime();
                
                const updateTimer = () => {
                    const now = new Date().getTime();
                    const distance = endsAt - now;

                    if (distance < 0) {
                        stopCountdown();
                        // Flash sale ended, reload to reflect new price
                        window.location.reload();
                        return;
                    }

                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    const cdDays = document.querySelector('#flash-countdown .cd-days');
                    const cdHours = document.querySelector('#flash-countdown .cd-hours');
                    const cdMins = document.querySelector('#flash-countdown .cd-mins');
                    const cdSecs = document.querySelector('#flash-countdown .cd-secs');

                    if(cdDays) cdDays.textContent = String(days).padStart(2, '0');
                    if(cdHours) cdHours.textContent = String(hours).padStart(2, '0');
                    if(cdMins) cdMins.textContent = String(minutes).padStart(2, '0');
                    if(cdSecs) cdSecs.textContent = String(seconds).padStart(2, '0');
                };

                updateTimer();
                countdownInterval = setInterval(updateTimer, 1000);
            }

            // Init countdown for default variant on load
            const checkedRadio = document.querySelector('.variant-radio:checked');
            if (checkedRadio) {
                const initVariantData = variantMap[checkedRadio.value];
                if (initVariantData && initVariantData.isFlash) {
                    startCountdown(initVariantData.flashEnds);
                    
                    // Init progress bar
                    const pBar = document.getElementById('flash-progress-bar');
                    const pText = document.getElementById('flash-progress-text');
                    if (pBar && pText) {
                        if (initVariantData.flashStock > 0) {
                            const pct = Math.min(100, Math.round((initVariantData.flashSold / initVariantData.flashStock) * 100));
                            pBar.style.width = pct + '%';
                            pText.textContent = `Đã bán ${initVariantData.flashSold}/${initVariantData.flashStock}`;
                        } else {
                            pBar.style.width = '0%';
                            pText.textContent = 'Đang bán chạy';
                        }
                    }
                }
            }

            // Tăng giảm số lượng sản phẩm
            const qtyInput = document.getElementById('quantity');
            const btnIncr = document.getElementById('qty-incr');
            const btnDecr = document.getElementById('qty-decr');

            if (btnIncr) {
                btnIncr.addEventListener('click', () => {
                    qtyInput.value = parseInt(qtyInput.value || 1) + 1;
                });
            }
            if (btnDecr) {
                btnDecr.addEventListener('click', () => {
                    qtyInput.value = Math.max(1, parseInt(qtyInput.value || 1) - 1);
                });
            }
        });
    </script>

    <script>
        (function () {
            const btn = document.getElementById('load-more-suggestions');
            const grid = document.getElementById('suggestions-grid');
            let offset = {{ ($suggestions ?? collect())->count() }};
            const url = '{{ route('products.suggestions', $product->slug) }}';

            if (btn) {
                btn.addEventListener('click', async () => {
                    btn.disabled = true;
                    btn.textContent = 'Đang tải...';
                    try {
                        const resp = await fetch(url + '?offset=' + offset + '&limit=8');
                        const data = await resp.json();
                        if (data.html) {
                            grid.insertAdjacentHTML('beforeend', data.html);
                            // Cập nhật countdown sau khi thêm các phần tử html nếu có flash sale
                            if (typeof updateCountdowns === 'function') updateCountdowns();
                        }
                        offset += data.count;
                        if (data.count < 8) {
                            btn.style.display = 'none';
                        }
                    } catch (e) {
                        console.error(e);
                    } finally {
                        btn.disabled = false;
                        btn.textContent = 'Tải thêm';
                    }
                });
            }
        })();
    </script>
@endsection
