@extends('layouts.app')
@section('title', 'Quản lý tài khoản - SFood')
@section('content')
<div class="bg-earth-800 text-cream-50 py-12">
    <div class="max-w-6xl mx-auto px-4">
        <h1 class="text-3xl font-bold">Quản lý tài khoản</h1>
    </div>
</div>
<div class="max-w-6xl mx-auto px-4 py-8 flex flex-col md:flex-row gap-6">
    <!-- Sidebar / Tabs -->
    <div class="w-full md:w-64 shrink-0">
        <div class="card p-4 space-y-1 sticky top-24">
            <a href="{{ route('profile') }}" class="block px-4 py-3 rounded-lg font-medium transition {{ request()->routeIs('profile') ? 'bg-leaf-600 text-white shadow' : 'text-earth-700 hover:bg-cream-100' }}">👤 Thông tin cá nhân</a>
            <a href="{{ route('orders.index') }}" class="block px-4 py-3 rounded-lg font-medium transition {{ request()->routeIs('orders.*') ? 'bg-leaf-600 text-white shadow' : 'text-earth-700 hover:bg-cream-100' }}">📦 Lịch sử mua hàng</a>
            <a href="{{ route('profile.coupons') }}" class="block px-4 py-3 rounded-lg font-medium transition flex justify-between items-center {{ request()->routeIs('profile.coupons') ? 'bg-leaf-600 text-white shadow' : 'text-earth-700 hover:bg-cream-100' }}">
                <span>🎟️ Ví Coupon</span>
            </a>
            <a href="{{ route('profile.address') }}" class="block px-4 py-3 rounded-lg font-medium transition {{ request()->routeIs('profile.address') ? 'bg-leaf-600 text-white shadow' : 'text-earth-700 hover:bg-cream-100' }}">📍 Địa chỉ giao hàng</a>
            
            <div class="pt-4 mt-4 border-t border-cream-200">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-3 rounded-lg font-medium text-red-600 hover:bg-red-50 transition">🚪 Đăng xuất</button>
                </form>
            </div>
        </div>
    </div>
    <!-- Content -->
    <div class="flex-1">
        @yield('profile_content')
    </div>
</div>
@endsection
