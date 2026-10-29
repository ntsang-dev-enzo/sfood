@extends('layouts.app')

@section('title', $product->name . ' - SFood')

@section('content')
@php use App\Support\MediaHelper; @endphp

<div class="bg-body-tertiary py-4 min-vh-100">
    <div class="container py-2">

        {{-- Breadcrumb (Bootstrap 5) --}}
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb bg-white p-3 rounded-3 shadow-sm border mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-success"><i class="bi bi-house-door me-1"></i>{{ __('app.home_title') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-decoration-none text-success">{{ __('app.products') }}</a></li>
                @if($product->category)
                    <li class="breadcrumb-item"><a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="text-decoration-none text-secondary">{{ $product->category->name }}</a></li>
                @endif
                <li class="breadcrumb-item active text-truncate fw-semibold" aria-current="page" style="max-width: 300px;">{{ $product->name }}</li>
            </ol>
        </nav>

        {{-- Main Product Section --}}
        <div class="row g-4 align-items-start mb-5">

            {{-- LEFT: Images (Sticky Gallery) --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white p-3 p-md-4 position-sticky" style="top: 90px;">
                    {{-- MAIN IMAGE --}}
                    <div class="ratio ratio-1x1 bg-light rounded-4 overflow-hidden border d-flex align-items-center justify-content-center p-3 mb-3">
                        <img id="product-main-image"
                             src="{{ MediaHelper::productImage($product->thumbnail, $product->id) }}"
                             alt="{{ $product->name }}" 
                             class="w-100 h-100 object-fit-contain transition">
                    </div>

                    {{-- GALLERY THUMBNAILS --}}
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

                    @if($gallery->count() > 1)
                        <div class="border-top pt-3">
                            <div id="thumbs-wrap" class="d-flex gap-2 overflow-x-auto pb-1" style="scrollbar-width: thin;">
                                @foreach ($gallery as $idx => $path)
                                    <button type="button"
                                        class="thumb-item btn p-0 border-2 rounded-3 overflow-hidden flex-shrink-0 {{ $idx === 0 ? 'border-success' : 'border-light-subtle' }}"
                                        data-src="{{ asset('storage/' . $path) }}"
                                        style="width: 70px; height: 70px;">
                                        <img src="{{ asset('storage/' . $path) }}" class="w-100 h-100 object-fit-cover" alt="Thumbnail">
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- RIGHT: Purchase Information --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                    {{-- CATEGORY BADGE & STOCK STATUS --}}
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        @if($product->category)
                            <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" 
                               class="badge bg-success-subtle text-success text-decoration-none px-3 py-2 rounded-pill fw-semibold">
                                <i class="bi bi-tag-fill me-1"></i>{{ $product->category->name }}
                            </a>
                        @endif
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1.5 small">
                            <i class="bi bi-shield-check me-1"></i>Chính hãng 100%
                        </span>
                    </div>

                    {{-- TITLE --}}
                    <h1 class="fw-bold text-dark fs-2 lh-sm mb-3">
                        {{ $product->name }}
                    </h1>

                    {{-- AVERAGE RATING & REVIEWS SUMMARY --}}
                    @php
                        $avgRating = collect($product->reviews)->avg('rating');
                        $reviewCount = collect($product->reviews)->count();
                    @endphp
                    <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                        @if($reviewCount > 0)
                            <div class="d-flex align-items-center text-warning fs-6">
                                <span class="fw-bold text-dark me-1">{{ number_format($avgRating, 1) }}</span>
                                <i class="bi bi-star-fill text-warning me-1"></i>
                                <span class="text-secondary small">({{ $reviewCount }} đánh giá)</span>
                            </div>
                            <span class="text-muted">|</span>
                        @endif
                        <span class="text-muted small"><i class="bi bi-truck me-1 text-success"></i>Giao nhanh trong 2h</span>
                        <span class="text-muted">|</span>
                        <span class="text-muted small"><i class="bi bi-arrow-repeat me-1 text-success"></i>Đổi trả miễn phí</span>
                    </div>

                    {{-- PRICE --}}
                    @php
                        $firstVariant = $product->variants->where('is_active', true)->firstWhere('id', $defaultVariantId)
                            ?? $product->variants->where('is_active', true)->first();
                    @endphp

                    <div class="p-3 bg-light rounded-3 mb-4">
                        <div class="d-flex align-items-baseline gap-3">
                            @if ($firstVariant)
                                <span class="fs-1 fw-bold text-danger" id="product-display-price">
                                    {{ number_format($firstVariant->effectivePrice()) }}đ
                                </span>
                                <span class="text-muted text-decoration-line-through fs-5" id="product-display-old-price"
                                    style="display: {{ ($firstVariant->effectivePrice() < $firstVariant->price) ? 'inline' : 'none' }}">
                                    {{ number_format($firstVariant->price) }}đ
                                </span>
                            @else
                                <span class="fs-1 fw-bold text-danger" id="product-display-price">
                                    {{ number_format($product->minPrice()) }}đ
                                </span>
                                <span class="text-muted text-decoration-line-through fs-5" id="product-display-old-price" style="display: none;"></span>
                            @endif
                        </div>
                    </div>

                    {{-- FLASH SALE MODULE --}}
                    <div id="flash-sale-module" class="rounded-4 text-white shadow-sm mb-4 overflow-hidden" 
                         style="background: linear-gradient(135deg, #e63946 0%, #d90429 100%); display: {{ $firstVariant && $firstVariant->isFlashActive() ? 'block' : 'none' }};">
                        <div class="p-3 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-lightning-charge-fill text-warning fs-3"></i>
                                <div>
                                    <h5 class="fw-black mb-0 fst-italic tracking-wider text-white">FLASHSALE</h5>
                                    <small class="text-white-50">Giá sốc số lượng có hạn</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="text-uppercase small fw-bold d-block text-warning mb-1">Kết thúc sau</span>
                                <div class="d-inline-flex gap-1 fw-bold font-monospace" id="flash-countdown">
                                    <span class="badge bg-dark bg-opacity-50 px-2 py-1 cd-days">00</span>:
                                    <span class="badge bg-dark bg-opacity-50 px-2 py-1 cd-hours">00</span>:
                                    <span class="badge bg-dark bg-opacity-50 px-2 py-1 cd-mins">00</span>:
                                    <span class="badge bg-dark bg-opacity-50 px-2 py-1 cd-secs">00</span>
                                </div>
                            </div>
                        </div>
                        <div class="px-3 pb-3">
                            <div class="progress position-relative" style="height: 20px; border-radius: 10px; background-color: rgba(0,0,0,0.3);">
                                <div id="flash-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar" style="width: 0%"></div>
                                <div id="flash-progress-text" class="position-absolute w-100 h-100 d-flex align-items-center justify-content-center text-white small fw-bold" style="top: 0; left: 0;">
                                    Đang bán chạy
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SHORT DESCRIPTION --}}
                    @if($product->short_description)
                        <div class="p-3 bg-light rounded-3 border-start border-4 border-success text-secondary small mb-4">
                            {{ $product->short_description }}
                        </div>
                    @endif

                    {{-- FORM PURCHASE --}}
                    <form method="POST" action="{{ route('products.add-to-cart', $product) }}">
                        @csrf

                        {{-- VARIANTS --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-box-seam text-success"></i> {{ __('app.choose_variant') }}:
                            </label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($product->variants->where('is_active', true) as $variant)
                                    <div class="position-relative">
                                        <input type="radio" 
                                               name="variant_id" 
                                               id="variant-{{ $variant->id }}" 
                                               value="{{ $variant->id }}"
                                               class="btn-check variant-radio" 
                                               @checked(old('variant_id', $defaultVariantId) == $variant->id)>
                                        <label class="btn btn-outline-success text-start rounded-3 p-2 px-3 position-relative" 
                                               for="variant-{{ $variant->id }}" style="min-width: 140px;">
                                            @if($variant->isFlashActive())
                                                <span class="position-absolute top-0 end-0 badge bg-danger rounded-pill" style="transform: translate(20%, -30%); font-size: 0.65rem;"><i class="bi bi-lightning-fill"></i> SALE</span>
                                            @endif
                                            <div class="fw-bold small">{{ $variant->name }}</div>
                                            <div class="small fw-semibold text-danger">{{ number_format($variant->effectivePrice()) }}đ</div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- QUANTITY --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-plus-slash-minus text-success"></i> {{ __('app.quantity') }}:
                            </label>
                            <div class="input-group" style="width: 150px;">
                                <button type="button" id="qty-decr" class="btn btn-outline-secondary px-3">−</button>
                                <input id="quantity" type="number" name="quantity" value="1" min="1" class="form-control text-center fw-bold">
                                <button type="button" id="qty-incr" class="btn btn-outline-secondary px-3">+</button>
                            </div>
                        </div>

                        {{-- ACTION BUTTONS --}}
                        <div class="row g-2 pt-2">
                            <div class="col-sm-5">
                                <button type="submit" name="action" value="add"
                                    class="btn btn-outline-success w-100 py-3 fw-bold rounded-pill d-flex align-items-center justify-content-center gap-2 shadow-sm">
                                    <i class="bi bi-cart-plus fs-5"></i>
                                    <span>{{ __('app.add_to_cart') }}</span>
                                </button>
                            </div>
                            <div class="col-sm-5">
                                <button type="submit" name="action" value="buy_now"
                                    class="btn btn-success w-100 py-3 fw-bold rounded-pill d-flex align-items-center justify-content-center gap-2 shadow-sm">
                                    <i class="bi bi-lightning-charge-fill"></i>
                                    <span>{{ __('app.buy_now') }}</span>
                                </button>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" id="favorite-btn" 
                                    data-url="{{ route('products.favorite', $product) }}"
                                    data-favorited="{{ $isFavorited ? 'true' : 'false' }}" 
                                    data-login-url="{{ route('login') }}"
                                    data-authenticated="{{ auth()->check() ? 'true' : 'false' }}"
                                    class="btn {{ $isFavorited ? 'btn-danger' : 'btn-outline-danger' }} w-100 py-3 rounded-pill d-flex align-items-center justify-content-center favorite-btn"
                                    @guest title="{{ __('app.login_to_favorite') }}" @endguest>
                                    <span class="favorite-icon fs-5"><i class="bi {{ $isFavorited ? 'bi-heart-fill' : 'bi-heart' }}"></i></span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- SUGGESTED PRODUCTS (3 sản phẩm 1 hàng trên điện thoại) --}}
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold text-dark fs-4 mb-0">
                    <i class="bi bi-stars text-warning me-2"></i>{{ __('app.related_products') }}
                </h3>
            </div>

            <div id="suggestions-grid" class="row row-cols-3 row-cols-md-3 row-cols-lg-4 g-2 g-md-3">
                @foreach ($suggestions ?? collect() as $s)
                    <div class="col">
                        @include('partials.product-card', ['product' => $s])
                    </div>
                @endforeach
            </div>

            <div class="mt-4 text-center">
                <button id="load-more-suggestions" class="btn btn-outline-success rounded-pill px-4 py-2 fw-semibold">
                    <i class="bi bi-arrow-down-circle me-1"></i> Tải thêm sản phẩm liên quan
                </button>
            </div>
        </div>

        {{-- PRODUCT DETAILED DESCRIPTION --}}
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-md-5 mb-4">
            <h3 class="fw-bold text-dark fs-4 mb-4 pb-3 border-bottom">
                <i class="bi bi-file-text text-success me-2"></i>{{ __('app.description') }}
            </h3>
            <div class="lh-lg text-secondary product-content">
                {!! $product->description !!}
            </div>
        </div>

        {{-- CUSTOMER REVIEWS SECTION --}}
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-md-5">
            <h3 class="fw-bold text-dark fs-4 mb-4 pb-3 border-bottom d-flex align-items-center justify-content-between">
                <span><i class="bi bi-chat-heart text-danger me-2"></i>Khách hàng đánh giá</span>
                <span class="badge bg-secondary-subtle text-secondary fs-6 rounded-pill px-3 py-1">{{ $product->reviews->count() }} phản hồi</span>
            </h3>

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
                <div class="card bg-light border-0 rounded-4 p-4 mb-4">
                    <div class="row align-items-center g-4">
                        <div class="col-md-4 text-center border-end-md">
                            <div class="display-3 fw-bold text-dark">{{ number_format($avgRating, 1) }}<span class="fs-4 text-muted">/5</span></div>
                            <div class="text-warning fs-4 mb-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi {{ $i <= round($avgRating) ? 'bi-star-fill' : 'bi-star' }}"></i>
                                @endfor
                            </div>
                            <p class="text-muted small mb-0">{{ $totalReviews }} khách hàng đã đánh giá</p>
                        </div>
                        <div class="col-md-8">
                            <div class="d-flex flex-column gap-2">
                                @foreach([5,4,3,2,1] as $star)
                                    @php
                                        $percent = $totalReviews > 0 ? ($ratingCounts[$star] / $totalReviews) * 100 : 0;
                                    @endphp
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="fw-bold text-secondary small" style="width: 50px;">{{ $star }} <i class="bi bi-star-fill text-warning"></i></div>
                                        <div class="progress flex-grow-1" style="height: 10px;">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $percent }}%"></div>
                                        </div>
                                        <div class="text-muted small text-end" style="width: 40px;">{{ $ratingCounts[$star] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="d-flex flex-column gap-4">
                @forelse ($product->reviews as $review)
                    <div class="border-bottom pb-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold fs-5 shadow-sm" style="width: 48px; height: 48px;">
                                    {{ mb_substr($review->user->name ?? 'K', 0, 1) }}
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">{{ $review->user->name ?? 'Khách hàng SFood' }}</h6>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <div class="text-warning small">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="bi {{ $i <= $review->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                            @endfor
                                        </div>
                                        <span class="text-muted small">• {{ $review->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                            <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1 small">
                                <i class="bi bi-patch-check-fill me-1"></i>Đã mua hàng
                            </span>
                        </div>
                        <p class="text-secondary lh-base mb-3 ps-md-5 ms-md-2">
                            {{ $review->comment }}
                        </p>
                        @if(!empty($review->attachments))
                            <div class="d-flex gap-2 overflow-x-auto ps-md-5 ms-md-2">
                                @foreach($review->attachments as $attachment)
                                    @php
                                        $ext = pathinfo($attachment, PATHINFO_EXTENSION);
                                    @endphp
                                    @if(in_array(strtolower($ext), ['jpg','jpeg','png','gif','webp']))
                                        <a href="{{ asset('storage/'.$attachment) }}" target="_blank" class="d-block border rounded-3 overflow-hidden shadow-sm" style="width: 80px; height: 80px;">
                                            <img src="{{ asset('storage/'.$attachment) }}" class="w-100 h-100 object-fit-cover" alt="Review photo">
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-chat-square-text display-3 opacity-25"></i>
                        <h5 class="fw-bold mt-3 mb-1">Chưa có đánh giá nào</h5>
                        <p class="small">Hãy là người đầu tiên trải nghiệm và chia sẻ nhận xét về sản phẩm này nhé!</p>
                    </div>
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

        const defaultProductImage = "{{ MediaHelper::productImage($product->thumbnail, $product->id) }}";

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

        function updateActiveThumb(src) {
            thumbs.forEach(x => {
                x.classList.remove('border-success');
                x.classList.add('border-light-subtle');
            });
            thumbs.forEach(thumb => {
                if (thumb.dataset.src === src) {
                    thumb.classList.add('border-success');
                    thumb.classList.remove('border-light-subtle');
                }
            });
        }

        thumbs.forEach(item => {
            item.addEventListener('click', function () {
                thumbs.forEach(x => {
                    x.classList.remove('border-success');
                    x.classList.add('border-light-subtle');
                });
                this.classList.add('border-success');
                this.classList.remove('border-light-subtle');
                main.src = this.dataset.src;
            });
        });

        variantRadios.forEach(radio => {
            radio.addEventListener('change', function () {
                const variantId = String(this.value);
                const variantData = variantMap[variantId];

                if (variantData) {
                    if (variantData.image && variantData.image !== 'null') {
                        main.src = variantData.image;
                        updateActiveThumb(variantData.image);
                    } else {
                        main.src = defaultProductImage;
                        updateActiveThumb(defaultProductImage);
                    }

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

                    const flashModule = document.getElementById('flash-sale-module');
                    if (flashModule) {
                        if (variantData.isFlash) {
                            flashModule.style.display = 'block';
                            startCountdown(variantData.flashEnds);
                            
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

        const checkedRadio = document.querySelector('.variant-radio:checked');
        if (checkedRadio) {
            const initVariantData = variantMap[checkedRadio.value];
            if (initVariantData && initVariantData.isFlash) {
                startCountdown(initVariantData.flashEnds);
                
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
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang tải...';
                try {
                    const resp = await fetch(url + '?offset=' + offset + '&limit=8');
                    const data = await resp.json();
                    if (data.html) {
                        grid.insertAdjacentHTML('beforeend', data.html);
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
                    btn.innerHTML = '<i class="bi bi-arrow-down-circle me-1"></i> Tải thêm sản phẩm liên quan';
                }
            });
        }
    })();
</script>
@endsection
