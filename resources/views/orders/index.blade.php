@extends('layouts.profile')

@section('profile_content')
<div class="d-flex flex-column gap-3">
    {{-- Status Tabs --}}
    <div class="d-flex gap-2 overflow-x-auto pb-2 scrollbar-none">
        <a href="{{ route('orders.index') }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ !request('status') ? 'btn-success' : 'btn-light border' }}">Tất cả</a>
        <a href="{{ route('orders.index', ['status' => 'pending']) }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ request('status') == 'pending' ? 'btn-success' : 'btn-light border' }}">Chờ xử lý</a>
        <a href="{{ route('orders.index', ['status' => 'confirmed']) }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ request('status') == 'confirmed' ? 'btn-success' : 'btn-light border' }}">Đã xác nhận</a>
        <a href="{{ route('orders.index', ['status' => 'shipping']) }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ request('status') == 'shipping' ? 'btn-success' : 'btn-light border' }}">Đang giao</a>
        <a href="{{ route('orders.index', ['status' => 'delivered']) }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ request('status') == 'delivered' ? 'btn-success' : 'btn-light border' }}">Đã giao</a>
        <a href="{{ route('orders.index', ['status' => 'cancelled']) }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ request('status') == 'cancelled' ? 'btn-success' : 'btn-light border' }}">Đã hủy</a>
    </div>

    {{-- Flash messages --}}
    @if(session('success') && !session('payment_switched_popup') && !session('address_updated_popup'))
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

    {{-- Order List --}}
    @forelse ($orders as $order)
        @php
            $isRepayHighlight = request('repay') == $order->id;
            $gateway = $order->payment?->gateway ?? 'cod';
            $isPaid = $order->payment_status === 'paid';
            $isPendingQr = ($gateway === 'payos' && ! $isPaid && in_array($order->order_status, ['pending', 'confirmed']));
            $isPendingCod = ($gateway === 'cod' && ! $isPaid && in_array($order->order_status, ['pending']));
        @endphp
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white {{ $isRepayHighlight ? 'border border-2 border-warning shadow' : '' }}" id="order-card-{{ $order->id }}">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3 pb-3 border-bottom">
                <div>
                    <h5 class="fw-bold text-dark mb-1">Đơn hàng #{{ $order->order_code }}</h5>
                    <small class="text-muted"><i class="bi bi-clock me-1"></i>{{ $order->created_at->format('d/m/Y H:i') }}</small>
                </div>
                <div class="text-end">
                    @php
                        $statusClasses = [
                            'pending' => 'bg-warning-subtle text-warning-emphasis',
                            'confirmed' => 'bg-info-subtle text-info-emphasis',
                            'shipping' => 'bg-primary-subtle text-primary',
                            'delivered' => 'bg-success-subtle text-success',
                            'cancelled' => 'bg-danger-subtle text-danger',
                        ];
                        $statusLabels = [
                            'pending' => 'Chờ xử lý',
                            'confirmed' => 'Đã xác nhận',
                            'shipping' => 'Đang giao',
                            'delivered' => 'Đã giao',
                            'cancelled' => 'Đã hủy',
                        ];
                    @endphp
                    <span class="badge {{ $statusClasses[$order->order_status] ?? 'bg-secondary' }} rounded-pill px-3 py-1.5">
                        {{ $statusLabels[$order->order_status] ?? ucfirst($order->order_status) }}
                    </span>
                    @if($order->payment_status === 'refund_pending')
                        <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-2 py-1 ms-1"><i class="bi bi-hourglass-split me-1"></i>Chờ hoàn tiền</span>
                    @elseif($order->payment_status === 'refunded')
                        <span class="badge bg-info-subtle text-info-emphasis rounded-pill px-2 py-1 ms-1"><i class="bi bi-check-circle-fill me-1"></i>Đã hoàn tiền</span>
                    @elseif($isPaid)
                        <span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-1 ms-1"><i class="bi bi-check2-circle me-1"></i>Đã thanh toán ({{ strtoupper($gateway) }})</span>
                    @elseif($isPendingQr)
                        <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 ms-1 fw-bold"><i class="bi bi-qr-code me-1"></i>Chờ thanh toán QR</span>
                    @elseif($gateway === 'cod')
                        <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5 py-1 ms-1"><i class="bi bi-cash me-1"></i>COD</span>
                    @endif
                    <div class="fs-5 fw-bold text-danger mt-1">
                        {{ number_format($order->total_amount) }}đ
                        @if($order->shipping_fee > 0)
                            <span class="small text-muted fw-normal d-block" style="font-size: 0.75rem;">(Phí ship: {{ number_format($order->shipping_fee) }}đ)</span>
                        @else
                            <span class="small text-success fw-normal d-block" style="font-size: 0.75rem;">(Miễn phí ship)</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Pending QR Notice & Action Buttons (Tiếp tục thanh toán QR / Thoát ra chọn COD) --}}
            @if($isPendingQr)
                <div class="alert alert-warning border border-warning-subtle rounded-3 p-3 mb-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-circle bg-warning text-dark p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                            <i class="bi bi-qr-code-scan fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark">Đơn hàng đang chờ thanh toán qua mã QR (PayOS)</div>
                            <div class="small text-secondary">Bạn có thể tiếp tục thanh toán ngay bằng QR hoặc thoát ra chuyển sang thanh toán khi nhận hàng (COD).</div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 flex-shrink-0">
                        {{-- Nút TIẾP TỤC quay lại thanh toán QR --}}
                        <a href="{{ route('orders.repay-qr', $order) }}" class="btn btn-warning text-dark fw-bold btn-sm rounded-pill px-3 py-1.5 shadow-sm d-inline-flex align-items-center gap-1.5">
                            <i class="bi bi-qr-code"></i>
                            <span>Tiếp tục thanh toán QR</span>
                        </a>

                        {{-- Nút THOÁT RA CHỌN THANH TOÁN COD (Chỉ 1 lần duy nhất, ẩn khi đã đổi) --}}
                        @if($order->canSwitchPayment())
                            <form id="switch-cod-form-{{ $order->id }}" action="{{ route('orders.switch-cod', $order) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="button" class="btn btn-outline-dark btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 btn-switch-cod-trigger"
                                        data-order-code="{{ $order->order_code }}"
                                        data-form-id="switch-cod-form-{{ $order->id }}">
                                    <i class="bi bi-cash-stack text-success"></i>
                                    <span>Đổi sang COD</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Items --}}
            <div class="bg-light rounded-3 p-3 mb-3">
                @foreach ($order->items as $item)
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 py-2 border-bottom border-light-subtle last-border-0">
                        <div>
                            @if($item->productVariant && $item->productVariant->product)
                                <a href="{{ route('products.show', $item->productVariant->product->slug) }}" class="fw-semibold text-decoration-none text-dark hover-text-success">
                                    {{ $item->product_name }} <span class="badge bg-secondary-subtle text-secondary small">{{ $item->variant_name }}</span>
                                </a>
                            @else
                                <span class="fw-semibold text-dark">{{ $item->product_name }} ({{ $item->variant_name }})</span>
                            @endif
                            <span class="text-muted small ms-2">× {{ $item->quantity }}</span>
                        </div>
                        @if($order->order_status === 'delivered')
                            @php
                                $productId = $item->productVariant->product_id;
                                $existingReview = \App\Models\ProductReview::where('user_id', auth()->id())->where('product_id', $productId)->first();
                            @endphp
                            @if($existingReview)
                                <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 btn-review" 
                                        data-product-id="{{ $productId }}" data-order-id="{{ $order->id }}" 
                                        data-rating="{{ $existingReview->rating }}" data-comment="{{ $existingReview->comment }}">
                                    <i class="bi bi-pencil-square me-1"></i>Sửa đánh giá
                                </button>
                            @else
                                <button type="button" class="btn btn-sm btn-success rounded-pill px-3 py-1 btn-review" 
                                        data-product-id="{{ $productId }}" data-order-id="{{ $order->id }}" 
                                        data-rating="5" data-comment="">
                                    <i class="bi bi-star-fill text-warning me-1"></i>Viết đánh giá
                                </button>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 pt-2">
                {{-- Địa chỉ giao hàng: Cho phép đổi 1 lần rồi ẩn nút --}}
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <small class="text-muted">
                        <i class="bi bi-geo-alt me-1 text-danger"></i>Giao đến: 
                        <strong class="text-dark">{{ $order->shipping_address['recipient_name'] ?? '' }} ({{ $order->shipping_address['phone'] ?? '' }})</strong> — 
                        {{ $order->shipping_address['address'] ?? '' }}
                    </small>

                    @if($order->canUpdateAddress())
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-0.5 small d-inline-flex align-items-center gap-1 btn-edit-address"
                                data-order-id="{{ $order->id }}"
                                data-order-code="{{ $order->order_code }}"
                                data-recipient="{{ $order->shipping_address['recipient_name'] ?? '' }}"
                                data-phone="{{ $order->shipping_address['phone'] ?? '' }}"
                                data-address="{{ $order->shipping_address['address'] ?? '' }}"
                                data-subtotal="{{ (float) $order->subtotal }}"
                                data-shipping-fee="{{ (float) $order->shipping_fee }}"
                                data-discount="{{ (float) ($order->discount_amount ?? 0) }}"
                                data-points-discount="{{ (float) ($order->points_discount ?? 0) }}"
                                data-total="{{ (float) $order->total_amount }}"
                                data-action-url="{{ route('orders.update-address', $order) }}"
                                title="Đổi địa chỉ giao hàng (Chỉ 1 lần duy nhất)">
                            <i class="bi bi-pencil-square"></i>
                            <span>Đổi địa chỉ</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.65rem;">1 lần</span>
                        </button>
                    @elseif(!empty($order->shipping_address['address_updated']))
                        <span class="badge bg-light text-muted border small"><i class="bi bi-check2 text-success me-1"></i>Đã cập nhật</span>
                    @endif
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    @if($order->order_status === 'delivered')
                        <form action="{{ route('orders.reorder', $order) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm">
                                <i class="bi bi-arrow-repeat"></i>
                                <span>Mua lại</span>
                            </button>
                        </form>
                    @endif

                    @if($isPendingCod && $order->canSwitchPayment())
                        <a href="{{ route('orders.repay-qr', $order) }}" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1" title="Chuyển sang thanh toán QR">
                            <i class="bi bi-qr-code"></i>
                            <span>Thanh toán bằng QR</span>
                        </a>
                    @endif

                    @if(in_array($order->order_status, ['pending', 'confirmed']))
                        <form action="{{ $order->payment_status === 'paid' ? route('orders.refund', $order) : route('orders.cancel', $order) }}" method="POST">
                            @csrf
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 btn-cancel-order" data-is-paid="{{ $order->payment_status === 'paid' ? 'true' : 'false' }}">
                                {{ $order->payment_status === 'paid' ? 'Hủy đơn & Hoàn tiền' : 'Hủy đơn hàng' }}
                            </button>
                        </form>
                    @elseif($order->order_status === 'shipping' && $order->payment_status === 'paid')
                        <span class="small text-muted fst-italic"><i class="bi bi-exclamation-circle text-warning me-1"></i>Đơn hàng đang được giao, không thể yêu cầu hủy</span>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
            <i class="bi bi-inbox display-3 text-muted opacity-50 mb-3"></i>
            <h5 class="fw-bold text-dark">Chưa có đơn hàng nào</h5>
            <p class="text-muted small">Hãy khám phá các sản phẩm tươi sạch và bắt đầu mua sắm ngay hôm nay.</p>
            <div class="mt-2">
                <a href="{{ route('products.index') }}" class="btn btn-success rounded-pill px-4 py-2 fw-semibold">
                    <i class="bi bi-bag-plus me-1"></i> Mua sắm ngay
                </a>
            </div>
        </div>
    @endforelse
