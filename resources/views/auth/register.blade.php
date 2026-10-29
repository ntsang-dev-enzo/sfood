@extends('layouts.app')
@section('title', 'Đăng Ký Tài Khoản - SFood')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <div class="text-center mb-4">
                    <img src="{{ asset('/logo.png') }}" alt="SFood Logo" style="height: 56px; width: auto;" class="rounded bg-white p-1 mb-2">
                    <h3 class="fw-bold text-dark mb-1">Tạo Tài Khoản Mới</h3>
                    <p class="text-muted small">Trở thành thành viên SFood để nhận nhiều ưu đãi độc quyền!</p>
                </div>

                <form method="POST" action="{{ route('register') }}" id="register-form" novalidate>
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Họ và tên <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                            <input type="text" name="name" value="{{ old('name') }}" required 
                                   class="form-control border-start-0 shadow-none @error('name') is-invalid @enderror" 
                                   placeholder="Nguyễn Văn A">
                        </div>
                        <div class="invalid-feedback">Vui lòng nhập họ và tên (tối thiểu 2 ký tự).</div>
                        @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Địa chỉ Email <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" value="{{ old('email') }}" required 
                                   class="form-control border-start-0 shadow-none @error('email') is-invalid @enderror" 
                                   placeholder="email@example.com">
                        </div>
                        <div class="invalid-feedback">Email không đúng định dạng.</div>
                        @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Số điện thoại</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-telephone"></i></span>
                            <input type="tel" name="phone" value="{{ old('phone') }}" 
                                   class="form-control border-start-0 shadow-none @error('phone') is-invalid @enderror" 
                                   placeholder="0912 345 678">
                        </div>
                        <div class="invalid-feedback">Số điện thoại phải gồm 10 chữ số hợp lệ.</div>
                        @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Mật khẩu <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password" required 
                                       class="form-control border-start-0 shadow-none @error('password') is-invalid @enderror" 
                                       placeholder="Tối thiểu 6 ký tự">
                            </div>
                            <div class="invalid-feedback">Mật khẩu tối thiểu 6 ký tự.</div>
                            @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-shield-lock"></i></span>
                                <input type="password" name="password_confirmation" required 
                                       class="form-control border-start-0 shadow-none" 
                                       placeholder="Nhập lại mật khẩu">
                            </div>
                            <div class="invalid-feedback">Mật khẩu xác nhận không khớp.</div>
                        </div>
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="terms" id="terms" required checked>
                        <label class="form-check-label small text-muted" for="terms">
                            Tôi đồng ý với <a href="{{ route('policy') }}" class="text-success text-decoration-none">Điều khoản &amp; Chính sách</a> của SFood
                        </label>
                        <div class="invalid-feedback">Bạn phải đồng ý với điều khoản sử dụng.</div>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-success fw-bold py-2.5 shadow-sm rounded-pill">
                            <i class="bi bi-person-plus-fill me-1"></i> Hoàn tất đăng ký
                        </button>
                    </div>
                </form>

                <p class="text-center small text-muted mt-4 mb-0">
                    Đã có tài khoản? <a href="{{ route('login') }}" class="text-success fw-bold text-decoration-none">Đăng nhập ngay</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
