@extends('layouts.app')
@section('title', 'Đặt lại mật khẩu - SFood')

@section('content')
<div class="bg-body-tertiary py-5 min-vh-75 d-flex align-items-center">
    <div class="container" style="max-width: 440px;">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
            <div class="text-center mb-4">
                <img src="{{ asset('/logo.png') }}" alt="SFood Logo" class="mb-3" style="max-height: 52px; width: auto;">
                <h4 class="fw-bold text-dark mb-1">Đặt mật khẩu mới</h4>
                <p class="text-muted small">Nhập mật khẩu mới cho tài khoản của bạn</p>
            </div>

            <form method="POST" action="{{ route('password.store') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-dark">Email</label>
                    <input type="email" name="email" value="{{ old('email', $email) }}" required class="form-control bg-light" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-dark">Mật khẩu mới <span class="text-danger">*</span></label>
                    <input type="password" name="password" required class="form-control" placeholder="••••••••">
                    @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-semibold text-dark">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                    <input type="password" name="password_confirmation" required class="form-control" placeholder="••••••••">
                </div>
                <button type="submit" class="btn btn-success w-100 py-2.5 rounded-pill fw-bold shadow-sm">
                    Cập nhật mật khẩu
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
