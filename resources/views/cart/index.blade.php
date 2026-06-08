@extends('layouts.app')
@section('title', 'Giỏ hàng - SFood')
@section('content')
@php use App\Support\MediaHelper; @endphp
<div class="bg-earth-800 text-cream-50 py-12">
    <div class="max-w-4xl mx-auto px-4 flex justify-between items-center">
        <h1 class="text-3xl font-bold">Giỏ hàng</h1>
        @if (!$items->isEmpty())
            <button type="button" id="clear-cart-btn" class="text-sm text-cream-300 hover:text-white border border-cream-400/30 px-4 py-2 rounded-lg transition">
                Xóa tất cả
            </button>
        @endif
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 py-8" id="cart-page"
    data-update-url="{{ route('cart.update') }}"
    data-remove-url="{{ route('cart.remove') }}"
    data-clear-url="{{ route('cart.clear') }}">
    @if ($items->isEmpty())
        <div class="card p-16 text-center" id="cart-empty">
            <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?w=300&q=80" class="w-40 h-40 mx-auto rounded-2xl object-cover mb-6 opacity-80" alt="">
            <p class="text-earth-600 text-lg mb-6">Giỏ hàng của bạn đang trống</p>
            <a href="{{ route('products.index') }}" class="btn-primary">Mua sắm ngay</a>
        </div>
    @else
        <div class="card divide-y divide-cream-300" id="cart-items">
            @foreach ($items as $item)
                @php
                    $v = $item->productVariant;
                    $product = $v->product;
                @endphp
                <div class="flex items-center gap-4 p-5 cart-row" data-item-id="{{ $item->id }}" data-unit-price="{{ $v->effectivePrice() }}">
                    <a href="{{ route('products.show', $product) }}" class="w-20 h-20 bg-cream-200 rounded-xl flex-shrink-0 overflow-hidden hover:opacity-90 transition">
                        <img src="{{ MediaHelper::productImage($v->image ?? $product->thumbnail, $product->id) }}" class="w-full h-full object-cover" alt="">
                    </a>
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('products.show', $product) }}" class="font-semibold text-earth-800 hover:text-leaf-700 transition line-clamp-1">
                            {{ $product->name }}
                        </a>
                        <p class="text-sm text-earth-500">{{ $v->name }}</p>
                        <p class="text-leaf-700 font-bold mt-1 item-subtotal">{{ number_format($item->subtotal()) }}đ</p>
                    </div>
                    <div class="flex items-center border border-cream-300 rounded-xl overflow-hidden bg-cream-50">
                        <button type="button" class="qty-minus w-9 h-9 flex items-center justify-center text-earth-700 hover:bg-cream-200 transition font-bold">−</button>
                        <span class="qty-value w-10 text-center text-sm font-semibold text-earth-800">{{ $item->quantity }}</span>
                        <button type="button" class="qty-plus w-9 h-9 flex items-center justify-center text-earth-700 hover:bg-cream-200 transition font-bold">+</button>
                    </div>
                    <button type="button" class="remove-item text-red-500 hover:text-red-700 text-sm p-2" title="Xóa">✕</button>
                </div>
            @endforeach
        </div>

        <div class="card p-6 mt-6 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div>
                <p class="text-earth-500 text-sm">Tạm tính</p>
                <p class="text-3xl font-bold text-leaf-700" id="cart-subtotal">{{ number_format($subtotal) }}đ</p>
            </div>
            @auth
                <a href="{{ route('checkout') }}" class="btn-primary !px-10">Thanh toán</a>
            @else
                <a href="{{ route('login') }}" class="btn-primary !px-10">Đăng nhập để thanh toán</a>
            @endauth
        </div>
    @endif
</div>
@endsection
