@extends('layouts.app')
@section('title', 'Đăng nhập - SFood')
@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4 py-12">
    <div class="card w-full max-w-md p-8">
        <div class="text-center mb-8">
            <img src="{{ asset('/logo.png') }}" alt="SFood Logo" class="h-16 mx-auto object-contain mb-4">
            <h1 class="text-2xl font-bold text-earth-800">Đăng nhập</h1>
            <p class="text-earth-500 text-sm mt-1">Chào mừng trở lại SFood</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 text-red-700 p-3 rounded-xl mb-4 text-sm">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium text-earth-700">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="input-field mt-1">
            </div>
            <div>
                <div class="flex justify-between items-center">
                    <label class="text-sm font-medium text-earth-700">Mật khẩu</label>
                    <a href="{{ route('password.request') }}" class="text-xs text-leaf-700 hover:underline">Quên mật khẩu?</a>
                </div>
                <input type="password" name="password" required class="input-field mt-1">
            </div>
            <label class="flex items-center gap-2 text-sm text-earth-600">
                <input type="checkbox" name="remember" class="accent-leaf-600"> Ghi nhớ đăng nhập
            </label>
            <button type="submit" class="btn-primary w-full">Đăng nhập</button>
        </form>

        <div class="my-6 flex items-center gap-3">
            <div class="flex-1 h-px bg-cream-300"></div>
            <span class="text-earth-400 text-sm">hoặc</span>
            <div class="flex-1 h-px bg-cream-300"></div>
        </div>

        <a href="{{ route('auth.google') }}" class="flex items-center justify-center gap-2 w-full border-2 border-cream-300 py-3 rounded-xl font-medium hover:bg-cream-100 transition text-earth-700">
            <img src="https://www.google.com/favicon.ico" class="w-5 h-5" alt="">
            Đăng nhập bằng Google
        </a>

        <p class="text-center text-sm mt-6 text-earth-500">
            Chưa có tài khoản? <a href="{{ route('register') }}" class="text-leaf-700 font-semibold hover:underline">Đăng ký</a>
        </p>
    </div>
</div>
@endsection
