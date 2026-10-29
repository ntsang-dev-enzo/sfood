@extends('layouts.app')
@section('title', 'Quản lý tài khoản - SFood')

@section('content')
<div class="bg-body-tertiary py-4 min-vh-100">
    <div class="container py-2">

        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb bg-white p-3 rounded-3 shadow-sm border mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-success"><i class="bi bi-house-door me-1"></i>{{ __('app.home_title') }}</a></li>
                <li class="breadcrumb-item active fw-semibold" aria-current="page">Quản lý tài khoản</li>
            </ol>
        </nav>

        <div class="row g-4">
            <!-- Sidebar / Tabs (Col 3) -->
            <div class="col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white position-sticky" style="top: 90px;">
                    <!-- User Avatar & Points -->
                    <div class="d-flex flex-column align-items-center text-center pb-3 mb-3 border-bottom">
                        <img src="{{ \App\Support\MediaHelper::avatar(auth()->user()->avatar, auth()->user()->name) }}" 
                             class="rounded-circle object-fit-cover border border-3 border-success-subtle mb-2 shadow-sm" 
                             style="width: 76px; height: 76px;" 
                             alt="{{ auth()->user()->name }}">
                        <h6 class="fw-bold text-dark mb-1">{{ auth()->user()->name }}</h6>
                        <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1.5 small fw-semibold">
                            <i class="bi bi-coin text-warning me-1"></i>{{ number_format(auth()->user()->reward_points) }} điểm
                        </span>
                    </div>

                    <!-- Navigation Links -->
                    <div class="list-group list-group-flush gap-1">
                        <a href="{{ route('profile') }}" 
                           class="list-group-item list-group-item-action border-0 rounded-3 py-2.5 px-3 fw-semibold {{ request()->routeIs('profile') ? 'bg-success text-white' : 'text-dark hover-bg-light' }}">
                            <i class="bi bi-person me-2"></i>Thông tin cá nhân
                        </a>
                        <a href="{{ route('orders.index') }}" 
                           class="list-group-item list-group-item-action border-0 rounded-3 py-2.5 px-3 fw-semibold {{ request()->routeIs('orders.*') ? 'bg-success text-white' : 'text-dark hover-bg-light' }}">
                            <i class="bi bi-bag-check me-2"></i>Lịch sử mua hàng
                        </a>
                        <a href="{{ route('notifications.index') }}" 
                           class="list-group-item list-group-item-action border-0 rounded-3 py-2.5 px-3 fw-semibold d-flex justify-content-between align-items-center {{ request()->routeIs('notifications.*') ? 'bg-success text-white' : 'text-dark hover-bg-light' }}">
                            <span><i class="bi bi-bell me-2"></i>Thông báo</span>
                            @php $unreadCount = auth()->user()->unreadNotifications->count(); @endphp
                            @if($unreadCount > 0)
                                <span class="badge bg-danger rounded-pill">{{ $unreadCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('profile.coupons') }}" 
                           class="list-group-item list-group-item-action border-0 rounded-3 py-2.5 px-3 fw-semibold {{ request()->routeIs('profile.coupons') ? 'bg-success text-white' : 'text-dark hover-bg-light' }}">
                            <i class="bi bi-ticket-perforated me-2"></i>Ví Voucher
                        </a>
                        <a href="{{ route('favorites.index') }}" 
                           class="list-group-item list-group-item-action border-0 rounded-3 py-2.5 px-3 fw-semibold {{ request()->routeIs('favorites.*') ? 'bg-success text-white' : 'text-dark hover-bg-light' }}">
                            <i class="bi bi-heart me-2"></i>Sản phẩm yêu thích
                        </a>
                        <a href="{{ route('profile.address') }}" 
                           class="list-group-item list-group-item-action border-0 rounded-3 py-2.5 px-3 fw-semibold {{ request()->routeIs('profile.address') ? 'bg-success text-white' : 'text-dark hover-bg-light' }}">
                            <i class="bi bi-geo-alt me-2"></i>Sổ địa chỉ
                        </a>
                    </div>
                    
                    <div class="border-top pt-3 mt-3">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger w-100 rounded-pill py-2 fw-semibold small text-start px-3">
                                <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Content Area (Col 9) -->
            <div class="col-lg-9">
                @yield('profile_content')
            </div>
        </div>
    </div>
</div>
@endsection
