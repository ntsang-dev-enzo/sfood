@if(!isset($miniCartItems) || $miniCartItems->isEmpty())
    <div class="text-center py-4 text-muted" id="mini-cart-empty-msg">
        <i class="bi bi-cart-x fs-2 text-secondary opacity-50 d-block mb-2"></i>
        Giỏ hàng trống
    </div>
@else
    <div class="list-group list-group-flush">
        @foreach($miniCartItems as $item)
            <div class="list-group-item px-0 py-2.5 d-flex align-items-center gap-2.5 mini-cart-item {{ !$item->is_selected ? 'opacity-50' : '' }}" data-id="{{ $item->id }}">
                <input type="checkbox" class="form-check-input mini-cart-item-checkbox m-0 cursor-pointer" data-id="{{ $item->id }}" {{ $item->is_selected ? 'checked' : '' }}>
                <img src="{{ \App\Support\MediaHelper::productImage($item->productVariant->product->thumbnail) }}" 
                     class="rounded-3 border object-fit-cover flex-shrink-0" style="width: 46px; height: 46px;" alt="">
                <div class="flex-grow-1 min-w-0">
                    <h6 class="mb-0 text-truncate fw-semibold small text-dark" style="font-size: 0.82rem;">{{ $item->productVariant->product->name }}</h6>
                    <small class="text-muted d-block text-truncate" style="font-size: 0.72rem;">{{ $item->productVariant->name }}</small>
                    <div class="d-flex align-items-center justify-content-between gap-2 mt-1">
                        <div class="input-group input-group-sm" style="width: 80px;">
                            <button class="btn btn-outline-secondary mini-cart-btn-minus px-1.5 py-0" type="button" data-id="{{ $item->id }}">-</button>
                            <input type="number" class="form-control text-center mini-cart-input-qty p-0" style="font-size: 0.8rem;" data-id="{{ $item->id }}" value="{{ $item->quantity }}" min="1">
                            <button class="btn btn-outline-secondary mini-cart-btn-plus px-1.5 py-0" type="button" data-id="{{ $item->id }}">+</button>
                        </div>
                        <span class="text-danger fw-bold small mini-cart-item-subtotal" data-id="{{ $item->id }}">
                            {{ number_format($item->subtotal()) }}₫
                        </span>
                    </div>
                </div>
                <button type="button" class="btn btn-sm text-secondary hover-danger mini-cart-btn-remove p-1" data-id="{{ $item->id }}" title="Xóa">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        @endforeach
    </div>
@endif

