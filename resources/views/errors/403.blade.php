@extends('layouts.app')
@section('title', 'Không có quyền truy cập - SFood')

@section('content')
<div class="bg-body-tertiary py-5 min-vh-75 d-flex align-items-center">
    <div class="container text-center" style="max-width: 580px;">
        <div class="card border-0 shadow-sm rounded-4 p-5 bg-white">
            <div class="rounded-circle bg-danger-subtle text-danger mx-auto d-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                <i class="bi bi-shield-x fs-1"></i>
            </div>
            <h3 class="fw-bold text-dark mb-2">Không có quyền truy cập!</h3>
            <p class="text-secondary small lh-lg mb-4">
                Xin lỗi, tài khoản của bạn không được cấp quyền để truy cập vào phân hệ này. Nếu bạn tin rằng đây là lỗi, vui lòng liên hệ bộ phận hỗ trợ kỹ thuật SFood.
            </p>
            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                <a href="{{ route('home') }}" class="btn btn-success rounded-pill px-4 py-2 fw-semibold">
                    <i class="bi bi-house-door me-1"></i> Về Trang Chủ
                </a>
                <a href="{{ route('contact') }}" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold">
                    <i class="bi bi-headset me-1"></i> Liên hệ Hỗ trợ
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
