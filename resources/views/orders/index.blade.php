@extends('layouts.app')
@section('title', 'Đơn hàng - SFood')
@section('content')
<div class="bg-earth-800 text-cream-50 py-12">
    <div class="max-w-4xl mx-auto px-4">
        <h1 class="text-3xl font-bold">Đơn hàng của tôi</h1>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 py-8 space-y-4">
    <div class="flex gap-2 overflow-x-auto pb-2 mb-4 scrollbar-hide">
        <a href="{{ route('orders.index') }}" class="whitespace-nowrap px-4 py-2 rounded-lg text-sm font-medium {{ !request('status') ? 'bg-leaf-600 text-white' : 'bg-cream-200 text-earth-700 hover:bg-cream-300' }}">Tất cả</a>
        <a href="{{ route('orders.index', ['status' => 'pending']) }}" class="whitespace-nowrap px-4 py-2 rounded-lg text-sm font-medium {{ request('status') == 'pending' ? 'bg-leaf-600 text-white' : 'bg-cream-200 text-earth-700 hover:bg-cream-300' }}">Chờ xử lý</a>
        <a href="{{ route('orders.index', ['status' => 'confirmed']) }}" class="whitespace-nowrap px-4 py-2 rounded-lg text-sm font-medium {{ request('status') == 'confirmed' ? 'bg-leaf-600 text-white' : 'bg-cream-200 text-earth-700 hover:bg-cream-300' }}">Đã xác nhận</a>
        <a href="{{ route('orders.index', ['status' => 'shipping']) }}" class="whitespace-nowrap px-4 py-2 rounded-lg text-sm font-medium {{ request('status') == 'shipping' ? 'bg-leaf-600 text-white' : 'bg-cream-200 text-earth-700 hover:bg-cream-300' }}">Đang giao</a>
        <a href="{{ route('orders.index', ['status' => 'delivered']) }}" class="whitespace-nowrap px-4 py-2 rounded-lg text-sm font-medium {{ request('status') == 'delivered' ? 'bg-leaf-600 text-white' : 'bg-cream-200 text-earth-700 hover:bg-cream-300' }}">Đã giao</a>
        <a href="{{ route('orders.index', ['status' => 'cancelled']) }}" class="whitespace-nowrap px-4 py-2 rounded-lg text-sm font-medium {{ request('status') == 'cancelled' ? 'bg-leaf-600 text-white' : 'bg-cream-200 text-earth-700 hover:bg-cream-300' }}">Đã hủy</a>
    </div>

    @if(session('success'))
        <div class="bg-leaf-100 text-leaf-800 p-4 rounded-xl mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 text-red-800 p-4 rounded-xl mb-4">
            {{ session('error') }}
        </div>
    @endif
    @forelse ($orders as $order)
        <div class="card p-6">
            <div class="flex flex-wrap justify-between items-start gap-2 mb-4">
                <div>
                    <p class="font-bold text-lg text-earth-800">#{{ $order->order_code }}</p>
                    <p class="text-sm text-earth-500">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div class="text-right">
                    <span class="badge {{ $order->order_status === 'delivered' ? 'bg-leaf-100 text-leaf-800' : 'bg-earth-100 text-earth-700' }}">
                        @php
                            $statusLabels = [
                                'pending' => 'Chờ xử lý',
                                'confirmed' => 'Đã xác nhận',
                                'shipping' => 'Đang giao',
                                'delivered' => 'Đã giao',
                                'cancelled' => 'Đã hủy',
                            ];
                        @endphp
                        {{ $statusLabels[$order->order_status] ?? ucfirst($order->order_status) }}
                    </span>
                    <p class="font-bold text-leaf-700 text-lg mt-1">{{ number_format($order->total_amount) }}đ</p>
                </div>
            </div>
            <ul class="text-sm space-y-2 text-earth-600 bg-cream-100 rounded-xl p-4">
                @foreach ($order->items as $item)
                    <li class="flex flex-col sm:flex-row justify-between sm:items-center gap-2 border-b border-cream-200 last:border-0 pb-2 last:pb-0">
                        <span>{{ $item->product_name }} ({{ $item->variant_name }}) × {{ $item->quantity }}</span>
                        @if($order->order_status === 'delivered')
                            @php
                                $productId = $item->productVariant->product_id;
                                $existingReview = \App\Models\ProductReview::where('user_id', auth()->id())->where('product_id', $productId)->first();
                            @endphp
                            @if($existingReview)
                                <button type="button" class="btn-review text-leaf-600 text-xs font-bold hover:underline bg-white px-3 py-1 rounded-full border border-leaf-200" data-product-id="{{ $productId }}" data-order-id="{{ $order->id }}" data-rating="{{ $existingReview->rating }}" data-comment="{{ $existingReview->comment }}">Sửa đánh giá</button>
                            @else
                                <button type="button" class="btn-review text-leaf-600 text-xs font-bold hover:underline bg-white px-3 py-1 rounded-full border border-leaf-200" data-product-id="{{ $productId }}" data-order-id="{{ $order->id }}" data-rating="5" data-comment="">⭐ Đánh giá</button>
                            @endif
                        @endif
                    </li>
                @endforeach
            </ul>
            <p class="text-sm text-earth-500 mt-3">📍 {{ $order->shipping_address['address'] ?? '' }}</p>
            @if(in_array($order->order_status, ['pending', 'confirmed']))
                <div class="mt-4 flex justify-end">
                    <form action="{{ route('orders.cancel', $order) }}" method="POST">
                        @csrf
                        <button type="button" class="btn-cancel-order btn-secondary text-red-600 border-red-200 hover:bg-red-50 py-1 px-3 text-sm" data-is-paid="{{ $order->payment_status === 'paid' ? 'true' : 'false' }}">
                            {{ $order->payment_status === 'paid' ? 'Hủy đơn & Hoàn tiền' : 'Hủy đơn hàng' }}
                        </button>
                    </form>
                </div>
            @endif
        </div>
    @empty
        <div class="card p-12 text-center">
            <p class="text-earth-500">Chưa có đơn hàng nào.</p>
            <a href="{{ route('products.index') }}" class="btn-primary mt-4 inline-flex">Mua sắm ngay</a>
        </div>
    @endforelse
