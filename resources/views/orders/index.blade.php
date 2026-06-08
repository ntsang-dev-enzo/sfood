@extends('layouts.app')
@section('title', 'Đơn hàng - SFood')
@section('content')
<div class="bg-earth-800 text-cream-50 py-12">
    <div class="max-w-4xl mx-auto px-4">
        <h1 class="text-3xl font-bold">Đơn hàng của tôi</h1>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 py-8 space-y-4">
    @forelse ($orders as $order)
        <div class="card p-6">
            <div class="flex flex-wrap justify-between items-start gap-2 mb-4">
                <div>
                    <p class="font-bold text-lg text-earth-800">#{{ $order->order_code }}</p>
                    <p class="text-sm text-earth-500">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div class="text-right">
                    <span class="badge {{ $order->order_status === 'delivered' ? 'bg-leaf-100 text-leaf-800' : 'bg-earth-100 text-earth-700' }}">
                        {{ ucfirst($order->order_status) }}
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
        </div>
    @empty
        <div class="card p-12 text-center">
            <p class="text-earth-500">Chưa có đơn hàng nào.</p>
            <a href="{{ route('products.index') }}" class="btn-primary mt-4 inline-flex">Mua sắm ngay</a>
        </div>
    @endforelse
</div>
@endsection
