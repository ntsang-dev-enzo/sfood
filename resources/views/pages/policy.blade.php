@extends('layouts.app')
@section('title', 'Chính sách dịch vụ - SFood')

@section('content')
<div class="bg-body-tertiary py-4 min-vh-100">
    <div class="container py-2" style="max-width: 860px;">
        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb bg-white p-3 rounded-3 shadow-sm border mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-success"><i class="bi bi-house-door me-1"></i>{{ __('app.home_title') }}</a></li>
                <li class="breadcrumb-item active fw-semibold" aria-current="page">Chính sách &amp; Điều khoản</li>
            </ol>
        </nav>

        <div class="text-center mb-5">
            <span class="badge bg-success-subtle text-success px-3 py-1.5 rounded-pill fw-bold mb-2">Quy chuẩn dịch vụ</span>
            <h2 class="fw-bold text-dark">Chính Sách &amp; Cam Kết</h2>
            <p class="text-muted small">Quyền lợi và trách nhiệm bảo vệ khách hàng khi mua sắm tại SFood</p>
        </div>

        <div class="d-flex flex-column gap-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <h5 class="fw-bold text-dark mb-3 d-flex align-items-center">
                    <i class="bi bi-truck text-success me-2 fs-4"></i>1. Chính sách giao hàng 2h
                </h5>
                <ul class="text-secondary small lh-lg mb-0 ps-3">
                    <li>Giao siêu tốc trong 2 giờ đối với các đơn hàng tại khu vực nội thành.</li>
                    <li>Đơn hàng từ 1.000.000đ hoặc khách hàng tại nội thành Cần Thơ được hỗ trợ miễn phí vận chuyển 100%.</li>
                    <li>Nông sản và thực phẩm tươi sống được đóng gói trong thùng bảo ôn giữ lạnh đạt chuẩn, đảm bảo giữ nguyên độ tươi mới.</li>
                </ul>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <h5 class="fw-bold text-dark mb-3 d-flex align-items-center">
                    <i class="bi bi-arrow-repeat text-danger me-2 fs-4"></i>2. Chính sách đổi trả &amp; hoàn tiền
                </h5>
                <ul class="text-secondary small lh-lg mb-0 ps-3">
                    <li>Khách hàng được quyền kiểm tra hàng trước khi thanh toán. Nếu phát hiện rau củ dập nát hoặc hàng không tươi ngon, quý khách có quyền từ chối nhận hàng.</li>
                    <li>Đổi trả miễn phí trong vòng 24 giờ kể từ thời điểm nhận hàng kèm video/hình ảnh mở hộp.</li>
                    <li>Đối với đơn thanh toán trực tuyến qua PayOS, hệ thống tự động hoàn tiền vào tài khoản ngân hàng trong vòng 1-3 ngày làm việc sau khi yêu cầu được duyệt.</li>
                </ul>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <h5 class="fw-bold text-dark mb-3 d-flex align-items-center">
                    <i class="bi bi-shield-check text-primary me-2 fs-4"></i>3. Chính sách bảo mật thông tin
                </h5>
                <p class="text-secondary small lh-lg mb-0">
                    SFood cam kết bảo vệ tuyệt đối thông tin cá nhân, số điện thoại, địa chỉ nhận hàng và dữ liệu thanh toán của quý khách hàng. Thông tin chỉ được sử dụng cho mục đích xử lý đơn hàng và chăm sóc hậu mãi, tuyệt đối không chia sẻ cho bên thứ ba.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