</div>

{{-- Cancel Modal --}}
<div id="cancel-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-earth-900/50 backdrop-blur-sm">
    <div class="bg-cream-50 p-6 rounded-2xl shadow-xl max-w-sm w-full mx-4">
        <h3 class="text-xl font-bold text-earth-800 mb-2">Xác nhận hủy đơn</h3>
        <p id="cancel-modal-msg" class="text-earth-600 mb-6">Bạn có chắc chắn muốn hủy đơn hàng này không? Quá trình này không thể hoàn tác.</p>
        <div class="flex justify-end gap-3">
            <button type="button" id="btn-close-modal" class="px-4 py-2 text-earth-600 hover:bg-cream-200 rounded-lg font-medium transition">Đóng lại</button>
            <button type="button" id="btn-confirm-cancel" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition">Xác nhận Hủy</button>
        </div>
    </div>
</div>

{{-- Review Modal --}}
<div id="review-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-earth-900/50 backdrop-blur-sm p-4">
    <div class="bg-cream-50 p-6 rounded-2xl shadow-xl max-w-md w-full relative">
        <button type="button" id="btn-close-review" class="absolute top-4 right-4 text-earth-500 hover:text-earth-800 text-2xl leading-none">&times;</button>
        <h3 class="text-xl font-bold text-earth-800 mb-4">Đánh giá sản phẩm</h3>
        
        <form action="{{ route('reviews.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="product_id" id="review-product-id">
            <input type="hidden" name="order_id" id="review-order-id">
            
            <div class="mb-4">
                <label class="block text-sm font-bold text-earth-800 mb-2">Chất lượng sản phẩm (1-5 sao)</label>
                <div class="flex gap-2 text-2xl text-gray-300" id="star-rating">
                    <button type="button" data-val="1" class="star hover:text-yellow-400 transition text-yellow-400">★</button>
                    <button type="button" data-val="2" class="star hover:text-yellow-400 transition text-yellow-400">★</button>
                    <button type="button" data-val="3" class="star hover:text-yellow-400 transition text-yellow-400">★</button>
                    <button type="button" data-val="4" class="star hover:text-yellow-400 transition text-yellow-400">★</button>
                    <button type="button" data-val="5" class="star hover:text-yellow-400 transition text-yellow-400">★</button>
                </div>
                <input type="hidden" name="rating" id="review-rating" value="5">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-bold text-earth-800 mb-2">Bình luận</label>
                <textarea name="comment" id="review-comment" rows="3" class="w-full rounded-xl border-cream-200 focus:border-leaf-500 focus:ring focus:ring-leaf-200 bg-white" placeholder="Sản phẩm rất tuyệt vời..."></textarea>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-bold text-earth-800 mb-2">Hình ảnh / Video (Tối đa 5MB ảnh, 20MB video)</label>
                <input type="file" name="attachments[]" multiple accept="image/*,video/*" class="block w-full text-sm text-earth-600
                  file:mr-4 file:py-2 file:px-4
                  file:rounded-full file:border-0
                  file:text-sm file:font-semibold
                  file:bg-leaf-50 file:text-leaf-700
                  hover:file:bg-leaf-100 transition
                "/>
            </div>

            <button type="submit" class="w-full btn-primary rounded-xl py-3">Gửi đánh giá</button>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('cancel-modal');
        const modalMsg = document.getElementById('cancel-modal-msg');
        const btnClose = document.getElementById('btn-close-modal');
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
                
                modal.classList.remove('hidden');
            });
        });

        const closeModal = () => {
            modal.classList.add('hidden');
            currentForm = null;
        };

        btnClose.addEventListener('click', closeModal);
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });

        btnConfirm.addEventListener('click', () => {
            if (currentForm) {
                // Prevent multiple clicks
                btnConfirm.disabled = true;
                btnConfirm.textContent = 'Đang xử lý...';
                currentForm.submit();
            }
        });

        // Review Modal Logic
        const reviewModal = document.getElementById('review-modal');
        const btnCloseReview = document.getElementById('btn-close-review');
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
                
                // Reset stars
                starBtns.forEach(b => {
                    if (parseInt(b.dataset.val) <= currentRating) {
                        b.classList.add('text-yellow-400');
                        b.classList.remove('text-gray-300');
                    } else {
                        b.classList.remove('text-yellow-400');
                        b.classList.add('text-gray-300');
                    }
                });
                ratingInput.value = currentRating;
                commentInput.value = currentComment;

                reviewModal.classList.remove('hidden');
            });
        });

        btnCloseReview.addEventListener('click', () => {
            reviewModal.classList.add('hidden');
        });

        reviewModal.addEventListener('click', (e) => {
            if (e.target === reviewModal) reviewModal.classList.add('hidden');
        });

        starBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const val = parseInt(btn.dataset.val);
                ratingInput.value = val;
                starBtns.forEach(b => {
                    if (parseInt(b.dataset.val) <= val) {
                        b.classList.add('text-yellow-400');
                        b.classList.remove('text-gray-300');
                    } else {
                        b.classList.remove('text-yellow-400');
                        b.classList.add('text-gray-300');
                    }
                });
            });
        });
    });
</script>
@endpush
@endsection
