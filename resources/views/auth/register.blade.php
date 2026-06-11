@extends('layouts.app')
@section('title', 'Đăng ký - SFood')
@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4 py-12">
    <div class="card w-full max-w-md p-8">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-earth-800">Tạo tài khoản</h1>
            <p class="text-earth-500 text-sm mt-1">Tham gia cộng đồng SFood</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium text-earth-700">Họ tên</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="input-field mt-1 @error('name') border-red-500 @enderror">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-earth-700">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="input-field mt-1 @error('email') border-red-500 @enderror">
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-earth-700">Số điện thoại</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="input-field mt-1 @error('phone') border-red-500 @enderror">
                @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-earth-700">Mật khẩu</label>
                <input type="password" name="password" required class="input-field mt-1 @error('password') border-red-500 @enderror">
                @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-earth-700">Xác nhận mật khẩu</label>
                <input type="password" name="password_confirmation" required class="input-field mt-1">
            </div>
            <button type="submit" class="btn-primary w-full">Đăng ký</button>
        </form>

        <p class="text-center text-sm mt-6 text-earth-500">
            Đã có tài khoản? <a href="{{ route('login') }}" class="text-leaf-700 font-semibold hover:underline">Đăng nhập</a>
        </p>
    </div>
</div>
@endsection
