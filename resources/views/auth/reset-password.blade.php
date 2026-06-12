@extends('layouts.app')
@section('title', 'Đặt lại mật khẩu - SFood')
@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4 py-12">
    <div class="card w-full max-w-md p-8">
        <div class="text-center mb-8">
            <img src="{{ asset('/logo.png') }}" alt="SFood Logo" class="h-16 mx-auto object-contain mb-4">
            <h1 class="text-2xl font-bold text-earth-800">Đặt mật khẩu mới</h1>
        </div>
        <p class="text-earth-500 text-sm text-center mb-6">Nhập mật khẩu mới cho tài khoản của bạn</p>

        <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div>
                <label class="text-sm font-medium text-earth-700">Email</label>
                <input type="email" name="email" value="{{ old('email', $email) }}" required class="input-field mt-1" readonly>
            </div>
            <div>
                <label class="text-sm font-medium text-earth-700">Mật khẩu mới</label>
                <input type="password" name="password" required class="input-field mt-1">
            </div>
            <div>
                <label class="text-sm font-medium text-earth-700">Xác nhận mật khẩu</label>
                <input type="password" name="password_confirmation" required class="input-field mt-1">
            </div>
            <button type="submit" class="btn-primary w-full">Cập nhật mật khẩu</button>
        </form>
    </div>
</div>
@endsection
