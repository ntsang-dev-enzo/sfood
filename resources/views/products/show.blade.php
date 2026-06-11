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
                                {{ number_format($firstVariant->sale_price ?? $firstVariant->price) }}đ
                            </span>

                            <span class="text-gray-400 line-through text-base" id="product-display-old-price"
                                style="display: {{ (!empty($firstVariant->sale_price) && $firstVariant->sale_price < $firstVariant->price) ? 'inline' : 'none' }}">
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
                                            class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 peer-checked:border-green-500 peer-checked:bg-green-50 dark:peer-checked:bg-green-900/30 transition">
                                            <div class="font-medium text-sm text-gray-900 dark:text-gray-300">{{ $variant->name }}</div>
                                            <div class="text-xs text-green-600 dark:text-green-400 mt-0.5">
                                                {{ number_format($variant->sale_price ?? $variant->price) }}đ
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

            {{-- UP NEXT: SUGGESTED PRODUCTS --}}
            <div class="mt-12 pt-6 border-t border-cream-200 dark:border-gray-700">
                <h2 class="text-xl font-bold text-earth-900 dark:text-gray-300 mb-3">{{ __('app.related_products') }}</h2>

                <div id="suggestions-grid" class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    @foreach ($suggestions ?? collect() as $s)
                        <a href="{{ route('products.show', $s->slug) }}"
                            class="block bg-white dark:bg-gray-800 rounded-xl p-3 border border-cream-200 dark:border-gray-700 hover:shadow-sm transition">
                            <img src="{{ MediaHelper::productImage($s->thumbnail, $s->id) }}"
                                class="w-full h-32 object-cover rounded-lg">
                            <div class="mt-2 text-xs font-medium text-earth-900 dark:text-gray-300 line-clamp-2 min-h-[2rem]">{{ $s->name }}</div>
                        </a>
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
            </div>

            {{-- REVIEWS SECTION --}}
            <div class="mt-12 pt-6 border-t border-cream-200 dark:border-gray-700">
                <h2 class="text-xl font-bold text-earth-900 dark:text-gray-300 mb-6">Đánh giá sản phẩm</h2>
                <div class="space-y-6">
                    @forelse ($product->reviews as $review)
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-cream-100 dark:border-gray-700">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-cream-200 dark:bg-gray-700 flex items-center justify-center font-bold text-earth-800 dark:text-gray-200">
                                        {{ substr($review->user->name ?? 'U', 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-earth-900 dark:text-gray-300">{{ $review->user->name ?? 'Khách hàng' }}</p>
                                        <p class="text-xs text-earth-500 dark:text-gray-400">{{ $review->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>
                                <div class="flex text-yellow-400">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="{{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }}">★</span>
                                    @endfor
                                </div>
                            </div>
                            <p class="text-earth-700 dark:text-gray-300 text-sm mb-4">
                                {{ $review->comment }}
                            </p>
                            @if(!empty($review->attachments))
                                <div class="flex gap-2 overflow-x-auto scrollbar-hide">
                                    @foreach($review->attachments as $attachment)
                                        @php
                                            $ext = pathinfo($attachment, PATHINFO_EXTENSION);
                                        @endphp
                                        @if(in_array(strtolower($ext), ['jpg','jpeg','png','gif','webp']))
                                            <a href="{{ asset('storage/'.$attachment) }}" target="_blank" class="shrink-0">
                                                <img src="{{ asset('storage/'.$attachment) }}" class="w-20 h-20 object-cover rounded-xl border border-cream-200 dark:border-gray-600">
                                            </a>
                                        @elseif(in_array(strtolower($ext), ['mp4','mov','avi']))
                                            <video src="{{ asset('storage/'.$attachment) }}" class="w-32 h-20 object-cover rounded-xl border border-cream-200 dark:border-gray-600" controls></video>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-earth-500 dark:text-gray-400 text-center py-8">Chưa có đánh giá nào cho sản phẩm này.</p>
                    @endforelse
                </div>
            </div>

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
                        price: "{{ number_format($v->sale_price ?? $v->price) }}đ",
                        oldPrice: {!! (!empty($v->sale_price) && $v->sale_price < $v->price) ? '"' . number_format($v->price) . 'đ"' : 'null' !!}
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
                    }
                });
            });

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
                        (data.data || []).forEach(s => {
                            const a = document.createElement('a');
                            a.href = '/san-pham/' + s.slug;
                            a.className = 'block bg-white dark:bg-gray-800 rounded-xl p-3 border border-cream-200 dark:border-gray-700 hover:shadow-sm transition';
                            a.innerHTML = `<img src="/storage/${s.thumbnail}" class="w-full h-32 object-cover rounded-lg"><div class="mt-2 text-xs font-medium text-earth-900 dark:text-gray-300 line-clamp-2 min-h-[2rem]">${s.name}</div>`;
                            grid.appendChild(a);
                        });
                        offset += (data.data || []).length;
                        if ((data.data || []).length < 8) {
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
