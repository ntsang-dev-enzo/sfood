@extends('layouts.app')
@section('title', 'Thu thập Voucher - SFood')

@section('content')
<div class="bg-body-tertiary py-4 min-vh-100">
    <div class="container py-2">
        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb bg-white p-3 rounded-3 shadow-sm border mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-success"><i class="bi bi-house-door me-1"></i>{{ __('app.home_title') }}</a></li>
                <li class="breadcrumb-item active fw-semibold" aria-current="page">Trạm Voucher Khuyến Mãi</li>
            </ol>
        </nav>

        <div class="p-4 p-md-5 rounded-4 text-white mb-4 shadow-sm" style="background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%);">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold mb-2">
                        <i class="bi bi-stars me-1"></i> Ưu đãi độc quyền SFood
                    </span>
                    <h2 class="fw-bold mb-2">Trạm Săn Mã Giảm Giá</h2>
                    <p class="text-white-50 mb-0">Thu thập các mã giảm giá hấp dẫn để nhận ưu đãi trực tiếp khi thanh toán đơn hàng.</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="{{ route('profile.coupons') }}" class="btn btn-outline-light rounded-pill px-4 fw-semibold">
                        <i class="bi bi-wallet2 me-1"></i> Xem ví voucher của tôi
                    </a>
                </div>
            </div>
        </div>

        @if ($coupons->count())
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                @foreach ($coupons as $coupon)
                    @php
                        $isCollected = in_array($coupon->id, $collectedIds);
                        $isUsedUp = $coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit;
                    @endphp
                    <div class="col">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden {{ $isUsedUp ? 'bg-light opacity-75' : 'bg-white' }}">
                            <div class="p-4 text-center border-bottom border-dashed bg-light">
                                <span class="badge bg-danger rounded-pill px-3 py-1.5 mb-2 fs-6">
                                    @if ($coupon->type === 'percent') Giảm {{ $coupon->value }}%
                                    @elseif ($coupon->type === 'fixed') Giảm {{ number_format($coupon->value) }}đ
                                    @else Miễn phí vận chuyển
                                    @endif
                                </span>
                                @if ($coupon->min_order_amount > 0)
                                    <div class="small text-muted mb-2">Cho đơn từ {{ number_format($coupon->min_order_amount) }}đ</div>
                                @endif
                                <div class="font-monospace fw-bold fs-4 text-success bg-white py-1 px-3 rounded-3 border d-inline-block shadow-sm">
                                    {{ $coupon->code }}
                                </div>
                            </div>
                            <div class="p-4 d-flex flex-column justify-content-between flex-grow-1">
                                <div class="small text-muted mb-3">
                                    @if ($coupon->expires_at)
                                        <div><i class="bi bi-clock me-1"></i>HSD: {{ $coupon->expires_at->format('d/m/Y H:i') }}</div>
                                    @endif
                                    @if ($coupon->usage_limit)
                                        <div><i class="bi bi-people me-1"></i>Đã dùng: {{ $coupon->used_count }}/{{ $coupon->usage_limit }} lượt</div>
                                    @endif
                                </div>
                                
                                @if($isUsedUp)
                                    <button class="btn btn-secondary w-100 rounded-pill py-2 disabled" disabled>Đã hết lượt</button>
                                @elseif($isCollected)
                                    <button class="btn btn-outline-secondary w-100 rounded-pill py-2 disabled" disabled><i class="bi bi-check-lg me-1"></i>Đã thu thập</button>
                                @else
                                    <button onclick="collectVoucher({{ $coupon->id }}, this)" class="btn btn-success w-100 rounded-pill py-2 fw-semibold shadow-sm">
                                        <i class="bi bi-box-arrow-in-down me-1"></i> Thu thập ngay
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                <i class="bi bi-ticket-perforated display-3 text-muted opacity-50 mb-3"></i>
                <h5 class="fw-bold text-dark">Chưa có mã giảm giá nào đang mở</h5>
                <p class="text-muted small">Hãy quay lại sau để nhận những ưu đãi hấp dẫn từ SFood nhé!</p>
                <div class="mt-2">
                    <a href="{{ route('products.index') }}" class="btn btn-success rounded-pill px-4 py-2 fw-semibold">
                        Tiếp tục mua sắm
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    async function collectVoucher(id, btn) {
        @if(!Auth::check())
            window.location.href = '{{ route('login') }}';
            return;
        @endif

        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang lưu...';
        btn.disabled = true;

        try {
            const res = await fetch(`/vouchers/${id}/collect`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            
            if (res.ok) {
                btn.className = "btn btn-outline-secondary w-100 rounded-pill py-2 disabled";
                btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Đã thu thập';
                alert(data.message);
            } else {
                btn.innerHTML = originalText;
                btn.disabled = false;
                alert(data.message || 'Có lỗi xảy ra');
            }
        } catch(e) {
            btn.innerHTML = originalText;
            btn.disabled = false;
            alert('Lỗi kết nối máy chủ');
        }
    }
</script>
@endpush
@endsection
