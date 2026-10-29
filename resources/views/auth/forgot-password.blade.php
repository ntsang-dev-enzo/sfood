@extends('layouts.app')
@section('title', 'Quên mật khẩu - SFood')

@section('content')
<div class="bg-body-tertiary py-5 min-vh-75 d-flex align-items-center">
    <div class="container" style="max-width: 440px;">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
            <div class="text-center mb-4">
                <img src="{{ asset('/logo.png') }}" alt="SFood Logo" class="mb-3" style="max-height: 52px; width: auto;">
                <h4 class="fw-bold text-dark mb-1">Quên mật khẩu</h4>
                <p class="text-muted small">Nhập email đăng ký để nhận liên kết khôi phục mật khẩu</p>
            </div>

            @if (session('status'))
                <div class="alert alert-success border-0 rounded-3 small mb-3">
                    <i class="bi bi-check-circle me-1"></i>{{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-dark">Email đăng ký <span class="text-danger">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="form-control" placeholder="you@example.com">
                    @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <button type="submit" class="btn btn-success w-100 py-2.5 rounded-pill fw-bold shadow-sm">
                    Gửi liên kết xác thực
                </button>
            </form>

            <div class="text-center mt-4 pt-2 border-top">
                <a href="{{ route('login') }}" class="small text-success fw-semibold text-decoration-none">
                    <i class="bi bi-arrow-left me-1"></i>Quay lại đăng nhập
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
