@extends('layouts.app')
@section('title', 'Nhận mã giảm giá & phí ship')
@section('content')
<div class="max-w-3xl mx-auto px-4 py-12">
    <h1 class="text-2xl font-bold mb-4">Nhận mã giảm giá & điều chỉnh phí ship</h1>

    @if(session('success'))
        <div class="p-3 bg-green-50 border border-green-200 text-green-700 mb-4">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('promo.store') }}">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Mã giảm giá (coupon)</label>
            <input name="coupon_code" value="{{ old('coupon_code', session('promo.coupon_code')) }}" class="input-field" placeholder="Nhập mã bạn nhận được">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Phí ship (áp dụng tạm vào giỏ hàng)</label>
            <input name="shipping_fee" type="number" step="1000" value="{{ old('shipping_fee', session('promo.shipping_fee', '')) }}" class="input-field" placeholder="Nhập phí ship (VND)">
            <p class="text-xs text-earth-500 mt-1">Phí ship này sẽ được dùng tạm khi tính tổng trong phiên làm việc.</p>
        </div>
        <button class="btn-primary">Lưu</button>
    </form>
</div>
@endsection
