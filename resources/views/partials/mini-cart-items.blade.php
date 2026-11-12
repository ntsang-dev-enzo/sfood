@if(!isset($miniCartItems) || $miniCartItems->isEmpty())
    <div class="text-center py-4 text-muted" id="mini-cart-empty-msg">
        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center p-3 mb-2" style="width: 60px; height: 60px;">
            <i class="bi bi-cart-x fs-3 text-secondary opacity-75"></i>
        </div>
        <div class="fw-semibold small text-dark">Giỏ hàng của bạn đang trống</div>
        <small class="text-muted d-block" style="font-size: 0.75rem;">Hãy thêm sản phẩm tươi ngon vào giỏ nhé!</small>
    </div>
@else
    <div class="d-flex flex-column gap-2 py-1">
        @foreach($miniCartItems as $item)
            @php
                $variant = $item->productVariant;
                $product = $variant?->product;
            @endphp
            <div class="p-2 border rounded-3 bg-white d-flex align-items-center gap-2.5 mini-cart-item transition {{ !$item->is_selected ? 'opacity-50 bg-light' : '' }}" data-id="{{ $item->id }}">
                <input type="checkbox" class="form-check-input mini-cart-item-checkbox m-0 cursor-pointer flex-shrink-0" 
                       data-id="{{ $item->id }}" {{ $item->is_selected ? 'checked' : '' }} title="Chọn mua sản phẩm này">
                
                <a href="{{ $product ? route('products.show', $product->slug) : '#' }}" class="flex-shrink-0">
                    <img src="{{ \App\Support\MediaHelper::productImage($variant?->image ?? $product?->thumbnail, $product?->id) }}" 
                         class="rounded-2 border object-fit-cover shadow-2xs" style="width: 46px; height: 46px;" alt="">
                </a>

                <div class="flex-grow-1 min-w-0">
                    <a href="{{ $product ? route('products.show', $product->slug) : '#' }}" 
                       class="fw-bold small text-dark text-truncate d-block text-decoration-none hover-text-success mb-0.5" 
                       style="font-size: 0.82rem;" title="{{ $product?->name }}">
                        {{ $product?->name ?? 'Sản phẩm' }}
                    </a>
                    
                    <div class="d-flex align-items-center gap-1.5 mb-1">
                        <span class="badge bg-secondary-subtle text-secondary small py-0.5 px-1.5 fw-normal text-truncate" style="font-size: 0.68rem; max-width: 130px;">
                            {{ $variant?->name ?? 'Mặc định' }}
                        </span>
                        <span class="text-muted" style="font-size: 0.72rem;">·</span>
                        <span class="text-danger fw-bold small mini-cart-item-subtotal" data-id="{{ $item->id }}" style="font-size: 0.78rem;">
                            {{ number_format($item->subtotal()) }}₫
                        </span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between gap-2">
                        {{-- Nút tăng giảm số lượng --}}
                        <div class="input-group input-group-sm" style="width: 82px; height: 24px;">
                            <button class="btn btn-light border mini-cart-btn-minus px-1.5 py-0 d-flex align-items-center justify-content-center" 
                                    type="button" data-id="{{ $item->id }}" style="line-height: 1; font-size: 0.75rem;">-</button>
                            <input type="number" class="form-control text-center mini-cart-input-qty p-0 fw-semibold bg-white" 
                                   style="font-size: 0.78rem;" data-id="{{ $item->id }}" value="{{ $item->quantity }}" min="1">
                            <button class="btn btn-light border mini-cart-btn-plus px-1.5 py-0 d-flex align-items-center justify-content-center" 
                                    type="button" data-id="{{ $item->id }}" style="line-height: 1; font-size: 0.75rem;">+</button>
                        </div>

                        <button type="button" class="btn btn-sm text-secondary hover-danger mini-cart-btn-remove p-0 border-0 bg-transparent" 
                                data-id="{{ $item->id }}" title="Xóa khỏi giỏ hàng">
                            <i class="bi bi-trash3" style="font-size: 0.85rem;"></i>
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
