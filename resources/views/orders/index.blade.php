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
            <ul class="text-sm space-y-1 text-earth-600 bg-cream-100 rounded-xl p-4">
                @foreach ($order->items as $item)
                    <li>{{ $item->product_name }} ({{ $item->variant_name }}) × {{ $item->quantity }}</li>
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
    });
</script>
@endpush
@endsection