</div>

{{-- Cancel Modal (Bootstrap 5 modal structure) --}}
<div class="modal fade" id="cancel-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Xác nhận hủy đơn hàng</h5>
                <button type="button" class="btn-close" id="btn-close-modal" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p id="cancel-modal-msg" class="text-secondary mb-0">Bạn có chắc chắn muốn hủy đơn hàng này không? Quá trình này không thể hoàn tác.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Đóng lại</button>
                <button type="button" id="btn-confirm-cancel" class="btn btn-danger rounded-pill px-4 fw-semibold">Xác nhận Hủy</button>
            </div>
        </div>
    </div>
</div>

{{-- Review Modal --}}
<div class="modal fade" id="review-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-star-fill text-warning me-2"></i>Đánh giá sản phẩm</h5>
                <button type="button" class="btn-close" id="btn-close-review" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('reviews.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body py-4">
                    <input type="hidden" name="product_id" id="review-product-id">
                    <input type="hidden" name="order_id" id="review-order-id">
                    
                    <div class="mb-3 text-center">
                        <label class="form-label fw-bold text-dark d-block">Chất lượng sản phẩm</label>
                        <div class="d-inline-flex gap-2 fs-2" id="star-rating">
                            <button type="button" data-val="1" class="star btn p-0 fs-2 text-warning border-0"><i class="bi bi-star-fill"></i></button>
                            <button type="button" data-val="2" class="star btn p-0 fs-2 text-warning border-0"><i class="bi bi-star-fill"></i></button>
                            <button type="button" data-val="3" class="star btn p-0 fs-2 text-warning border-0"><i class="bi bi-star-fill"></i></button>
                            <button type="button" data-val="4" class="star btn p-0 fs-2 text-warning border-0"><i class="bi bi-star-fill"></i></button>
                            <button type="button" data-val="5" class="star btn p-0 fs-2 text-warning border-0"><i class="bi bi-star-fill"></i></button>
                        </div>
                        <input type="hidden" name="rating" id="review-rating" value="5">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Bình luận cảm nhận</label>
                        <textarea name="comment" id="review-comment" rows="3" class="form-control" placeholder="Sản phẩm rất tươi ngon, đóng gói cẩn thận..."></textarea>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold text-dark small">Hình ảnh đính kèm (Ảnh &lt; 5MB)</label>
                        <input type="file" name="attachments[]" multiple accept="image/*,video/*" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold">Gửi đánh giá</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Switch COD Confirmation Modal (Ngắn gọn, không mô tả chi tiết) --}}
<div class="modal fade" id="switch-cod-confirm-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-cash-stack text-success"></i>
                    <span>Đổi sang thanh toán COD</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <p class="text-secondary mb-1">
                    Bạn có chắc muốn đổi đơn hàng <strong id="switch-cod-order-code" class="text-dark">#</strong> sang thanh toán khi nhận hàng (COD) không?
                </p>
                <small class="text-muted fst-italic">Lưu ý: Chỉ được đổi 1 lần duy nhất.</small>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                <button type="button" id="btn-confirm-switch-cod" class="btn btn-success rounded-pill px-4 fw-semibold">
                    Xác nhận
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Edit Shipping Address Modal (Có chọn địa chỉ lưu sẵn hoặc nhập tay có API tỉnh thành) --}}
<div class="modal fade" id="edit-address-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-geo-alt-fill text-danger"></i>
                    <span>Cập nhật địa chỉ nhận hàng</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="edit-address-form" method="POST" action="">
                @csrf
                <div class="modal-body py-3">
                    <div class="alert alert-info border-0 rounded-3 p-2.5 small mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i>
                        Đơn hàng <strong id="edit-address-order-code">#</strong>: Bạn chỉ được đổi địa chỉ <strong>01 lần duy nhất</strong> khi đơn hàng chưa giao.
                    </div>

                    {{-- Chọn địa chỉ đã lưu hoặc nhập mới --}}
                    @if(isset($addresses) && $addresses->isNotEmpty())
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary mb-2">Chọn địa chỉ đã lưu hoặc nhập mới:</label>
                            <div class="d-flex flex-column gap-2 mb-3">
                                @foreach($addresses as $addr)
                                    <div class="form-check p-2.5 border rounded-3 transition edit-addr-card bg-light">
                                        <input class="form-check-input ms-0 me-2 cursor-pointer edit-addr-radio" 
                                               type="radio" name="shipping_address_id" 
                                               id="edit-addr-{{ $addr->id }}" 
                                               value="{{ $addr->id }}"
                                               data-city="{{ $addr->city }}"
                                               data-district="{{ $addr->district }}"
                                               data-ward="{{ $addr->ward }}"
                                               data-address="{{ $addr->address_line }}"
                                               data-full-address="{{ $addr->fullAddress() }}"
                                               data-recipient="{{ $addr->recipient_name }}"
                                               data-phone="{{ $addr->phone }}">
                                        <label class="form-check-label ps-1 cursor-pointer w-100" for="edit-addr-{{ $addr->id }}">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span class="fw-bold text-dark small">{{ $addr->recipient_name }} — {{ $addr->phone }}</span>
                                                @if($addr->is_default)
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.68rem;">Mặc định</span>
                                                @endif
                                            </div>
                                            <div class="small text-muted" style="font-size: 0.78rem;">{{ $addr->fullAddress() }}</div>
                                        </label>
                                    </div>
                                @endforeach

                                <div class="form-check p-2.5 border border-dashed rounded-3 edit-addr-card border-success bg-white shadow-sm">
                                    <input class="form-check-input ms-0 me-2 cursor-pointer edit-addr-radio" 
                                           type="radio" name="shipping_address_id" id="edit-addr-new" value="" checked>
                                    <label class="form-check-label ps-1 cursor-pointer w-100" for="edit-addr-new">
                                        <div class="fw-bold text-dark small"><i class="bi bi-pencil-square text-success me-1"></i>Nhập thông tin địa chỉ mới</div>
                                        <div class="small text-muted" style="font-size: 0.78rem;">Nhập trực tiếp người nhận, tỉnh/thành phố và số nhà</div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Khối nhập tay địa chỉ mới (Có API tỉnh thành và Số nhà, đường) --}}
                    <div id="edit-address-manual-wrapper" class="p-3 bg-light rounded-3 border">
                        <h6 class="fw-bold text-dark mb-2.5 small text-uppercase">
                            <i class="bi bi-geo-alt text-success me-1"></i>Thông tin địa chỉ nhận hàng
                        </h6>
                        <div class="row g-2.5">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Họ tên người nhận <span class="text-danger">*</span></label>
                                <input type="text" name="recipient_name" id="modal-recipient-name" class="form-control" placeholder="Ví dụ: Nguyễn Văn A" required maxlength="100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Số điện thoại người nhận <span class="text-danger">*</span></label>
                                <input type="tel" name="phone" id="modal-phone" class="form-control" placeholder="Ví dụ: 0912345678" required maxlength="20">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Địa chỉ cụ thể (Số nhà, đường) <span class="text-danger">*</span></label>
                                <input type="text" name="address_line" id="modal-address-line" class="form-control" placeholder="Ví dụ: 123 Đường 30/4" required maxlength="255">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Tỉnh / Thành phố <span class="text-danger">*</span></label>
                                <select id="edit-province-select" name="city" class="form-select" required>
                                    <option value="">Chọn Tỉnh/TP</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Quận / Huyện</label>
                                <select id="edit-district-select" name="district" class="form-select">
                                    <option value="">Chọn Quận/Huyện</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Phường / Xã</label>
                                <select id="edit-ward-select" name="ward" class="form-select">
                                    <option value="">Chọn Phường/Xã</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Bảng tính lại phí ship và tổng tiền dự kiến --}}
                    <div class="p-3 bg-light rounded-3 border mt-3" id="edit-fee-preview-box">
                        <div class="d-flex justify-content-between text-secondary small mb-1">
                            <span>Tiền hàng:</span>
                            <span class="fw-semibold text-dark" id="edit-modal-subtotal">0đ</span>
                        </div>
                        <div class="d-flex justify-content-between text-secondary small mb-1" id="edit-modal-discount-row" style="display: none;">
                            <span>Khuyến mãi / Giảm giá:</span>
                            <span class="text-danger fw-semibold" id="edit-modal-discount">-0đ</span>
                        </div>
                        <div class="d-flex justify-content-between text-secondary small mb-1" id="edit-modal-points-row" style="display: none;">
                            <span>Điểm thưởng đã dùng:</span>
                            <span class="text-danger fw-semibold" id="edit-modal-points">-0đ</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center small mb-1">
                            <span class="text-secondary">Phí vận chuyển dự kiến:</span>
                            <span class="fw-bold fs-7 text-danger" id="edit-modal-shipping">30.000đ</span>
                        </div>
                        <div class="small fst-italic text-muted mb-2" id="edit-modal-shipping-note" style="font-size: 0.78rem;">
                            * Miễn phí ship cho Quận Ninh Kiều và Quận Cái Răng (Cần Thơ) hoặc đơn từ 1.000.000đ. Đổi sang khu vực/thành phố khác áp dụng phí ship 30.000đ.
                        </div>
                        <div class="border-top pt-2 d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark small">Tổng thanh toán dự kiến:</span>
                            <span class="fw-bold fs-6 text-danger" id="edit-modal-total">0đ</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" id="btn-submit-edit-address" class="btn btn-success rounded-pill px-4 fw-semibold">
                        <i class="bi bi-check2 me-1"></i>Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Popup Đổi phương thức thanh toán thành công (Ngắn gọn) --}}
