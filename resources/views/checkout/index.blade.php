@extends('layouts.app')
@section('title', 'Thanh toán - SFood')
@section('content')
<div class="bg-earth-800 text-cream-50 py-12">
    <div class="max-w-4xl mx-auto px-4">
        <h1 class="text-3xl font-bold">Thanh toán</h1>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 py-8">
    <form method="POST" action="{{ route('checkout.store') }}">
        @csrf
        <div class="grid md:grid-cols-2 gap-6">
            <div class="card p-6">
                <h2 class="font-bold text-lg text-earth-800 mb-4">📍 Địa chỉ giao hàng</h2>

                @if ($addresses->count())
                    <div class="space-y-2 mb-4">
                        @foreach ($addresses as $addr)
                            <label class="flex items-start gap-3 p-4 border border-cream-300 rounded-xl cursor-pointer hover:border-leaf-500 transition has-[:checked]:border-leaf-600 has-[:checked]:bg-leaf-50">
                                <input type="radio" name="shipping_address_id" value="{{ $addr->id }}" class="mt-1 accent-leaf-600"
                                    @checked(old('shipping_address_id', $addresses->where('is_default', true)->first()?->id) == $addr->id)>
                                <div>
                                    <p class="font-medium text-earth-800">{{ $addr->recipient_name }} — {{ $addr->phone }}</p>
                                    <p class="text-sm text-earth-500">{{ $addr->fullAddress() }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <p class="text-sm text-earth-500 mb-3">Hoặc nhập địa chỉ mới:</p>
                @endif

                <div class="space-y-3">
                    <input name="recipient_name" value="{{ old('recipient_name') }}" placeholder="Họ tên người nhận" class="input-field">
                    <input name="phone" value="{{ old('phone') }}" placeholder="Số điện thoại" class="input-field">
                    <input name="address_line" value="{{ old('address_line') }}" placeholder="Địa chỉ" class="input-field">
                    <div class="grid grid-cols-3 gap-2">
                        <input name="ward" value="{{ old('ward') }}" placeholder="Phường/Xã" class="input-field">
                        <input name="district" value="{{ old('district') }}" placeholder="Quận/Huyện" class="input-field">
                        <input name="city" value="{{ old('city') }}" placeholder="Tỉnh/TP" class="input-field">
                    </div>
                    <textarea name="notes" placeholder="Ghi chú đơn hàng" class="input-field" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="card p-6">
                <h2 class="font-bold text-lg text-earth-800 mb-4">💳 Thanh toán</h2>

                <div class="space-y-2 text-sm mb-5 p-4 bg-cream-100 rounded-xl">
                    <div class="flex justify-between text-earth-700"><span>Tạm tính</span><span>{{ number_format($subtotal) }}đ</span></div>
                    <div class="flex justify-between text-earth-700"><span>Phí giao hàng</span><span>{{ $shipping == 0 ? 'Miễn phí' : number_format($shipping).'đ' }}</span></div>
                    @if ($subtotal < 300000)
                        <p class="text-xs text-earth-500">Miễn phí ship đơn từ 300.000đ</p>
                    @endif
                    <div class="flex justify-between font-bold text-lg text-earth-800 pt-2 border-t border-cream-300">
                        <span>Tổng</span><span class="text-leaf-700">{{ number_format($total) }}đ</span>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="text-sm font-medium text-earth-700">Mã giảm giá</label>
                    <input name="coupon_code" value="{{ old('coupon_code') }}" placeholder="VD: SFOOD10" class="input-field mt-1">
                </div>

                <div class="space-y-2 mb-6">
                    <label class="text-sm font-medium text-earth-700">Phương thức</label>
                    @foreach (['cod' => 'COD — Thanh toán khi nhận', 'payos' => 'PayOS (QR / Ngân hàng)', 'vnpay' => 'VNPay', 'momo' => 'MoMo'] as $val => $label)
                        <label class="flex items-center gap-2 p-3 border border-cream-300 rounded-xl cursor-pointer hover:border-leaf-500 has-[:checked]:border-leaf-600 has-[:checked]:bg-leaf-50">
                            <input type="radio" name="payment_gateway" value="{{ $val }}" class="accent-leaf-600" @checked(old('payment_gateway', 'cod') === $val)>
                            <span class="text-sm text-earth-800">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>

                <button type="submit" class="btn-primary w-full text-lg">Đặt hàng ngay</button>
            </div>
        </div>
    </form>
</div>
@endsection
