@extends('layouts.app')
@section('title', 'Nhận mã giảm giá & phí ship - SFood')

@section('content')
<div class="bg-body-tertiary py-5 min-vh-100">
    <div class="container py-2" style="max-width: 680px;">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
            <h3 class="fw-bold text-dark mb-2">Nhận mã giảm giá &amp; điều chỉnh phí ship</h3>
            <p class="text-muted small mb-4">Nhập mã ưu đãi hoặc áp dụng phí ship tạm thời cho giỏ hàng của bạn.</p>

            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('promo.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-dark">Mã giảm giá (Coupon Code)</label>
                    <input name="coupon_code" value="{{ old('coupon_code', session('promo.coupon_code')) }}" class="form-control" placeholder="Nhập mã bạn nhận được...">
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-semibold text-dark">Phí ship tuỳ chỉnh (áp dụng tạm vào giỏ hàng)</label>
                    <div class="input-group">
                        <input name="shipping_fee" type="number" step="1000" value="{{ old('shipping_fee', session('promo.shipping_fee', '')) }}" class="form-control" placeholder="Ví dụ: 25000">
                        <span class="input-group-text">VNĐ</span>
                    </div>
                    <small class="text-muted mt-1 d-block">Phí ship này sẽ được dùng tạm khi tính tổng trong phiên làm việc.</small>
                </div>
                <button type="submit" class="btn btn-success rounded-pill px-4 py-2 fw-semibold shadow-sm">
                    <i class="bi bi-check-lg me-1"></i> Áp dụng ngay
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