@if(session('payment_switched_popup'))
<div class="modal fade" id="payment-switched-success-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow text-center p-3">
            <div class="modal-body pt-3 pb-2">
                <div class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center p-3 mb-2" style="width: 58px; height: 58px;">
                    <i class="bi bi-check-circle-fill fs-2 text-success"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">Đổi phương thức thành công</h5>
                <p class="text-secondary small mb-3">
                    Đơn hàng <strong>#{{ session('payment_switched_popup.order_code') }}</strong> đã chuyển sang thanh toán khi nhận hàng (COD).
                </p>
                <button type="button" class="btn btn-success rounded-pill px-4 py-2 fw-semibold w-100" data-bs-dismiss="modal">
                    Đã hiểu
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Popup Cập nhật địa chỉ nhận hàng thành công (Ngắn gọn) --}}
@if(session('address_updated_popup'))
<div class="modal fade" id="address-updated-success-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow text-center p-3">
            <div class="modal-body pt-3 pb-2">
                <div class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center p-3 mb-2" style="width: 58px; height: 58px;">
                    <i class="bi bi-geo-alt-fill fs-2 text-success"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">Cập nhật địa chỉ thành công</h5>
                <p class="text-secondary small mb-2">
                    Đơn hàng <strong>#{{ session('address_updated_popup.order_code') }}</strong> đã được cập nhật địa chỉ mới.
                </p>
                <div class="p-2.5 bg-light rounded-3 text-start small border mb-3">
                    <div><strong>Người nhận:</strong> {{ session('address_updated_popup.recipient_name') }} ({{ session('address_updated_popup.phone') }})</div>
                    <div><strong>Địa chỉ mới:</strong> {{ session('address_updated_popup.address') }}</div>
                    @if(isset(session('address_updated_popup')['shipping_fee']))
                        <div class="mt-2 pt-2 border-top d-flex justify-content-between">
                            <span class="text-secondary">Phí vận chuyển:</span>
                            <strong class="{{ session('address_updated_popup.shipping_fee') == 0 ? 'text-success' : 'text-danger' }}">
                                {{ session('address_updated_popup.shipping_fee') == 0 ? 'Miễn phí' : number_format(session('address_updated_popup.shipping_fee')) . 'đ' }}
                            </strong>
                        </div>
                    @endif
                    @if(isset(session('address_updated_popup')['total_amount']))
                        <div class="d-flex justify-content-between mt-1">
                            <span class="text-secondary">Tổng đơn hàng mới:</span>
                            <strong class="text-danger fs-6">{{ number_format(session('address_updated_popup.total_amount')) }}đ</strong>
                        </div>
                    @endif
                </div>
                <button type="button" class="btn btn-success rounded-pill px-4 py-2 fw-semibold w-100" data-bs-dismiss="modal">
                    Đã hiểu
                </button>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Tự động kích hoạt Popup khi có flash session đổi thanh toán hoặc đổi địa chỉ
        const switchSuccessModalEl = document.getElementById('payment-switched-success-modal');
        if (switchSuccessModalEl) {
            const m = new bootstrap.Modal(switchSuccessModalEl);
            m.show();
        }

        const addressSuccessModalEl = document.getElementById('address-updated-success-modal');
        if (addressSuccessModalEl) {
            const m = new bootstrap.Modal(addressSuccessModalEl);
            m.show();
        }

        // Switch COD logic qua Modal Popup
        const switchCodModalEl = document.getElementById('switch-cod-confirm-modal');
        const switchCodModal = switchCodModalEl ? new bootstrap.Modal(switchCodModalEl) : null;
        const switchCodOrderCodeEl = document.getElementById('switch-cod-order-code');
        const btnConfirmSwitchCod = document.getElementById('btn-confirm-switch-cod');
        let activeSwitchForm = null;

        document.querySelectorAll('.btn-switch-cod-trigger').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const formId = btn.dataset.formId;
                const orderCode = btn.dataset.orderCode;
                activeSwitchForm = document.getElementById(formId);
                if (switchCodOrderCodeEl) switchCodOrderCodeEl.textContent = '#' + orderCode;
                if (switchCodModal) switchCodModal.show();
            });
        });

        if (btnConfirmSwitchCod) {
            btnConfirmSwitchCod.addEventListener('click', () => {
                if (activeSwitchForm) {
                    btnConfirmSwitchCod.disabled = true;
                    btnConfirmSwitchCod.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Đang xử lý...';
                    activeSwitchForm.submit();
                }
            });
        }

        // Edit Address logic qua Modal Popup (Hỗ trợ địa chỉ lưu sẵn & API tỉnh thành)
        const editAddressModalEl = document.getElementById('edit-address-modal');
        const editAddressModal = editAddressModalEl ? new bootstrap.Modal(editAddressModalEl) : null;
        const editAddressForm = document.getElementById('edit-address-form');
        const editAddressOrderCodeEl = document.getElementById('edit-address-order-code');
        const modalRecipientName = document.getElementById('modal-recipient-name');
        const modalPhone = document.getElementById('modal-phone');
        const modalAddressLine = document.getElementById('modal-address-line');
        const editProvinceSelect = document.getElementById('edit-province-select');
        const editDistrictSelect = document.getElementById('edit-district-select');
        const editWardSelect = document.getElementById('edit-ward-select');
        const manualWrapper = document.getElementById('edit-address-manual-wrapper');
        const editAddrRadios = document.querySelectorAll('.edit-addr-radio');

        const PROV_API = 'https://provinces.open-api.vn/api';
        let editProvincesLoaded = false;

        async function loadEditProvinces() {
            if (editProvincesLoaded || !editProvinceSelect) return;
            try {
                const res = await fetch(`${PROV_API}/p/`);
                const data = await res.json();
                if (Array.isArray(data)) {
                    data.forEach(p => {
                        const opt = document.createElement('option');
                        opt.value = p.name;
                        opt.dataset.code = p.code;
                        opt.textContent = p.name;
                        editProvinceSelect.appendChild(opt);
                    });
                    editProvincesLoaded = true;
                }
            } catch (e) {
                console.error('Province API error:', e);
            }
        }

        let currentOrderSubtotal = 0;
        let currentOrderDiscount = 0;
        let currentOrderPointsDiscount = 0;

        function formatVnd(amount) {
            return new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
        }

        function isInnerCanThoCheck(city, district, fullAddress) {
            const cityNorm = (city || '').toLowerCase();
            const distNorm = (district || '').toLowerCase();
            const addrNorm = (fullAddress || '').toLowerCase();

            const isCanTho = cityNorm.includes('cần thơ') || addrNorm.includes('cần thơ');
            const isInner = distNorm.includes('ninh kiều') || distNorm.includes('cái răng') ||
                            addrNorm.includes('ninh kiều') || addrNorm.includes('cái răng');

            return isCanTho && isInner;
        }

        function updateEditModalShipping() {
            const activeRadio = document.querySelector('input.edit-addr-radio:checked');
            let city = '';
            let district = '';
            let fullAddress = '';

            if (activeRadio && activeRadio.value) {
                city = activeRadio.dataset.city || '';
                district = activeRadio.dataset.district || '';
                fullAddress = activeRadio.dataset.fullAddress || activeRadio.dataset.address || '';
            } else {
                city = editProvinceSelect?.selectedOptions[0]?.textContent || editProvinceSelect?.value || '';
                district = editDistrictSelect?.selectedOptions[0]?.textContent || editDistrictSelect?.value || '';
                fullAddress = modalAddressLine?.value || '';
            }

            let newShipping = 30000;
            let note = '* Đổi sang thành phố/khu vực khác ngoài Quận Ninh Kiều & Quận Cái Răng: tính lại phí ship 30.000đ.';

            if (currentOrderSubtotal >= 1000000) {
                newShipping = 0;
                note = '* Miễn phí ship cho đơn hàng từ 1.000.000đ.';
            } else if (isInnerCanThoCheck(city, district, fullAddress)) {
                newShipping = 0;
                note = '* Miễn phí ship cho khu vực Quận Ninh Kiều & Quận Cái Răng (Cần Thơ).';
            }

            const shippingEl = document.getElementById('edit-modal-shipping');
            const noteEl = document.getElementById('edit-modal-shipping-note');
            const totalEl = document.getElementById('edit-modal-total');

            if (shippingEl) {
                if (newShipping === 0) {
                    shippingEl.textContent = 'Miễn phí';
                    shippingEl.className = 'fw-bold fs-7 text-success';
                } else {
                    shippingEl.textContent = formatVnd(newShipping);
                    shippingEl.className = 'fw-bold fs-7 text-danger';
                }
            }

            if (noteEl) {
                noteEl.textContent = note;
            }

            const newTotal = Math.max(0, currentOrderSubtotal - currentOrderDiscount - currentOrderPointsDiscount + newShipping);
            if (totalEl) {
                totalEl.textContent = formatVnd(newTotal);
            }
        }

        async function loadEditDistricts(provinceCode) {
            if (!editDistrictSelect) return;
            editDistrictSelect.innerHTML = '<option value="">Chọn Quận/Huyện</option>';
            if (editWardSelect) editWardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
            try {
                const res = await fetch(`${PROV_API}/p/${provinceCode}?depth=2`);
                const data = await res.json();
                if (data && data.districts) {
                    data.districts.forEach(d => {
                        const opt = document.createElement('option');
                        opt.value = d.name;
                        opt.dataset.code = d.code;
                        opt.textContent = d.name;
                        editDistrictSelect.appendChild(opt);
                    });
                }
            } catch (e) {
                console.error(e);
            }
            updateEditModalShipping();
        }

        async function loadEditWards(districtCode) {
            if (!editWardSelect) return;
            editWardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
            try {
                const res = await fetch(`${PROV_API}/d/${districtCode}?depth=2`);
                const data = await res.json();
                if (data && data.wards) {
                    data.wards.forEach(w => {
                        const opt = document.createElement('option');
                        opt.value = w.name;
                        opt.dataset.code = w.code;
                        opt.textContent = w.name;
                        editWardSelect.appendChild(opt);
                    });
                }
            } catch (e) {
                console.error(e);
            }
        }

        if (editProvinceSelect) {
            editProvinceSelect.addEventListener('change', function(e) {
                const code = e.target.selectedOptions[0]?.dataset?.code;
                if (code) loadEditDistricts(code);
                else {
                    editDistrictSelect.innerHTML = '<option value="">Chọn Quận/Huyện</option>';
                    if (editWardSelect) editWardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
                }
                updateEditModalShipping();
            });
        }

        if (editDistrictSelect) {
            editDistrictSelect.addEventListener('change', function(e) {
                const code = e.target.selectedOptions[0]?.dataset?.code;
                if (code) loadEditWards(code);
                else if (editWardSelect) {
                    editWardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
                }
                updateEditModalShipping();
            });
        }

        if (modalAddressLine) {
            modalAddressLine.addEventListener('input', updateEditModalShipping);
        }

        function toggleEditAddressManualFields(isManual) {
            if (manualWrapper) {
                if (isManual) {
                    manualWrapper.classList.remove('d-none');
                    if (modalRecipientName) modalRecipientName.required = true;
                    if (modalPhone) modalPhone.required = true;
                    if (modalAddressLine) modalAddressLine.required = true;
                    if (editProvinceSelect) editProvinceSelect.required = true;
                } else {
                    manualWrapper.classList.add('d-none');
                    if (modalRecipientName) modalRecipientName.required = false;
                    if (modalPhone) modalPhone.required = false;
                    if (modalAddressLine) modalAddressLine.required = false;
                    if (editProvinceSelect) editProvinceSelect.required = false;
                }
            }
            updateEditModalShipping();
        }

        editAddrRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.edit-addr-card').forEach(card => {
                    card.classList.remove('border-success', 'bg-white', 'shadow-sm');
                    card.classList.add('bg-light');
                });
                const parentCard = this.closest('.edit-addr-card');
                if (parentCard) {
                    parentCard.classList.remove('bg-light');
                    parentCard.classList.add('border-success', 'bg-white', 'shadow-sm');
                }
                toggleEditAddressManualFields(!this.value);
            });
        });

        document.querySelectorAll('.btn-edit-address').forEach(btn => {
            btn.addEventListener('click', () => {
                const url = btn.dataset.actionUrl;
                const orderCode = btn.dataset.orderCode;
                const recipient = btn.dataset.recipient;
                const phone = btn.dataset.phone;
                const address = btn.dataset.address;

                currentOrderSubtotal = parseFloat(btn.dataset.subtotal) || 0;
                currentOrderDiscount = parseFloat(btn.dataset.discount) || 0;
                currentOrderPointsDiscount = parseFloat(btn.dataset.pointsDiscount) || 0;

                const subtotalEl = document.getElementById('edit-modal-subtotal');
                if (subtotalEl) subtotalEl.textContent = formatVnd(currentOrderSubtotal);

                const discountRow = document.getElementById('edit-modal-discount-row');
                const discountEl = document.getElementById('edit-modal-discount');
                if (discountRow && discountEl) {
                    if (currentOrderDiscount > 0) {
                        discountEl.textContent = '-' + formatVnd(currentOrderDiscount);
                        discountRow.style.removeProperty('display');
                    } else {
                        discountRow.style.setProperty('display', 'none', 'important');
                    }
                }

                const pointsRow = document.getElementById('edit-modal-points-row');
                const pointsEl = document.getElementById('edit-modal-points');
                if (pointsRow && pointsEl) {
                    if (currentOrderPointsDiscount > 0) {
                        pointsEl.textContent = '-' + formatVnd(currentOrderPointsDiscount);
                        pointsRow.style.removeProperty('display');
                    } else {
                        pointsRow.style.setProperty('display', 'none', 'important');
                    }
                }

                if (editAddressForm) editAddressForm.action = url;
                if (editAddressOrderCodeEl) editAddressOrderCodeEl.textContent = '#' + orderCode;
                if (modalRecipientName) modalRecipientName.value = recipient || '';
                if (modalPhone) modalPhone.value = phone || '';
                if (modalAddressLine) modalAddressLine.value = address || '';

                loadEditProvinces();

                // Mặc định chọn nhập mới
                const newRadio = document.getElementById('edit-addr-new');
                if (newRadio) {
                    newRadio.checked = true;
                    newRadio.dispatchEvent(new Event('change'));
                } else {
                    updateEditModalShipping();
                }

                if (editAddressModal) editAddressModal.show();
            });
        });
        const cancelModalEl = document.getElementById('cancel-modal');
        const cancelModal = new bootstrap.Modal(cancelModalEl);
        const modalMsg = document.getElementById('cancel-modal-msg');
        const btnConfirm = document.getElementById('btn-confirm-cancel');
        let currentForm = null;

        document.querySelectorAll('.btn-cancel-order').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                currentForm = btn.closest('form');
                
                if (btn.dataset.isPaid === 'true') {
                    modalMsg.textContent = 'Bạn có chắc chắn muốn hủy đơn hàng này không? Số tiền đã thanh toán sẽ được hệ thống xử lý hoàn lại trong 1-3 ngày làm việc.';
                    btnConfirm.textContent = 'Hủy & Hoàn tiền';
                } else {
                    modalMsg.textContent = 'Bạn có chắc chắn muốn hủy đơn hàng này không? Quá trình này không thể hoàn tác.';
                    btnConfirm.textContent = 'Xác nhận Hủy';
                }
                
                cancelModal.show();
            });
        });

        btnConfirm.addEventListener('click', () => {
            if (currentForm) {
                btnConfirm.disabled = true;
                btnConfirm.textContent = 'Đang xử lý...';
                currentForm.submit();
            }
        });

        // Review Modal Logic
        const reviewModalEl = document.getElementById('review-modal');
        const reviewModal = new bootstrap.Modal(reviewModalEl);
        const starBtns = document.querySelectorAll('.star');
        const ratingInput = document.getElementById('review-rating');
        const reviewProductId = document.getElementById('review-product-id');
        const reviewOrderId = document.getElementById('review-order-id');
        const commentInput = document.getElementById('review-comment');

        document.querySelectorAll('.btn-review').forEach(btn => {
            btn.addEventListener('click', () => {
                reviewProductId.value = btn.dataset.productId;
                reviewOrderId.value = btn.dataset.orderId;
                
                const currentRating = parseInt(btn.dataset.rating) || 5;
                const currentComment = btn.dataset.comment || '';
                
                starBtns.forEach(b => {
                    if (parseInt(b.dataset.val) <= currentRating) {
                        b.classList.add('text-warning');
                        b.classList.remove('text-secondary-subtle');
                    } else {
                        b.classList.remove('text-warning');
                        b.classList.add('text-secondary-subtle');
                    }
                });
                ratingInput.value = currentRating;
                commentInput.value = currentComment;

                reviewModal.show();
            });
        });

        starBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const val = parseInt(btn.dataset.val);
                ratingInput.value = val;
                starBtns.forEach(b => {
                    if (parseInt(b.dataset.val) <= val) {
                        b.classList.add('text-warning');
                        b.classList.remove('text-secondary-subtle');
                    } else {
                        b.classList.remove('text-warning');
                        b.classList.add('text-secondary-subtle');
                    }
                });
            });
        });

        // Tự động cuộn đến đơn hàng cần thanh toán nếu có param repay
        const urlParams = new URLSearchParams(window.location.search);
        const repayId = urlParams.get('repay');
        if (repayId) {
            const targetEl = document.getElementById('order-card-' + repayId);
            if (targetEl) {
                targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
</script>
@endpush
@endsection
