@extends('layouts.app')
@section('title', 'Giới thiệu - SFood')

@section('content')
<div class="bg-body-tertiary py-4 min-vh-100">
    <div class="container py-2">
        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb bg-white p-3 rounded-3 shadow-sm border mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-success"><i class="bi bi-house-door me-1"></i>{{ __('app.home_title') }}</a></li>
                <li class="breadcrumb-item active fw-semibold" aria-current="page">Về SFood</li>
            </ol>
        </nav>

        {{-- Hero Banner --}}
        <div class="p-5 rounded-4 text-white text-center mb-5 shadow-sm" style="background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%);">
            <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold mb-2">Thực phẩm sạch vì sức khỏe cộng đồng</span>
            <h1 class="display-5 fw-bold mb-3">Câu Chuyện Về SFood</h1>
            <p class="lead text-white-50 mx-auto" style="max-width: 700px;">
                Hành trình kết nối những nông trại hữu cơ uy tín đến từng căn bếp gia đình Việt, cam kết nông sản tươi ngon, nguồn gốc minh bạch và trải nghiệm mua sắm tiện ích vượt trội.
            </p>
        </div>

        {{-- Mission & Stats --}}
        <div class="row g-5 align-items-center mb-5">
            <div class="col-lg-6">
                <h3 class="fw-bold text-dark mb-3">Sứ Mệnh Của Chúng Tôi</h3>
                <p class="text-secondary lh-lg mb-3">
                    SFood ra đời từ niềm trăn trở về chất lượng bữa cơm hàng ngày của người tiêu dùng. Chúng tôi xây dựng quy trình khép kín từ khâu thu hoạch tại vườn đến tận tay người nhận trong vòng 2 giờ, loại bỏ tối đa thời gian lưu kho để giữ trọn vẹn hương vị và dưỡng chất tự nhiên.
                </p>
                <p class="text-secondary lh-lg mb-0">
                    Bên cạnh chất lượng nông sản, website được trang bị hệ thống quản trị hiện đại, tính năng tìm kiếm thông minh, quản lý dữ liệu XML DOM tương tác mượt mà và hỗ trợ khách hàng đa kênh.
                </p>
            </div>
            <div class="col-lg-6">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-white h-100">
                            <div class="display-5 fw-bold text-success mb-1">500+</div>
                            <div class="text-muted small fw-semibold">Nông sản &amp; Thực phẩm sạch</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-white h-100">
                            <div class="display-5 fw-bold text-success mb-1">10K+</div>
                            <div class="text-muted small fw-semibold">Khách hàng tin yêu</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-white h-100">
                            <div class="display-5 fw-bold text-warning mb-1">2 Giờ</div>
                            <div class="text-muted small fw-semibold">Giao hàng thần tốc</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-white h-100">
                            <div class="display-5 fw-bold text-danger mb-1">4.9 <i class="bi bi-star-fill text-warning fs-4"></i></div>
                            <div class="text-muted small fw-semibold">Đánh giá hài lòng</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Core Values --}}
        <div class="text-center mb-4">
            <h3 class="fw-bold text-dark">Giá Trị Cốt Lõi Tại SFood</h3>
            <p class="text-muted">Những tiêu chuẩn khắt khe làm nên thương hiệu</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-white h-100">
                    <div class="fs-1 text-success mb-3"><i class="bi bi-flower1"></i></div>
                    <h5 class="fw-bold text-dark mb-2">100% Nguồn Gốc Rõ Ràng</h5>
                    <p class="text-secondary small lh-base mb-0">Mọi sản phẩm đều có thông tin xuất xứ từ nông trại VietGAP, tiêu chuẩn canh tác sạch và ngày thu hoạch chi tiết.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-white h-100">
                    <div class="fs-1 text-primary mb-3"><i class="bi bi-lightning-charge-fill"></i></div>
                    <h5 class="fw-bold text-dark mb-2">Mua Nhanh Nguyên Liệu</h5>
                    <p class="text-secondary small lh-base mb-0">Xem các video hướng dẫn nấu ăn chuẩn vị và mua trọn gói nguyên liệu cần thiết chỉ với một thao tác bấm tiện lợi.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-white h-100">
                    <div class="fs-1 text-warning mb-3"><i class="bi bi-file-earmark-code"></i></div>
                    <h5 class="fw-bold text-dark mb-2">Chuẩn Hóa Dữ Liệu XML</h5>
                    <p class="text-secondary small lh-base mb-0">Hệ thống thực đơn breakfast-menu.xml với cấu trúc DTD hợp lệ, nạp dữ liệu bằng XMLHttpRequest và DOM node traversal mượt mà.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
