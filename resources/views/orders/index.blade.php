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
    @if(session('success'))
        <div class="alert alert-success rounded-3 border-0 shadow-sm mb-3">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger rounded-3 border-0 shadow-sm mb-3">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        </div>
    @endif

    {{-- Order List --}}
    @forelse ($orders as $order)
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
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
                    @endif
                    <div class="fs-5 fw-bold text-danger mt-1">{{ number_format($order->total_amount) }}đ</div>
                </div>
            </div>

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
                <small class="text-muted"><i class="bi bi-geo-alt me-1 text-danger"></i>Giao đến: {{ $order->shipping_address['address'] ?? '' }}</small>

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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
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
    });
</script>
@endpush
@endsection
