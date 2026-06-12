@extends('layouts.app')
@section('title', 'Quên mật khẩu - SFood')
@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4 py-12">
    <div class="card w-full max-w-md p-8">
        <div class="text-center mb-8">
            <img src="{{ asset('/logo.png') }}" alt="SFood Logo" class="h-16 mx-auto object-contain mb-4">
            <h1 class="text-2xl font-bold text-earth-800">Quên mật khẩu</h1>
        </div>
        <p class="text-earth-500 text-sm text-center mb-6">Nhập email để nhận link đặt lại mật khẩu</p>

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium text-earth-700">Email đăng ký</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="input-field mt-1" placeholder="you@email.com">
            </div>
            <button type="submit" class="btn-primary w-full">Gửi link xác thực</button>
        </form>

        <p class="text-center text-sm mt-6 text-earth-500">
            <a href="{{ route('login') }}" class="text-leaf-700 font-semibold hover:underline">← Quay lại đăng nhập</a>
        </p>
    </div>
</div>
@endsection
