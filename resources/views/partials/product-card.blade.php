@php use App\Support\MediaHelper; @endphp
<div class="card card-product h-100 border-0 shadow-sm position-relative overflow-hidden">
    <a href="{{ route('products.show', $product) }}" class="text-decoration-none text-dark d-flex flex-column h-100">
        {{-- Ảnh sản phẩm & Badge --}}
        <div class="position-relative overflow-hidden bg-light" style="aspect-ratio: 1/1;">
            <img src="{{ MediaHelper::productImage($product->getFilteredImage(request('min_price'), request('max_price')), $product->id) }}" 
                 alt="{{ $product->name }}"
                 class="card-img-top w-100 h-100 object-fit-cover transition">
            
            @if ($product->category)
                <span class="position-absolute top-0 start-0 m-1 m-md-2 badge bg-success shadow-sm d-none d-sm-inline-block" style="font-size: 0.65rem;">
                    {{ $product->category->name }}
                </span>
            @endif

            @php
                $isNew = $product->is_new;
                $isHot = $product->is_hot;
                $hasFlashSale = $product->variants->contains(function ($v) { return $v->isFlashActive(); });
            @endphp
            <div class="position-absolute top-0 end-0 m-1 m-md-2 d-flex flex-column gap-1 align-items-end">
                @if($isHot)
                    <span class="badge bg-danger shadow-sm p-1 px-1.5" style="font-size: 0.62rem;"><i class="bi bi-fire me-0.5"></i>Hot</span>
                @endif
                @if($isNew)
                    <span class="badge bg-primary shadow-sm p-1 px-1.5" style="font-size: 0.62rem;"><i class="bi bi-stars me-0.5"></i>Mới</span>
                @endif
                @if($hasFlashSale)
                    @php
                        $flashVariant = $product->variants->first(function ($v) { return $v->isFlashActive(); });
                    @endphp
                    <span class="badge bg-warning text-dark shadow-sm p-1 px-1.5" style="font-size: 0.62rem;" data-countdown="{{ $flashVariant->flash_sale_ends_at->toIso8601String() }}">
                        <i class="bi bi-lightning-fill"></i> <span class="countdown-timer fw-bold"></span>
                    </span>
                @endif
            </div>
        </div>

        {{-- Thân Card --}}
        <div class="card-body p-2 p-md-3 d-flex flex-column flex-grow-1">
            <h6 class="card-title fw-bold text-dark mb-1 mb-md-2 text-truncate-2 product-card-title" 
                style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.25;">
                {{ $product->name }}
            </h6>

            @php
                $avgRating = $product->reviews_avg_rating ?? $product->averageRating();
                $reviewCount = $product->reviews_count ?? $product->reviews()->count();
            @endphp
            <div class="small mb-1 mb-md-2 d-flex align-items-center" style="font-size: 0.72rem; min-height: 1.1rem;">
                @if($reviewCount > 0)
                    <span class="text-warning"><i class="bi bi-star-fill"></i> {{ number_format($avgRating, 1) }}</span>
                    <span class="text-muted ms-1">({{ $reviewCount }})</span>
                @else
                    <span class="text-muted text-truncate"><i class="bi bi-check2-circle text-success me-1"></i>VietGAP</span>
                @endif
            </div>

            <div class="mt-auto pt-1 pt-md-2 border-top d-flex justify-content-between align-items-center">
                <span class="fw-bold text-danger product-card-price">
                    {{ number_format($product->minPrice(request('min_price'), request('max_price'))) }}₫
                </span>
                <span class="btn btn-sm btn-outline-success rounded-pill px-2 py-0.5 small d-none d-sm-inline" style="font-size: 0.72rem;">
                    Xem
                </span>
                <span class="btn btn-sm btn-outline-success rounded-circle d-inline-flex d-sm-none align-items-center justify-content-center p-0" style="width: 22px; height: 22px; font-size: 0.65rem;">
                    <i class="bi bi-chevron-right"></i>
                </span>
            </div>
        </div>
    </a>
</div>
