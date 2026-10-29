@extends('layouts.app')
@section('title', 'Thu thập mã giảm giá - SFood')

@section('content')
<div class="bg-body-tertiary py-4 min-vh-100">
    <div class="container py-2">
        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb bg-white p-3 rounded-3 shadow-sm border mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-success"><i class="bi bi-house-door me-1"></i>{{ __('app.home_title') }}</a></li>
                <li class="breadcrumb-item active fw-semibold" aria-current="page">Mã giảm giá đặc biệt</li>
            </ol>
        </nav>

        <div class="p-4 rounded-4 text-white mb-4 shadow-sm" style="background: linear-gradient(135deg, #f77f00 0%, #d62828 100%);">
            <h2 class="fw-bold mb-1"><i class="bi bi-gift-fill me-2"></i>Mã Giảm Giá Đặc Biệt</h2>
            <p class="text-white-50 mb-0">Thu thập các mã giảm giá bên dưới để sử dụng cho đơn hàng của bạn.</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            </div>
        @endif

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            @forelse($coupons as $coupon)
                @php
                    $isSaved = $userCoupons->contains(function ($userCoupon) use ($coupon) {
                        return str_starts_with($userCoupon->code, $coupon->code . '_');
                    });
                    $isExpired = $coupon->expires_at && $coupon->expires_at->isPast();
                    $isDisabled = $isSaved || $isExpired;
                @endphp
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm rounded-4 p-4 bg-white d-flex flex-column justify-content-between {{ $isDisabled ? 'opacity-75' : '' }}">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge {{ $isDisabled ? 'bg-secondary' : 'bg-danger' }} rounded-pill px-3 py-1.5 fw-bold">
                                    @if ($coupon->type === 'percent') Giảm {{ $coupon->value }}%
                                    @elseif ($coupon->type === 'fixed') Giảm {{ number_format($coupon->value) }}đ
                                    @else Miễn phí ship
                                    @endif
                                </span>
                                @if($isSaved)
                                    <span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-1 small">Đã lưu</span>
                                @elseif($isExpired)
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5 py-1 small">Hết hạn</span>
                                @endif
                            </div>
                            
                            <h5 class="fw-bold font-monospace text-dark mb-2">{{ $coupon->code }}</h5>
                            <div class="small text-muted mb-3">
                                @if ($coupon->min_order_amount > 0)
                                    <div>• Đơn tối thiểu: <strong>{{ number_format($coupon->min_order_amount) }}đ</strong></div>
                                @endif
                                @if ($coupon->expires_at)
                                    <div>• HSD: {{ $coupon->expires_at->format('d/m/Y H:i') }}</div>
                                @endif
                            </div>
                        </div>

                        <div class="border-top pt-3 mt-2">
                            @if($isSaved)
                                <a href="{{ route('profile.coupons') }}" class="btn btn-outline-secondary w-100 rounded-pill py-2 small fw-semibold">Xem trong ví</a>
                            @elseif($isExpired)
                                <button disabled class="btn btn-light border w-100 rounded-pill py-2 small" disabled>Đã hết hạn</button>
                            @else
                                <form action="{{ route('promo.save', $coupon) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-warning text-dark fw-bold w-100 rounded-pill py-2 shadow-sm">
                                        <i class="bi bi-bookmark-plus me-1"></i> Lưu mã
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="bi bi-ticket-perforated display-3 text-muted opacity-50 mb-3"></i>
                    <h5 class="fw-bold text-dark">Chưa có mã giảm giá nào</h5>
                    <p class="text-muted small">Hãy quay lại sau để nhận các ưu đãi hấp dẫn nhé!</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
