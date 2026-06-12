@extends('layouts.profile')
@section('profile_content')
@php use App\Support\MediaHelper; @endphp

<div class="space-y-6">
    @if(session('success'))
        <div class="bg-leaf-100 text-leaf-800 p-4 rounded-xl">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 text-red-800 p-4 rounded-xl">
            {{ session('error') }}
        </div>
    @endif

    <div class="card p-6">
        <h2 class="font-bold text-lg text-earth-800 mb-4">Thông tin cá nhân</h2>
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="flex items-center gap-4">
                <img src="{{ MediaHelper::avatar($user->avatar, $user->name) }}" class="w-20 h-20 rounded-2xl object-cover border-2 border-cream-300" alt="">
                <div>
                    <label class="text-sm font-medium text-earth-700">Ảnh đại diện</label>
                    <input type="file" name="avatar" accept="image/*" class="text-sm mt-1 text-earth-600">
                </div>
            </div>
            <div>
                <label class="text-sm font-medium text-earth-700">Họ tên</label>
                <input name="name" value="{{ old('name', $user->name) }}" required class="input-field mt-1">
            </div>
            <div>
                <label class="text-sm font-medium text-earth-700">Email</label>
                <input value="{{ $user->email }}" disabled class="input-field mt-1 bg-cream-100">
            </div>
            <div>
                <label class="text-sm font-medium text-earth-700">Số điện thoại</label>
                <input name="phone" value="{{ old('phone', $user->phone) }}" class="input-field mt-1">
            </div>
            <button type="submit" class="btn-primary">Lưu thay đổi</button>
        </form>
    </div>

    <div class="card p-6">
        <h2 class="font-bold text-lg text-earth-800 mb-4">Đổi mật khẩu</h2>
        <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="text-sm font-medium text-earth-700">Mật khẩu hiện tại</label>
                <input type="password" name="current_password" required class="input-field mt-1">
            </div>
            <div>
                <label class="text-sm font-medium text-earth-700">Mật khẩu mới</label>
                <input type="password" name="password" required class="input-field mt-1">
            </div>
            <div>
                <label class="text-sm font-medium text-earth-700">Xác nhận mật khẩu mới</label>
                <input type="password" name="password_confirmation" required class="input-field mt-1">
            </div>
            <button type="submit" class="btn-secondary">Đổi mật khẩu</button>
        </form>
    </div>
</div>
@endsection
