@php 
    use App\Support\MediaHelper; 
    $activeVariants = $product->variants ? $product->variants->where('is_active', true)->values() : collect();
    $variantsCount = $activeVariants->count();
    $firstVariant = $activeVariants->first();
    $hasMultipleVariants = $variantsCount > 1;
    $variantsData = $hasMultipleVariants ? $activeVariants->map(function ($v) {
        return [
            'id' => $v->id,
            'name' => $v->name,
            'price' => $v->effectivePrice(),
            'price_formatted' => number_format($v->effectivePrice()) . '₫',
            'stock' => $v->stock,
            'in_stock' => $v->inStock(),
        ];
    })->values() : [];
@endphp
<div class="card card-product h-100 border-0 shadow-sm position-relative overflow-hidden">
    {{-- Ảnh sản phẩm & Badge (Link đến trang chi tiết) --}}
    <a href="{{ route('products.show', $product) }}" class="position-relative overflow-hidden bg-light d-block text-decoration-none" style="aspect-ratio: 1/1;">
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
            $hasFlashSale = $product->variants ? $product->variants->contains(function ($v) { return $v->isFlashActive(); }) : false;
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
    </a>

    {{-- Thân Card --}}
    <div class="card-body p-2 p-md-3 d-flex flex-column flex-grow-1">
        <a href="{{ route('products.show', $product) }}" class="text-decoration-none text-dark d-block">
            <h6 class="card-title fw-bold text-dark mb-1 mb-md-2 text-truncate-2 product-card-title" 
                style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.25;">
                {{ $product->name }}
            </h6>
        </a>

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

        {{-- Giá & Nút Thêm Giỏ Hàng --}}
        <div class="mt-auto pt-1 pt-md-2 border-top d-flex justify-content-between align-items-center">
            <span class="fw-bold text-danger product-card-price">
                {{ number_format($product->minPrice(request('min_price'), request('max_price'))) }}₫
            </span>

            @if($variantsCount === 0 || ($firstVariant && !$firstVariant->inStock()))
                <button type="button" class="btn btn-sm btn-light border text-muted rounded-pill px-2 py-0.5 small disabled" style="font-size: 0.72rem;">
                    Hết hàng
                </button>
            @elseif($hasMultipleVariants)
                <button type="button" 
                        class="btn btn-sm btn-success rounded-circle d-flex align-items-center justify-content-center p-0 shadow-sm btn-quick-cart" 
                        style="width: 30px; height: 30px; transition: transform 0.2s ease;" 
                        title="Chọn phân loại & thêm vào giỏ"
                        data-action="open-variant-modal"
                        data-product-name="{{ $product->name }}"
                        data-product-img="{{ MediaHelper::productImage($product->thumbnail, $product->id) }}"
                        data-add-url="{{ route('products.add-to-cart', $product) }}"
                        data-variants="{{ json_encode($variantsData) }}">
                    <i class="bi bi-cart-plus fs-6"></i>
                </button>
            @else
                <button type="button" 
                        class="btn btn-sm btn-success rounded-circle d-flex align-items-center justify-content-center p-0 shadow-sm btn-quick-cart" 
                        style="width: 30px; height: 30px; transition: transform 0.2s ease;" 
                        title="Thêm vào giỏ hàng"
                        data-action="quick-add"
                        data-product-name="{{ $product->name }}"
                        data-variant-id="{{ $firstVariant->id }}"
                        data-add-url="{{ route('products.add-to-cart', $product) }}">
                    <i class="bi bi-cart-plus fs-6"></i>
                </button>
            @endif
        </div>
    </div>
</div>
