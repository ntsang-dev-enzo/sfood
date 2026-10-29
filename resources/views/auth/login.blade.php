@extends('layouts.app')
@section('title', 'Đăng Nhập - SFood')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <div class="text-center mb-4">
                    <img src="{{ asset('/logo.png') }}" alt="SFood Logo" style="height: 56px; width: auto;" class="rounded bg-white p-1 mb-2">
                    <h3 class="fw-bold text-dark mb-1">Đăng Nhập SFood</h3>
                    <p class="text-muted small">Chào mừng bạn quay trở lại với thực phẩm tươi sống!</p>
                </div>

                @if ($errors->has('email') && str_contains($errors->first('email'), 'chưa tồn tại'))
                    <div class="alert alert-warning py-3 px-3 small mb-4 rounded-3 border-warning" role="alert">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
                            <strong class="text-dark">Chưa có tài khoản SFood!</strong>
                        </div>
                        <p class="mb-2 text-dark">{{ $errors->first('email') }}</p>
                        <a href="{{ route('register') }}" class="btn btn-success btn-sm fw-bold rounded-pill px-3">
                            <i class="bi bi-person-plus me-1"></i> Đăng ký tài khoản ngay
                        </a>
                    </div>
                @elseif ($errors->any())
                    <div class="alert alert-danger py-2 small mb-4" role="alert">
                        <i class="bi bi-exclamation-circle-fill me-1"></i> {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" id="login-form" novalidate>
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Địa chỉ Email <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" value="{{ old('email') }}" required 
                                   class="form-control border-start-0 shadow-none @error('email') is-invalid @enderror" 
                                   placeholder="email@example.com">
                        </div>
                        <div class="invalid-feedback">Vui lòng nhập địa chỉ email hợp lệ.</div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-semibold text-dark mb-0">Mật khẩu <span class="text-danger">*</span></label>
                            <a href="{{ route('password.request') }}" class="small text-success text-decoration-none">Quên mật khẩu?</a>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" required 
                                   class="form-control border-start-0 shadow-none @error('password') is-invalid @enderror" 
                                   placeholder="••••••••">
                        </div>
                        <div class="invalid-feedback">Mật khẩu phải có ít nhất 6 ký tự.</div>
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" checked>
                        <label class="form-check-label small text-muted cursor-pointer" for="remember">
                            Ghi nhớ đăng nhập
                        </label>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-success fw-bold py-2.5 shadow-sm rounded-pill">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập
                        </button>
                    </div>
                </form>

                <div class="d-flex align-items-center my-3">
                    <hr class="flex-grow-1">
                    <span class="px-3 small text-muted">hoặc</span>
                    <hr class="flex-grow-1">
                </div>

                <a href="{{ route('auth.google') }}" class="btn btn-outline-secondary w-100 py-2 d-flex align-items-center justify-center gap-2 rounded-pill small">
                    <img src="https://www.google.com/favicon.ico" style="width: 18px; height: 18px;" alt="">
                    Đăng nhập bằng tài khoản Google
                </a>

                <p class="text-center small text-muted mt-4 mb-0">
                    Chưa có tài khoản? <a href="{{ route('register') }}" class="text-success fw-bold text-decoration-none">Đăng ký ngay</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
