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
            <!-- User Avatar & Points -->
            <div class="flex flex-col items-center gap-2 mb-4 pb-4 border-b border-cream-200">
                <img src="{{ \App\Support\MediaHelper::avatar(auth()->user()->avatar, auth()->user()->name) }}" class="w-20 h-20 rounded-full object-cover border-2 border-cream-300" alt="">
                <h3 class="font-bold text-earth-800 text-center">{{ auth()->user()->name }}</h3>
                <span class="text-xs font-bold text-leaf-600 bg-leaf-50 px-3 py-1 rounded-full border border-leaf-200" title="Điểm tích lũy">Điểm: {{ number_format(auth()->user()->reward_points) }}</span>
            </div>

            <a href="{{ route('profile') }}" class="block px-4 py-3 rounded-lg font-medium transition {{ request()->routeIs('profile') ? 'bg-leaf-600 text-white shadow' : 'text-earth-700 hover:bg-cream-100' }}">👤 Thông tin cá nhân</a>
            <a href="{{ route('orders.index') }}" class="block px-4 py-3 rounded-lg font-medium transition {{ request()->routeIs('orders.*') ? 'bg-leaf-600 text-white shadow' : 'text-earth-700 hover:bg-cream-100' }}">📦 Lịch sử mua hàng</a>
            <a href="{{ route('notifications.index') }}" class="block px-4 py-3 rounded-lg font-medium transition flex justify-between items-center {{ request()->routeIs('notifications.*') ? 'bg-leaf-600 text-white shadow' : 'text-earth-700 hover:bg-cream-100' }}">
                <span>🔔 Thông báo</span>
                @php $unreadCount = auth()->user()->unreadNotifications->count(); @endphp
                @if($unreadCount > 0)
                    <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $unreadCount }}</span>
                @endif
            </a>
            <a href="{{ route('profile.coupons') }}" class="block px-4 py-3 rounded-lg font-medium transition flex justify-between items-center {{ request()->routeIs('profile.coupons') ? 'bg-leaf-600 text-white shadow' : 'text-earth-700 hover:bg-cream-100' }}">
                <span>🎟️ Ví Coupon</span>
            </a>
            <a href="{{ route('favorites.index') }}" class="block px-4 py-3 rounded-lg font-medium transition {{ request()->routeIs('favorites.*') ? 'bg-leaf-600 text-white shadow' : 'text-earth-700 hover:bg-cream-100' }}">❤️ Sản phẩm yêu thích</a>
            <a href="{{ route('profile.address') }}" class="block px-4 py-3 rounded-lg font-medium transition {{ request()->routeIs('profile.address') ? 'bg-leaf-600 text-white shadow' : 'text-earth-700 hover:bg-cream-100' }}">📍 Địa chỉ giao hàng</a>
            <a href="{{ route('profile.chat-history') }}" class="block px-4 py-3 rounded-lg font-medium transition {{ request()->routeIs('profile.chat-history') ? 'bg-leaf-600 text-white shadow' : 'text-earth-700 hover:bg-cream-100' }}">💬 Lịch sử chat</a>
            
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
