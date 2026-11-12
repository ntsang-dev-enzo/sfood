@extends('layouts.app')
@section('title', 'Giỏ hàng của bạn - SFood')

@section('content')
<div class="bg-body-tertiary py-4 min-vh-100">
    <div class="container py-2">

        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb bg-white p-3 rounded-3 shadow-sm border mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-success"><i class="bi bi-house-door me-1"></i>{{ __('app.home_title') }}</a></li>
                <li class="breadcrumb-item active fw-semibold" aria-current="page">Giỏ hàng của bạn</li>
            </ol>
        </nav>

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="alert alert-success rounded-3 border-0 shadow-sm mb-3">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info rounded-3 border-0 shadow-sm mb-3 d-flex align-items-center">
                <i class="bi bi-info-circle-fill me-2 fs-5 text-info"></i>
                <div>{{ session('info') }}</div>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger rounded-3 border-0 shadow-sm mb-3">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            </div>
        @endif

        {{-- Cart Content --}}
        <div id="cart-page-wrapper">
            @if($items->isEmpty())
                {{-- Khối Giỏ Hàng Trống --}}
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white my-3" id="cart-empty-state">
                    <div class="mb-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light border p-4 shadow-sm" style="width: 88px; height: 88px;">
                            <i class="bi bi-cart-x text-secondary" style="font-size: 2.5rem;"></i>
                        </span>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Giỏ hàng của bạn đang trống</h5>
                    <p class="text-muted small mb-4">Hãy thêm những món nông sản tươi ngon, an toàn đạt chuẩn VietGAP vào giỏ hàng ngay nhé!</p>
                    <div>
                        <a href="{{ route('products.index') }}" class="btn btn-success rounded-pill px-4 py-2.5 fw-bold shadow-sm">
                            <i class="bi bi-bag-plus me-1.5"></i>Khám phá sản phẩm ngay
                        </a>
                    </div>
                </div>
            @else
                @php
                    $selectedCount = $items->where('is_selected', true)->count();
                    $allSelected = $items->isNotEmpty() && ($selectedCount === $items->count());
                @endphp
                <div class="row g-4" id="cart-active-state">
                    {{-- Cột Trái: Danh Sách Sản Phẩm (8 Cols) --}}
                    <div class="col-lg-8">
                        {{-- Thanh chọn tất cả & xóa tất cả --}}
                        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="form-check mb-0 d-flex align-items-center">
                                    <input class="form-check-input ms-0 me-2 cursor-pointer" type="checkbox" id="cart-select-all" @checked($allSelected)>
                                    <label class="form-check-label small fw-bold text-dark cursor-pointer" for="cart-select-all">
                                        Chọn tất cả (<span id="cart-selected-count">{{ $selectedCount }}</span>/{{ $items->count() }} sản phẩm)
                                    </label>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 small" id="cart-clear-all-btn">
                                    <i class="bi bi-trash3 me-1"></i>Xóa tất cả
                                </button>
                            </div>
                        </div>

                        {{-- Danh sách các dòng sản phẩm --}}
                        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-3">
                            {{-- Header Bảng trên Desktop --}}
                            <div class="p-3 bg-light border-bottom d-none d-md-flex align-items-center text-secondary small fw-bold">
                                <div style="width: 50%;">Sản phẩm</div>
                                <div class="text-center" style="width: 15%;">Đơn giá</div>
                                <div class="text-center" style="width: 18%;">Số lượng</div>
                                <div class="text-end" style="width: 17%;">Thành tiền</div>
                            </div>

                            <div class="list-group list-group-flush" id="cart-items-container">
                                @foreach ($items as $item)
                                    @php
                                        $v = $item->productVariant;
                                        $product = $v?->product;
                                    @endphp
                                    <div class="list-group-item p-3 p-md-3.5 cart-item-row transition {{ !$item->is_selected ? 'bg-light-subtle opacity-75' : '' }}" 
                                         data-item-id="{{ $item->id }}" data-unit-price="{{ $v?->effectivePrice() ?? 0 }}">
                                        <div class="row align-items-center g-2 g-md-3">
                                            {{-- Cột 1: Checkbox + Ảnh + Tên sản phẩm --}}
                                            <div class="col-12 col-md-6 d-flex align-items-center gap-2.5">
                                                <input type="checkbox" class="form-check-input cart-item-checkbox flex-shrink-0 m-0 cursor-pointer" 
                                                       data-item-id="{{ $item->id }}" @checked($item->is_selected)>
                                                <a href="{{ $product ? route('products.show', $product->slug) : '#' }}" class="flex-shrink-0">
                                                    <img src="{{ \App\Support\MediaHelper::productImage($v?->image ?? $product?->thumbnail, $product?->id) }}" 
                                                         class="rounded-3 border object-fit-cover shadow-2xs" style="width: 58px; height: 58px;" alt="">
                                                </a>
                                                <div class="min-w-0 flex-grow-1">
                                                    <a href="{{ $product ? route('products.show', $product->slug) : '#' }}" 
                                                       class="text-decoration-none fw-semibold text-dark text-truncate d-block mb-1 hover-text-success small">
                                                        {{ $product?->name ?? 'Sản phẩm' }}
                                                    </a>
                                                    <span class="badge bg-secondary-subtle text-secondary small fw-normal">
                                                        {{ $v?->name ?? 'Mặc định' }}
                                                    </span>
                                                    {{-- Giá hiển thị trên mobile --}}
                                                    <div class="text-danger fw-bold small d-md-none mt-1">
                                                        {{ number_format($v?->effectivePrice() ?? 0) }}₫
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Cột 2: Đơn giá (Desktop) --}}
                                            <div class="col-md-2 d-none d-md-block text-center small text-secondary fw-semibold">
                                                {{ number_format($v?->effectivePrice() ?? 0) }}₫
                                            </div>

                                            {{-- Cột 3: Bộ tăng/giảm số lượng --}}
                                            <div class="col-6 col-md-2">
                                                <div class="input-group input-group-sm rounded-pill border overflow-hidden mx-auto" style="max-width: 95px;">
                                                    <button class="btn btn-light cart-btn-minus px-2 py-0 border-0" type="button" data-item-id="{{ $item->id }}">−</button>
                                                    <input type="number" class="form-control text-center cart-input-qty border-0 p-0 fw-bold bg-white text-dark" 
                                                           style="font-size: 0.85rem;" data-item-id="{{ $item->id }}" value="{{ $item->quantity }}" min="1" max="{{ $v?->stock ?? 99 }}">
                                                    <button class="btn btn-light cart-btn-plus px-2 py-0 border-0" type="button" data-item-id="{{ $item->id }}">+</button>
                                                </div>
                                            </div>

                                            {{-- Cột 4: Thành tiền + Nút xóa --}}
                                            <div class="col-6 col-md-2 d-flex align-items-center justify-content-end gap-2 text-end">
                                                <span class="fw-bold text-danger cart-row-subtotal small" data-item-id="{{ $item->id }}">
                                                    {{ number_format($item->subtotal()) }}₫
                                                </span>
                                                <button type="button" class="btn btn-link text-secondary hover-danger p-1 cart-btn-remove border-0" 
                                                        data-item-id="{{ $item->id }}" title="Xóa sản phẩm">
                                                    <i class="bi bi-trash fs-6"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Nút tiếp tục mua sắm --}}
                        <div class="d-none d-md-block">
                            <a href="{{ route('products.index') }}" class="btn btn-outline-success rounded-pill px-4 py-2 small fw-semibold">
                                <i class="bi bi-arrow-left me-1.5"></i>Tiếp tục chọn thêm sản phẩm
                            </a>
                        </div>
                    </div>

                    {{-- Cột Phải: Tóm Tắt Đơn Hàng (4 Cols - Sticky) --}}
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white position-sticky" style="top: 90px;">
                            <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                                <i class="bi bi-receipt text-success"></i>Tóm tắt đơn hàng
                            </h5>

                            <div class="d-flex justify-content-between align-items-center mb-2.5 small">
                                <span class="text-secondary">Tạm tính (đã chọn):</span>
                                <span class="fw-bold text-dark" id="cart-summary-subtotal">{{ number_format($selectedSubtotal) }}₫</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3 small">
                                <span class="text-secondary">Phí vận chuyển:</span>
                                <span class="text-success fw-semibold">Tính khi thanh toán</span>
                            </div>

                            <div class="p-2.5 rounded-3 bg-success-subtle text-success small mb-3 d-flex align-items-start gap-2">
                                <i class="bi bi-truck fs-5 mt-n0.5"></i>
                                <div style="font-size: 0.8rem;">
                                    Miễn phí ship nội thành Cần Thơ hoặc đơn hàng từ <strong>1.000.000₫</strong>
                                </div>
                            </div>

                            <div class="border-top pt-3 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-dark">Tổng thanh toán:</span>
                                    <span class="fs-4 fw-bold text-danger" id="cart-summary-total">{{ number_format($selectedSubtotal) }}₫</span>
                                </div>
                                <small class="text-muted d-block text-end" style="font-size: 0.75rem;">(Chưa tính mã giảm giá voucher)</small>
                            </div>

                            <a href="{{ route('checkout.index') }}" id="cart-checkout-btn" 
                               class="btn btn-success w-100 py-3 fw-bold rounded-pill shadow-sm fs-6 mb-2.5 d-flex align-items-center justify-content-center gap-2 {{ $selectedSubtotal > 0 ? '' : 'disabled' }}">
                                <span>Tiến hành thanh toán</span>
                                <i class="bi bi-arrow-right"></i>
                            </a>

                            <div class="d-block d-md-none">
                                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary w-100 py-2 rounded-pill fw-semibold small">
                                    <i class="bi bi-arrow-left me-1"></i>Tiếp tục mua hàng
                                </a>
                            </div>

                            <div class="mt-4 pt-3 border-top small text-muted d-flex flex-column gap-2" style="font-size: 0.78rem;">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-shield-check text-success fs-6"></i>
                                    <span>100% Nông sản tươi sạch đạt chuẩn VietGAP</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-arrow-counterclockwise text-primary fs-6"></i>
                                    <span>Đổi trả miễn phí trong 24h nếu không hài lòng</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        const fetchCart = async (url, data) => {
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(data)
                });
                return await res.json();
            } catch (err) {
                console.error('Cart API error:', err);
                return null;
            }
        };

        const updateCartPageUI = (data) => {
            if (!data) return;

            // Cập nhật badge header
            document.querySelectorAll('[data-cart-count]').forEach(el => {
                el.textContent = data.count;
                if (data.count > 0) el.classList.remove('d-none');
                else el.classList.add('d-none');
            });

            // Cập nhật tổng tiền tạm tính
            const formattedSelected = data.selected_subtotal_formatted || (new Intl.NumberFormat('vi-VN').format(data.selected_subtotal || 0) + '₫');
            const subtotalEl = document.getElementById('cart-summary-subtotal');
            const totalEl = document.getElementById('cart-summary-total');
            if (subtotalEl) subtotalEl.textContent = formattedSelected;
            if (totalEl) totalEl.textContent = formattedSelected;

            // Cập nhật trạng thái nút checkout
            const checkoutBtn = document.getElementById('cart-checkout-btn');
            if (checkoutBtn) {
                if ((data.selected_subtotal || 0) > 0) checkoutBtn.classList.remove('disabled');
                else checkoutBtn.classList.add('disabled');
            }

            // Cập nhật số lượng sản phẩm được chọn
            const checkboxes = document.querySelectorAll('.cart-item-checkbox');
            const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
            const countLabel = document.getElementById('cart-selected-count');
            if (countLabel) countLabel.textContent = checkedCount;

            const selectAllCb = document.getElementById('cart-select-all');
            if (selectAllCb) selectAllCb.checked = (checkboxes.length > 0 && checkedCount === checkboxes.length);

            // Nếu giỏ hàng trống hoàn toàn -> tải lại trang để render trạng thái trống
            if (data.empty || checkboxes.length === 0) {
                location.reload();
            }
        };

        // Bắt sự kiện trên container giỏ hàng
        const cartContainer = document.getElementById('cart-active-state');
        if (cartContainer) {
            cartContainer.addEventListener('click', async (e) => {
                const btnPlus = e.target.closest('.cart-btn-plus');
                const btnMinus = e.target.closest('.cart-btn-minus');
                const btnRemove = e.target.closest('.cart-btn-remove');

                if (btnPlus) {
                    const id = btnPlus.dataset.itemId;
                    const input = cartContainer.querySelector(`.cart-input-qty[data-item-id="${id}"]`);
                    const newQty = parseInt(input.value || 1) + 1;
                    input.value = newQty;

                    const data = await fetchCart('{{ route('cart.update') }}', { item_id: parseInt(id), quantity: newQty });
                    if (data && data.item) {
                        const rowSub = cartContainer.querySelector(`.cart-row-subtotal[data-item-id="${id}"]`);
                        if (rowSub) rowSub.textContent = data.item.subtotal_formatted;
                    }
                    updateCartPageUI(data);
                } else if (btnMinus) {
                    const id = btnMinus.dataset.itemId;
                    const input = cartContainer.querySelector(`.cart-input-qty[data-item-id="${id}"]`);
                    if (parseInt(input.value) > 1) {
                        const newQty = parseInt(input.value) - 1;
                        input.value = newQty;

                        const data = await fetchCart('{{ route('cart.update') }}', { item_id: parseInt(id), quantity: newQty });
                        if (data && data.item) {
                            const rowSub = cartContainer.querySelector(`.cart-row-subtotal[data-item-id="${id}"]`);
                            if (rowSub) rowSub.textContent = data.item.subtotal_formatted;
                        }
                        updateCartPageUI(data);
                    }
                } else if (btnRemove) {
                    const id = btnRemove.dataset.itemId;
                    if (!confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?')) return;

                    const data = await fetchCart('{{ route('cart.remove') }}', { item_id: parseInt(id) });
                    const row = cartContainer.querySelector(`.cart-item-row[data-item-id="${id}"]`);
                    if (row) row.remove();
                    updateCartPageUI(data);
                }
            });

            // Thay đổi checkbox chọn item
            cartContainer.addEventListener('change', async (e) => {
                const itemCheck = e.target.closest('.cart-item-checkbox');
                if (itemCheck) {
                    const id = itemCheck.dataset.itemId;
                    const isChecked = itemCheck.checked;
                    const row = cartContainer.querySelector(`.cart-item-row[data-item-id="${id}"]`);
                    if (row) {
                        if (isChecked) {
                            row.classList.remove('bg-light-subtle', 'opacity-75');
                        } else {
                            row.classList.add('bg-light-subtle', 'opacity-75');
                        }
                    }

                    const data = await fetchCart('{{ route('cart.toggle_select') }}', { item_id: parseInt(id), selected: isChecked });
                    updateCartPageUI(data);
                }
            });

            // Chọn tất cả
            const selectAllBtn = document.getElementById('cart-select-all');
            if (selectAllBtn) {
                selectAllBtn.addEventListener('change', async function() {
                    const isChecked = this.checked;
                    document.querySelectorAll('.cart-item-checkbox').forEach(cb => {
                        cb.checked = isChecked;
                        const row = cb.closest('.cart-item-row');
                        if (row) {
                            if (isChecked) row.classList.remove('bg-light-subtle', 'opacity-75');
                            else row.classList.add('bg-light-subtle', 'opacity-75');
                        }
                    });

                    const data = await fetchCart('{{ route('cart.toggle_select_all') }}', { selected: isChecked });
                    updateCartPageUI(data);
                });
            }

            // Xóa tất cả
            const clearAllBtn = document.getElementById('cart-page-clear-all');
            if (clearAllBtn) {
                clearAllBtn.addEventListener('click', async function() {
                    if (!confirm('Bạn có chắc chắn muốn xóa toàn bộ giỏ hàng?')) return;
                    const data = await fetchCart('{{ route('cart.clear') }}', {});
                    location.reload();
                });
            }
        }
    });
</script>
@endpush
@endsection

