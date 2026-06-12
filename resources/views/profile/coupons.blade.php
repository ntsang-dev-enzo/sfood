@extends('layouts.profile')
@section('profile_content')
<div class="space-y-6">
    <div class="card p-6">
        <h2 class="font-bold text-lg text-earth-800 mb-4">Ví Coupon của bạn</h2>

        @if ($coupons->count())
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($coupons as $coupon)
                    @php
                        $isUsed = $coupon->pivot->is_used || ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit);
                        $isExpired = $coupon->expires_at && $coupon->expires_at->isPast();
                        $isDisabled = $isUsed || $isExpired || !$coupon->is_active;
                    @endphp
                    <div class="border rounded-xl p-4 flex flex-col justify-between {{ $isDisabled ? 'bg-cream-100 opacity-70 grayscale' : 'bg-white border-leaf-200' }}">
                        <div>
                            <div class="flex justify-between items-start mb-2">
                                <span class="badge {{ $isDisabled ? 'bg-earth-200 text-earth-600' : 'bg-leaf-100 text-leaf-800' }}">
                                    @if ($coupon->type === 'percent') Giảm {{ $coupon->value }}%
                                    @elseif ($coupon->type === 'fixed') Giảm {{ number_format($coupon->value) }}đ
                                    @else Miễn phí vận chuyển
                                    @endif
                                </span>
                                @if($isUsed)
                                    <span class="text-xs font-bold text-red-600">Đã sử dụng</span>
                                @elseif($isExpired)
                                    <span class="text-xs font-bold text-earth-500">Đã hết hạn</span>
                                @endif
                            </div>
                            <p class="font-mono font-bold text-xl text-earth-800 mb-1">{{ $coupon->code }}</p>
                            @if ($coupon->min_order_amount > 0)
                                <p class="text-xs text-earth-600">Đơn tối thiểu: {{ number_format($coupon->min_order_amount) }}đ</p>
                            @endif
                            @if ($coupon->expires_at)
                                <p class="text-xs text-earth-600">HSD: {{ $coupon->expires_at->format('d/m/Y H:i') }}</p>
                            @endif
                        </div>
                        <div class="mt-4 flex gap-2">
                            <button onclick="navigator.clipboard.writeText('{{ $coupon->code }}'); alert('Đã sao chép mã: {{ $coupon->code }}')" class="btn-secondary text-sm flex-1" {{ $isDisabled ? 'disabled' : '' }}>
                                Sao chép mã
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <div class="text-4xl mb-4">🎟️</div>
                <p class="text-earth-600 mb-4">Bạn chưa có mã giảm giá nào trong ví.</p>
                <a href="{{ route('vouchers.index') }}" class="btn-primary inline-flex">Thu thập ngay</a>
            </div>
        @endif
    </div>
</div>
@endsection
