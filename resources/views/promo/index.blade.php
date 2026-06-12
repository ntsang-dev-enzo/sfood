@extends('layouts.app')
@section('title', 'Thu thập mã giảm giá')
@section('content')
<div class="bg-gradient-to-r from-orange-100 to-yellow-50 py-12">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <h1 class="text-4xl font-bold text-earth-800 mb-2">🎁 Mã Giảm Giá Đặc Biệt</h1>
        <p class="text-earth-600">Thu thập các mã giảm giá bên dưới để sử dụng cho đơn hàng của bạn</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 py-8">
    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-4 rounded-xl mb-6 shadow-sm border border-green-200">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 text-red-800 p-4 rounded-xl mb-6 shadow-sm border border-red-200">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($coupons as $coupon)
            @php
                $isSaved = $userCoupons->contains(function ($userCoupon) use ($coupon) {
                    return str_starts_with($userCoupon->code, $coupon->code . '_');
                });
                $isExpired = $coupon->expires_at && $coupon->expires_at->isPast();
                $isDisabled = $isSaved || $isExpired;
            @endphp
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border dark:border-gray-700 shadow-sm flex flex-col relative overflow-hidden {{ $isDisabled ? 'border-gray-200 opacity-80' : 'border-orange-200 hover:shadow-md transition' }}">
                <div class="absolute -right-12 -top-12 w-32 h-32 bg-orange-100 rounded-full opacity-50 pointer-events-none"></div>
                <div class="flex justify-between items-start mb-4 relative z-10">
                    <span class="badge {{ $isDisabled ? 'bg-gray-100 text-gray-600' : 'bg-orange-100 text-orange-800' }} font-bold text-sm">
                        @if ($coupon->type === 'percent') Giảm {{ $coupon->value }}%
                        @elseif ($coupon->type === 'fixed') Giảm {{ number_format($coupon->value) }}đ
                        @else Miễn phí vận chuyển
                        @endif
                    </span>
                    @if($isSaved)
                        <span class="text-xs font-bold text-leaf-600 bg-leaf-50 px-2 py-1 rounded-full">Đã lưu</span>
                    @elseif($isExpired)
                        <span class="text-xs font-bold text-gray-500">Đã hết hạn</span>
                    @endif
                </div>
                
                <div class="relative z-10 flex-1">
                    <h3 class="font-bold text-lg text-earth-800 mb-2">{{ $coupon->code }}</h3>
                    <ul class="text-sm text-earth-600 space-y-1 mb-4">
                        @if ($coupon->min_order_amount > 0)
                            <li>• Đơn tối thiểu: <strong>{{ number_format($coupon->min_order_amount) }}đ</strong></li>
                        @endif
                        @if ($coupon->expires_at)
                            <li>• HSD: {{ $coupon->expires_at->format('d/m/Y H:i') }}</li>
                        @endif
                    </ul>
                </div>

                <div class="mt-auto relative z-10 pt-4 border-t border-gray-100">
                    @if($isSaved)
                        <a href="{{ route('profile.coupons') }}" class="btn-secondary w-full text-center block bg-gray-50 border-gray-200 text-gray-600">Xem trong ví</a>
                    @elseif($isExpired)
                        <button disabled class="btn-secondary w-full opacity-50 cursor-not-allowed text-center">Đã hết hạn</button>
                    @else
                        <form action="{{ route('promo.save', $coupon) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-primary w-full shadow-sm bg-orange-500 hover:bg-orange-600 border-none text-white">Lưu mã</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <span class="text-4xl mb-4 block">🎫</span>
                <h3 class="text-xl font-bold text-earth-800">Chưa có mã giảm giá nào</h3>
                <p class="text-earth-600 mt-2">Hãy quay lại sau để nhận các ưu đãi hấp dẫn nhé!</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
