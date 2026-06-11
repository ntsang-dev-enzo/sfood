@extends('layouts.app')
@section('title', 'Không có quyền truy cập')
@section('content')
<div class="min-h-[60vh] flex items-center justify-center px-4 py-12">
    <div class="text-center max-w-lg">
        <div class="w-24 h-24 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-earth-800 mb-4">Không có quyền truy cập!</h1>
        <p class="text-earth-600 mb-8 leading-relaxed">
            Xin lỗi, tài khoản của bạn không được cấp quyền để truy cập vào trang Quản trị hệ thống. Nếu bạn tin rằng đây là lỗi, vui lòng liên hệ bộ phận hỗ trợ.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('home') }}" class="btn-primary">Về Trang Chủ</a>
            <a href="{{ route('contact') }}" class="btn-secondary">Liên hệ Hỗ trợ</a>
        </div>
    </div>
</div>
@endsection
