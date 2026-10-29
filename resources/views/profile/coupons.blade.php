@extends('layouts.profile')
@section('profile_content')
<div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <h5 class="fw-bold text-dark mb-4 pb-2 border-bottom d-flex align-items-center justify-content-between">
        <span><i class="bi bi-ticket-perforated-fill text-danger me-2"></i>Ví Voucher của bạn</span>
        <a href="{{ route('vouchers.index') }}" class="btn btn-sm btn-outline-success rounded-pill px-3">
            <i class="bi bi-plus-lg me-1"></i>Săn thêm voucher
        </a>
    </h5>

    @if ($coupons->count())
        <div class="row row-cols-1 row-cols-md-2 g-3">
            @foreach ($coupons as $coupon)
                @php
                    $isUsed = $coupon->pivot->is_used || ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit);
                    $isExpired = $coupon->expires_at && $coupon->expires_at->isPast();
                    $isDisabled = $isUsed || $isExpired || !$coupon->is_active;
                @endphp
                <div class="col">
                    <div class="card h-100 border rounded-4 p-3 {{ $isDisabled ? 'bg-light text-muted opacity-75' : 'bg-white border-success-subtle shadow-sm' }}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge {{ $isDisabled ? 'bg-secondary' : 'bg-danger' }} rounded-pill px-3 py-1.5 small">
                                @if ($coupon->type === 'percent') Giảm {{ $coupon->value }}%
                                @elseif ($coupon->type === 'fixed') Giảm {{ number_format($coupon->value) }}đ
                                @else Miễn phí vận chuyển
                                @endif
                            </span>
                            @if($isUsed)
                                <span class="badge bg-secondary-subtle text-secondary small">Đã dùng</span>
                            @elseif($isExpired)
                                <span class="badge bg-danger-subtle text-danger small">Hết hạn</span>
                            @endif
                        </div>
                        <div class="font-monospace fw-bold fs-4 text-dark mb-1">{{ $coupon->code }}</div>
                        <div class="small text-muted mb-3">
                            @if ($coupon->min_order_amount > 0)
                                <div><i class="bi bi-cart-check me-1"></i>Đơn tối thiểu: {{ number_format($coupon->min_order_amount) }}đ</div>
                            @endif
                            @if ($coupon->expires_at)
                                <div><i class="bi bi-clock me-1"></i>Hạn dùng: {{ $coupon->expires_at->format('d/m/Y H:i') }}</div>
                            @endif
                        </div>
                        <div class="mt-auto pt-2">
                            <button type="button" 
                                    onclick="navigator.clipboard.writeText('{{ $coupon->code }}'); alert('Đã sao chép mã ưu đãi: {{ $coupon->code }}')" 
                                    class="btn btn-sm {{ $isDisabled ? 'btn-light border disabled' : 'btn-outline-success' }} w-100 rounded-pill py-2 fw-semibold">
                                <i class="bi bi-clipboard me-1"></i> Sao chép mã
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-5">
            <i class="bi bi-ticket-perforated display-3 text-muted opacity-50 mb-3"></i>
            <h5 class="fw-bold text-dark">Ví voucher đang trống</h5>
            <p class="text-muted small">Hãy thu thập các mã giảm giá hấp dẫn để tiết kiệm hơn khi mua sắm.</p>
            <div class="mt-3">
                <a href="{{ route('vouchers.index') }}" class="btn btn-success rounded-pill px-4 py-2 fw-semibold">
                    <i class="bi bi-gift me-1"></i> Thu thập Voucher ngay
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
