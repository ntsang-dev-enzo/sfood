<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SFood - Nông Sản & Thực Phẩm Tươi Sống')</title>
    <meta name="description" content="SFood - Cửa hàng thực phẩm tươi sống, nông sản hữu cơ đạt chuẩn VietGAP, giao hàng nhanh trong ngày.">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & Bootstrap Icons (Local & CDN Fallback) -->
    <link href="{{ asset('bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('bootstrap/icons/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- App Assets (Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --bs-primary: #2d6a4f;
            --bs-primary-rgb: 45, 106, 79;
            --bs-success: #2d6a4f;
            --bs-success-rgb: 45, 106, 79;
            --sfood-primary: #2d6a4f;
            --sfood-primary-hover: #1b4332;
            --sfood-accent: #40916c;
            --sfood-cream: #faf7f2;
            --sfood-earth: #3d2c1e;
        }

        body {
            font-family: 'Be Vietnam Pro', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--sfood-cream);
            color: #2b2d42;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .bg-sfood-primary {
            background-color: var(--sfood-primary) !important;
        }

        .bg-sfood-dark {
            background-color: var(--sfood-earth) !important;
        }

        .text-sfood-primary {
            color: var(--sfood-primary) !important;
        }

        .btn-success, .btn-primary {
            background-color: var(--sfood-primary);
            border-color: var(--sfood-primary);
        }

        .btn-success:hover, .btn-primary:hover {
            background-color: var(--sfood-primary-hover);
            border-color: var(--sfood-primary-hover);
        }

        .btn-outline-success {
            color: var(--sfood-primary);
            border-color: var(--sfood-primary);
        }

        .btn-outline-success:hover {
            background-color: var(--sfood-primary);
            border-color: var(--sfood-primary);
            color: #fff;
        }

        .navbar-brand img {
            height: 44px;
            width: auto;
            object-fit: contain;
        }

        .nav-link {
            font-weight: 500;
            color: #495057;
            transition: all 0.2s ease-in-out;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--sfood-primary) !important;
        }

        .dropdown-item:active, .dropdown-item.active {
            background-color: var(--sfood-primary);
        }

        /* Product & Recipe Cards */
        .card-product {
            border: 1px solid rgba(0,0,0,0.06);
            border-radius: 1rem;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            background: #fff;
            overflow: hidden;
        }

        .card-product:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
        }

        /* Suggest dropdown */
        /* Hover effects */
        .hover-success {
            transition: all 0.2s ease;
        }
        .hover-success:hover {
            color: #198754 !important;
            background-color: rgba(25, 135, 84, 0.08);
        }

        /* Mega Menu 3 Cột Hiển Thị Khi Hover */
        .header-nav-container {
            position: relative !important;
        }

        @media (min-width: 992px) {
            .nav-item.dropdown-hover {
                position: static !important;
            }
            .mega-menu-3cols {
                position: absolute !important;
                top: 100% !important;
                left: 0 !important;
                right: 0 !important;
                width: 100% !important;
                background: #ffffff;
                border-radius: 0.85rem;
                box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.16);
                border: 1px solid rgba(0, 0, 0, 0.08);
                padding: 1.5rem;
                z-index: 1060;
                display: none !important;
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
                transform: translateY(6px);
                transition: opacity 0.18s ease, transform 0.18s ease, visibility 0.18s ease;
            }
            /* Chỉ hiển thị khi có class is-open (kích hoạt khi hover đúng nút Sản phẩm) */
            .mega-menu-3cols.is-open {
                display: block !important;
                opacity: 1 !important;
                visibility: visible !important;
                pointer-events: auto !important;
                transform: translateY(0) !important;
            }
        }

        .hover-category-item {
            transition: all 0.15s ease-in-out;
            color: #334155;
            text-decoration: none;
        }
        .hover-category-item:hover {
            background-color: #f0fdf4 !important;
            color: #15803d !important;
            transform: translateX(4px);
        }

        .suggest-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1050;
            max-height: 320px;
            overflow-y: auto;
            display: none;
        }

        .suggest-dropdown.active {
            display: block;
        }

        /* Mini cart offcanvas/dropdown */
        .cart-badge {
            font-size: 0.65rem;
            padding: 0.35em 0.55em;
        }

        /* Voice search button seamless integration */
        .voice-search-btn {
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            background: transparent !important;
        }
        .voice-search-btn:focus, .voice-search-btn:active {
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            background: transparent !important;
        }
    </style>

    @stack('styles')
</head>

<body>
    {{-- Top Bar (Bootstrap 5 Utilities) --}}
    <div class="bg-sfood-dark text-white py-1 small">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-truck text-warning"></i>
                <span class="d-none d-sm-inline">{{ __('app.free_shipping', ['default' => 'Miễn phí vận chuyển cho đơn hàng từ 300.000₫']) }}</span>
                <span class="d-inline d-sm-none">Freeship từ 300k</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                @auth
                    <a href="{{ route('favorites.index') }}" class="text-white text-decoration-none d-none d-md-inline opacity-75 hover-opacity-100">
                        <i class="bi bi-heart me-1"></i>Yêu thích
                    </a>
                    <a href="{{ route('orders.index') }}" class="text-white text-decoration-none d-none d-md-inline opacity-75 hover-opacity-100">
                        <i class="bi bi-box-seam me-1"></i>{{ __('nav.orders', ['default' => 'Đơn hàng']) }}
                    </a>
                @endauth

                {{-- Chuyển đổi ngôn ngữ --}}
                <div class="d-flex align-items-center gap-2 border-start border-secondary ps-3">
                    @if(session('locale', 'vi') === 'en')
                        <a href="{{ route('lang.switch', 'vi') }}" class="text-white text-decoration-none small" title="Tiếng Việt">
                            <span class="badge bg-light text-dark fw-bold">VI</span> Tiếng Việt
                        </a>
                    @else
                        <a href="{{ route('lang.switch', 'en') }}" class="text-white text-decoration-none small" title="English">
                            <span class="badge bg-light text-dark fw-bold">EN</span> English
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Header Chính (2 Tầng chuẩn E-commerce) --}}
    <header class="bg-white sticky-top shadow-sm border-bottom">
        {{-- Tầng 1: Logo, Search Box, Giỏ hàng, Tài khoản --}}
        <div class="py-2 py-lg-3">
            <div class="container d-flex align-items-center justify-content-between gap-3">
                {{-- Brand Logo --}}
                <a class="d-flex align-items-center gap-2 text-decoration-none text-dark flex-shrink-0" href="{{ route('home') }}">
                    <img src="{{ asset('/logo.png') }}" alt="SFood Logo" style="height: 42px; width: auto;" class="rounded-2 shadow-sm border p-0.5 bg-white">
                    <div class="d-none d-sm-block">
                        <span class="fw-bold fs-4 text-success d-block lh-1">SFood</span>
                        <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.65rem; letter-spacing: 1px;">Fresh &amp; Organic</small>
                    </div>
                </a>

                {{-- Form Tìm kiếm trên Desktop (Rộng rãi ở giữa) --}}
                <div class="d-none d-lg-block flex-grow-1 mx-4" style="max-width: 560px;">
                    @include('partials.search-box', ['size' => 'md'])
                </div>

                {{-- Right Actions: Giỏ hàng, Tài khoản, Toggler --}}
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    {{-- Nút Giỏ Hàng mở Offcanvas (Không viền) --}}
                    <button class="btn border-0 bg-light text-dark position-relative rounded-pill px-3 py-2 d-flex align-items-center gap-2 shadow-none" 
                            type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart" aria-controls="offcanvasCart" style="border: none !important;">
                        <i class="bi bi-cart3 fs-5 text-success"></i>
                        <span class="d-none d-sm-inline fw-semibold small">{{ __('app.cart', ['default' => 'Giỏ hàng']) }}</span>
                        @php $cartCount = app(\App\Services\CartService::class)->count(); @endphp
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-badge {{ $cartCount > 0 ? '' : 'd-none' }}" data-cart-count>
                            {{ $cartCount }}
                        </span>
                    </button>

                    {{-- User Dropdown --}}
                    @auth
                        <div class="dropdown d-none d-sm-block">
                            <button class="btn btn-light rounded-pill dropdown-toggle d-flex align-items-center gap-2 border px-2.5 py-1.5" 
                                    type="button" id="userProfileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="{{ \App\Support\MediaHelper::avatar(auth()->user()->avatar, auth()->user()->name) }}"
                                     class="rounded-circle border" style="width: 28px; height: 28px; object-fit: cover;" alt="">
                                <span class="small fw-semibold text-truncate" style="max-width: 120px;">{{ auth()->user()->name }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 py-2" aria-labelledby="userProfileDropdown" style="min-width: 200px; z-index: 1070;">
                                <li><h6 class="dropdown-header text-success">{{ number_format(auth()->user()->reward_points ?? 0) }} điểm</h6></li>
                                <li><a class="dropdown-item py-1.5" href="{{ route('profile') }}">Tài khoản</a></li>
                                <li><a class="dropdown-item py-1.5" href="{{ route('orders.index') }}">Đơn mua</a></li>
                                <li><a class="dropdown-item py-1.5" href="{{ route('favorites.index') }}">Món yêu thích</a></li>
                                @if(auth()->user()->is_admin ?? false)
                                    <li><a class="dropdown-item py-1.5 text-danger" href="{{ url('/admin') }}">Quản trị (Filament)</a></li>
                                @endif
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                                        @csrf
                                        <button class="dropdown-item py-1.5 text-danger" type="submit">Đăng xuất</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-success rounded-pill px-3 py-2 small fw-semibold d-none d-sm-inline-flex align-items-center gap-1 shadow-sm">
                            Đăng nhập
                        </a>
                    @endauth

                    {{-- Toggler cho Mobile --}}
                    <button class="navbar-toggler border-0 shadow-none p-1 d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNav" aria-controls="offcanvasNav">
                        <i class="bi bi-list fs-2 text-dark"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile Search Box (Below Navbar on small screens) --}}
        <div class="container d-lg-none pb-2">
            @include('partials.search-box', ['size' => 'md'])
        </div>

        {{-- Tầng 2: Thanh Menu Điều Hướng (Secondary Nav Bar - Trải rộng toàn bộ hàng ngang) --}}
        <div class="border-top py-2 d-none d-lg-block bg-white position-relative">
            <div class="container d-flex align-items-center justify-content-between header-nav-container">
                <nav class="nav gap-1 align-items-center">
                    {{-- Trang chủ --}}
                    <a class="nav-link px-3 py-1.5 rounded-pill fw-semibold {{ request()->routeIs('home') ? 'bg-success text-white shadow-sm' : 'text-dark hover-success' }}" href="{{ route('home') }}">
                        Trang chủ
                    </a>
                    
                    {{-- Dropdown Sản phẩm dạng Mega Menu 3 Cột (Chỉ mở khi hover vào nút Sản phẩm) --}}
                    <div class="nav-item dropdown dropdown-hover">
                        <a class="nav-link dropdown-toggle px-3 py-1.5 rounded-pill fw-semibold {{ request()->routeIs('products.*') ? 'bg-success text-white shadow-sm' : 'text-dark hover-success' }}" 
                           href="{{ route('products.index') }}" id="productMegaDropdownToggle">
                            Sản phẩm
                        </a>

                        {{-- Bảng Mega Menu 3 Cột Hiển Thị Khi Hover --}}
                        <div class="dropdown-menu mega-menu-3cols p-4 border-0 shadow-lg mt-0" id="productMegaMenuContainer">
                            @php
                                $menuCats = ($allNavCategories ?? collect())->count() ? $allNavCategories : \App\Models\Category::where('is_active', true)->orderBy('sort_order')->withCount('products')->get();
                                $col1 = $menuCats->slice(0, 4);
                                $col2 = $menuCats->slice(4, 3);
                                $col3 = $menuCats->slice(7);
                            @endphp
                            <div class="row g-4">
                                {{-- Cột 1: Nông Sản & Tươi Sống --}}
                                <div class="col-4 border-end">
                                    <div class="mb-3 pb-2 border-bottom">
                                        <h6 class="fw-bold mb-0 text-success text-uppercase small" style="letter-spacing: 0.5px;">Nông Sản &amp; Tươi Sống</h6>
                                    </div>
                                    <div class="d-flex flex-column gap-1">
                                        @foreach($col1 as $cItem)
                                            <a href="{{ route('products.index', ['category' => $cItem->slug]) }}" class="p-2 rounded-2 hover-category-item d-flex align-items-center justify-content-between text-decoration-none">
                                                <span class="fw-medium">{{ $cItem->name }}</span>
                                                @if($cItem->products_count > 0)
                                                    <span class="badge bg-light text-muted border small">{{ $cItem->products_count }}</span>
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Cột 2: Bách Hóa & Đồ Khô --}}
                                <div class="col-4 border-end">
                                    <div class="mb-3 pb-2 border-bottom">
                                        <h6 class="fw-bold mb-0 text-dark text-uppercase small" style="letter-spacing: 0.5px;">Bách Hóa &amp; Đồ Khô</h6>
                                    </div>
                                    <div class="d-flex flex-column gap-1">
                                        @foreach($col2 as $cItem)
                                            <a href="{{ route('products.index', ['category' => $cItem->slug]) }}" class="p-2 rounded-2 hover-category-item d-flex align-items-center justify-content-between text-decoration-none">
                                                <span class="fw-medium">{{ $cItem->name }}</span>
                                                @if($cItem->products_count > 0)
                                                    <span class="badge bg-light text-muted border small">{{ $cItem->products_count }}</span>
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Cột 3: Đồ Uống & Tiêu Dùng --}}
                                <div class="col-4">
                                    <div class="mb-3 pb-2 border-bottom">
                                        <h6 class="fw-bold mb-0 text-dark text-uppercase small" style="letter-spacing: 0.5px;">Đồ Uống &amp; Tiêu Dùng</h6>
                                    </div>
                                    <div class="d-flex flex-column gap-1 mb-3">
                                        @foreach($col3 as $cItem)
                                            <a href="{{ route('products.index', ['category' => $cItem->slug]) }}" class="p-2 rounded-2 hover-category-item d-flex align-items-center justify-content-between text-decoration-none">
                                                <span class="fw-medium">{{ $cItem->name }}</span>
                                                @if($cItem->products_count > 0)
                                                    <span class="badge bg-light text-muted border small">{{ $cItem->products_count }}</span>
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>

                                    {{-- Khối xem tất cả sản phẩm --}}
                                    <div class="p-3 rounded-3 bg-light border text-center">
                                        <div class="fw-bold text-success small mb-1">Toàn Bộ Sản Phẩm</div>
                                        <p class="text-muted mb-2" style="font-size: 0.72rem;">Cam kết tươi ngon, an toàn vệ sinh thực phẩm</p>
                                        <a href="{{ route('products.index') }}" class="btn btn-success btn-sm w-100 fw-bold rounded-pill">
                                            Xem tất cả sản phẩm
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Công thức món ngon --}}
                    <a class="nav-link px-3 py-1.5 rounded-pill fw-semibold {{ request()->routeIs('recipes.*') ? 'bg-success text-white shadow-sm' : 'text-dark hover-success' }}" href="{{ route('recipes.index') }}">
                        Công thức nấu ăn
                    </a>

                    {{-- Khuyến mãi --}}
                    <a class="nav-link px-3 py-1.5 rounded-pill fw-semibold {{ request()->routeIs('promo.index') ? 'bg-success text-white shadow-sm' : 'text-dark hover-success' }}" href="{{ route('promo.index') }}">
                        Khuyến mãi
                    </a>

                    {{-- Giới thiệu --}}
                    <a class="nav-link px-3 py-1.5 rounded-pill fw-semibold {{ request()->routeIs('about') ? 'bg-success text-white shadow-sm' : 'text-dark hover-success' }}" href="{{ route('about') }}">
                        Giới thiệu
                    </a>

                    {{-- Liên hệ --}}
                    <a class="nav-link px-3 py-1.5 rounded-pill fw-semibold {{ request()->routeIs('contact') ? 'bg-success text-white shadow-sm' : 'text-dark hover-success' }}" href="{{ route('contact') }}">
                        Liên hệ
                    </a>
                </nav>

                {{-- Thông tin hotline & cam kết bên phải --}}
                <div class="d-flex align-items-center gap-3 small text-muted">
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">
                        100% VietGAP
                    </span>
                    <span class="d-none d-xl-inline text-secondary">
                        Hotline: <strong class="text-dark">1900 6868</strong>
                    </span>
                </div>
            </div>
        </div>
    </header>

    {{-- Offcanvas Menu (Mobile Navigation - Bootstrap 5 Offcanvas) --}}
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNav" aria-labelledby="offcanvasNavLabel">
        <div class="offcanvas-header bg-success text-white">
            <div class="d-flex align-items-center gap-2">
                <img src="{{ asset('/logo.png') }}" alt="SFood Logo" style="height: 36px; width: auto;" class="rounded bg-white p-1">
                <div>
                    <h5 class="offcanvas-title fw-bold mb-0 lh-1" id="offcanvasNavLabel">SFood Organic</h5>
                    <small class="text-white-50" style="font-size: 0.7rem;">Thực phẩm sạch cho mọi nhà</small>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
            @auth
                <div class="p-3 bg-light border-bottom d-flex align-items-center gap-3">
                    <img src="{{ \App\Support\MediaHelper::avatar(auth()->user()->avatar, auth()->user()->name) }}"
                         class="rounded-circle border" style="width: 46px; height: 46px; object-fit: cover;" alt="">
                    <div class="flex-grow-1 overflow-hidden">
                        <h6 class="fw-bold mb-0 text-truncate text-dark">{{ auth()->user()->name }}</h6>
                        <small class="badge bg-success-subtle text-success"><i class="bi bi-coin me-1"></i>{{ number_format(auth()->user()->reward_points ?? 0) }} điểm</small>
                    </div>
                </div>
            @endauth

            {{-- Danh sách điều hướng Mobile (Gọn gàng, sạch sẽ, không icon màu mè) --}}
            <div class="list-group list-group-flush">
                <a class="list-group-item list-group-item-action py-3 fw-semibold {{ request()->routeIs('home') ? 'text-success bg-light' : 'text-dark' }}" href="{{ route('home') }}">
                    Trang chủ
                </a>

                {{-- Accordion Danh mục sản phẩm trên mobile --}}
                <div class="list-group-item p-0 border-0">
                    <a class="list-group-item list-group-item-action py-3 d-flex align-items-center justify-content-between {{ request()->routeIs('products.*') ? 'text-success fw-bold' : 'text-dark' }}" 
                       data-bs-toggle="collapse" href="#mobileCategoryCollapse" role="button" aria-expanded="false">
                        <span class="fw-semibold">Danh mục sản phẩm</span>
                        <i class="bi bi-chevron-down small text-muted"></i>
                    </a>
                    <div class="collapse bg-light {{ request()->routeIs('products.*') ? 'show' : '' }}" id="mobileCategoryCollapse">
                        <div class="py-1">
                            <a class="list-group-item list-group-item-action py-2 ps-4 border-0 bg-transparent text-success fw-bold small" href="{{ route('products.index') }}">
                                &bull; Tất cả sản phẩm
                            </a>
                            @php
                                $mobileCats = ($allNavCategories ?? collect())->count() ? $allNavCategories : \App\Models\Category::where('is_active', true)->orderBy('sort_order')->get();
                            @endphp
                            @foreach($mobileCats as $mCat)
                                <a class="list-group-item list-group-item-action py-2 ps-4 border-0 bg-transparent text-dark small" href="{{ route('products.index', ['category' => $mCat->slug]) }}">
                                    &bull; {{ $mCat->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Công thức nấu ăn --}}
                <a class="list-group-item list-group-item-action py-3 fw-semibold {{ request()->routeIs('recipes.*') ? 'text-success bg-light' : 'text-dark' }}" href="{{ route('recipes.index') }}">
                    Công thức nấu ăn
                </a>

                {{-- Khuyến mãi --}}
                <a class="list-group-item list-group-item-action py-3 fw-semibold {{ request()->routeIs('promo.index') ? 'text-success bg-light' : 'text-dark' }}" href="{{ route('promo.index') }}">
                    Khuyến mãi &amp; Ưu đãi
                </a>

                {{-- Giới thiệu --}}
                <a class="list-group-item list-group-item-action py-3 fw-semibold {{ request()->routeIs('about') ? 'text-success bg-light' : 'text-dark' }}" href="{{ route('about') }}">
                    Giới thiệu SFood
                </a>

                {{-- Liên hệ --}}
                <a class="list-group-item list-group-item-action py-3 fw-semibold {{ request()->routeIs('contact') ? 'text-success bg-light' : 'text-dark' }}" href="{{ route('contact') }}">
                    Liên hệ &amp; Góp ý
                </a>
            </div>

            {{-- Thông tin tài khoản Mobile --}}
            <div class="p-3 mt-3 border-top bg-light">
                @auth
                    <div class="d-grid gap-2">
                        <a href="{{ route('profile') }}" class="btn btn-outline-success btn-sm">Tài khoản của tôi</a>
                        <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary btn-sm">Đơn hàng đã đặt</a>
                        @if(auth()->user()->is_admin ?? false)
                            <a href="{{ url('/admin') }}" class="btn btn-outline-danger btn-sm">Trang Quản trị</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button class="btn btn-danger btn-sm w-100" type="submit">Đăng xuất</button>
                        </form>
                    </div>
                @else
                    <div class="d-grid gap-2">
                        <a href="{{ route('login') }}" class="btn btn-success fw-semibold">Đăng nhập</a>
                        <a href="{{ route('register') }}" class="btn btn-outline-secondary">Đăng ký tài khoản</a>
                    </div>
                @endauth
            </div>
        </div>
    </div>

    {{-- Offcanvas Giỏ Hàng (Bootstrap 5 Offcanvas) --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasCart" aria-labelledby="offcanvasCartLabel">
        <div class="offcanvas-header bg-success text-white">
            <h5 class="offcanvas-title fw-bold" id="offcanvasCartLabel"><i class="bi bi-cart3 me-2"></i>Giỏ hàng của bạn</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column p-0">
            @php
                $miniCartItems = app(\App\Services\CartService::class)->items();
                $miniCartSelectedSubtotal = app(\App\Services\CartService::class)->selectedSubtotal();
            @endphp
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light">
                <span class="small text-muted fw-semibold">Sản phẩm đã chọn</span>
                <button type="button" id="mini-cart-clear-all" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 0.75rem;">Xóa tất cả</button>
            </div>

            <div class="flex-grow-1 overflow-y-auto p-3" id="mini-cart-items-container">
                @if($miniCartItems->isEmpty())
                    <div class="text-center py-5 text-muted" id="mini-cart-empty-msg">
                        <i class="bi bi-cart-x fs-1 text-secondary opacity-50 d-block mb-2"></i>
                        Giỏ hàng trống
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($miniCartItems as $item)
                            <div class="list-group-item px-0 py-3 d-flex align-items-center gap-3 mini-cart-item {{ !$item->is_selected ? 'opacity-50' : '' }}" data-id="{{ $item->id }}">
                                <input type="checkbox" class="form-check-input mini-cart-item-checkbox m-0 cursor-pointer" data-id="{{ $item->id }}" {{ $item->is_selected ? 'checked' : '' }}>
                                <img src="{{ \App\Support\MediaHelper::productImage($item->productVariant->product->thumbnail) }}" 
                                     class="rounded-3 border object-fit-cover" style="width: 50px; height: 50px;" alt="">
                                <div class="flex-grow-1 min-w-0">
                                    <h6 class="mb-0 text-truncate fw-semibold small text-dark">{{ $item->productVariant->product->name }}</h6>
                                    <small class="text-muted d-block">{{ $item->productVariant->name }}</small>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <div class="input-group input-group-sm" style="width: 90px;">
                                            <button class="btn btn-outline-secondary mini-cart-btn-minus px-2" type="button" data-id="{{ $item->id }}">-</button>
                                            <input type="number" class="form-control text-center mini-cart-input-qty p-0" data-id="{{ $item->id }}" value="{{ $item->quantity }}" min="1">
                                            <button class="btn btn-outline-secondary mini-cart-btn-plus px-2" type="button" data-id="{{ $item->id }}">+</button>
                                        </div>
                                        <span class="text-danger fw-bold small ms-auto mini-cart-item-subtotal" data-id="{{ $item->id }}">
                                            {{ number_format($item->subtotal()) }}₫
                                        </span>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm text-secondary hover-danger mini-cart-btn-remove p-1" data-id="{{ $item->id }}" title="Xóa">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="p-3 border-top bg-light mt-auto" id="mini-cart-footer">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small">Tạm tính (đã chọn):</span>
                    <h5 class="fw-bold text-danger mb-0" id="mini-cart-subtotal">{{ number_format($miniCartSelectedSubtotal) }}₫</h5>
                </div>
                <a href="{{ route('checkout.index') }}" id="mini-cart-checkout-btn" 
                   class="btn btn-success w-100 fw-bold py-2 shadow-sm {{ $miniCartSelectedSubtotal > 0 ? '' : 'disabled' }}">
                    Tiến hành thanh toán <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if (session('success'))
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                <div>{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    {{-- Main Page Content --}}
    <main class="flex-grow-1">
        @yield('content')
    </main>

    {{-- Live Chat Với Nhân Viên Hỗ Trợ (Human Support - Không dùng AI) --}}
    @auth
    @php
        $unreadChatCount = \App\Models\ChatMessage::where('user_id', auth()->id())->where('sender', 'admin')->where('is_read', false)->count();
    @endphp
    <div id="live-chatbox-container" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
        <!-- Chat Window -->
        <div id="live-chat-window" class="card shadow-lg border-0 mb-3 d-none" style="width: 340px; border-radius: 1rem; overflow: hidden;">
            <div class="card-header bg-success text-white py-2 px-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-headset fs-5"></i>
                    <div>
                        <h6 class="mb-0 fw-bold small">Hỗ trợ khách hàng</h6>
                        <small class="text-white-50" style="font-size: 0.65rem;">Nhân viên SFood trực tuyến</small>
                    </div>
                </div>
                <button type="button" id="close-live-chat-btn" class="btn-close btn-close-white small"></button>
            </div>
            <div id="live-chat-messages-container" class="card-body p-3 overflow-y-auto bg-light" style="height: 280px;">
                <div class="text-center text-muted small my-3" id="live-chat-empty-msg">Chào bạn! Nhắn tin để gặp nhân viên hỗ trợ SFood.</div>
            </div>
            <div class="card-footer bg-white p-2">
                <div class="input-group input-group-sm">
                    <input type="text" id="live-chat-input-field" class="form-control" placeholder="Nhập tin nhắn..." autocomplete="off">
                    <button class="btn btn-success" type="button" id="send-live-chat-btn"><i class="bi bi-send-fill"></i></button>
                </div>
            </div>
        </div>

        <!-- Toggle Button -->
        <button type="button" id="toggle-live-chat-btn" class="btn btn-success rounded-circle shadow-lg p-0 d-flex align-items-center justify-center ms-auto position-relative" style="width: 54px; height: 54px;" title="Chat nhân viên hỗ trợ">
            <i class="bi bi-chat-dots-fill fs-4 m-auto"></i>
            @if($unreadChatCount > 0)
                <span id="live-chat-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white">
                    {{ $unreadChatCount }}
                </span>
            @endif
        </button>
    </div>
    @endauth

    {{-- Footer (Bootstrap 5 Grid 12 cột & Logo thương hiệu tự thiết kế) --}}
    <footer class="bg-sfood-dark text-white pt-5 pb-3 mt-5">
        <div class="container">
            <div class="row g-4">
                {{-- Cột 1: Logo thương hiệu nhóm & Giới thiệu --}}
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <img src="{{ asset('/logo.png') }}" alt="SFood Logo" style="height: 48px; width: auto;" class="rounded bg-white p-1">
                        <div>
                            <h5 class="fw-bold mb-0 text-white">SFood Organic</h5>
                            <small class="text-success text-uppercase fw-semibold" style="letter-spacing: 1px;">Fresh &amp; Clean Food</small>
                        </div>
                    </div>
                    <p class="text-white-50 small pe-lg-3">
                        Hệ thống cung cấp thực phẩm tươi sống, nông sản sạch chuẩn VietGAP và công thức món ngon mỗi ngày. 
                        Cam kết 100% tươi sạch, an toàn vệ sinh thực phẩm và giao nhanh trong 2 giờ.
                    </p>
                    <div class="d-flex gap-2">
                        <a href="#" class="btn btn-sm btn-outline-light rounded-circle" style="width: 32px; height: 32px;"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="btn btn-sm btn-outline-light rounded-circle" style="width: 32px; height: 32px;"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="btn btn-sm btn-outline-light rounded-circle" style="width: 32px; height: 32px;"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>

                {{-- Cột 2: Danh mục sản phẩm --}}
                <div class="col-6 col-md-3 col-lg-2">
                    <h6 class="fw-bold text-uppercase mb-3 text-white">Danh Mục</h6>
                    <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                        <li><a href="{{ route('products.index') }}" class="text-white-50 text-decoration-none hover-white">Tất cả sản phẩm</a></li>
                        <li><a href="{{ route('recipes.index') }}" class="text-white-50 text-decoration-none hover-white">Công thức nấu ăn</a></li>
                        <li><a href="{{ route('promo.index') }}" class="text-white-50 text-decoration-none hover-white">Khuyến mãi hot</a></li>
                    </ul>
                </div>

                {{-- Cột 3: Hỗ trợ & Chính sách --}}
                <div class="col-6 col-md-3 col-lg-2">
                    <h6 class="fw-bold text-uppercase mb-3 text-white">Hỗ Trợ</h6>
                    <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                        <li><a href="{{ route('about') }}" class="text-white-50 text-decoration-none hover-white">Về chúng tôi</a></li>
                        <li><a href="{{ route('contact') }}" class="text-white-50 text-decoration-none hover-white">Liên hệ hợp tác</a></li>
                        <li><a href="{{ route('faq') }}" class="text-white-50 text-decoration-none hover-white">Câu hỏi thường gặp</a></li>
                        <li><a href="{{ route('policy') }}" class="text-white-50 text-decoration-none hover-white">Chính sách giao hàng</a></li>
                    </ul>
                </div>

                {{-- Cột 4: Thông tin liên hệ nhóm --}}
                <div class="col-12 col-md-6 col-lg-4">
                    <h6 class="fw-bold text-uppercase mb-3 text-white">Liên Hệ Nhóm Thực Hiện</h6>
                    <ul class="list-unstyled small text-white-50 d-flex flex-column gap-2 mb-0">
                        <li><i class="bi bi-geo-alt-fill text-success me-2"></i>123 Đường Nông Nghiệp Xanh, TP. Hồ Chí Minh</li>
                        <li><i class="bi bi-telephone-fill text-success me-2"></i>Hotline: 1900 6868 - (028) 3822 5588</li>
                        <li><i class="bi bi-envelope-fill text-success me-2"></i>Email: contact@sfood.vn</li>
                        <li><i class="bi bi-server text-success me-2"></i>Tương thích: Apache (XAMPP) / IIS Server</li>
                    </ul>
                </div>
            </div>

            <hr class="border-secondary my-4">

            <div class="row align-items-center small text-white-50">
                <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                    &copy; {{ date('Y') }} SFood Organic. Tất cả các quyền được bảo lưu.
                </div>
                <div class="col-md-6 text-center text-md-end">
                    Thương hiệu nông sản &amp; thực phẩm tươi sạch đạt chuẩn VietGAP
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS (Local) -->
    <script src="{{ asset('bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Client-side Validation JS -->
    <script src="{{ asset('js/form-validation.js') }}"></script>

    <!-- App JS -->
    <script>
        // Mini Cart AJAX Logic
        document.addEventListener('DOMContentLoaded', function () {
            const miniCartContainer = document.getElementById('mini-cart-items-container');
            const clearAllBtn = document.getElementById('mini-cart-clear-all');

            const fetchApi = async (url, bodyData) => {
                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(bodyData)
                    });
                    return await res.json();
                } catch (e) {
                    console.error(e);
                    return null;
                }
            };

            const updateMiniCartUI = (data) => {
                if (!data) return;
                const countBadge = document.querySelector('[data-cart-count]');
                if (countBadge) {
                    countBadge.textContent = data.count;
                    if (data.count > 0) countBadge.classList.remove('d-none');
                    else countBadge.classList.add('d-none');
                }
                const subtotalEl = document.getElementById('mini-cart-subtotal');
                if (subtotalEl && data.subtotal !== undefined) {
                    subtotalEl.textContent = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(data.subtotal);
                }
                const checkoutBtn = document.getElementById('mini-cart-checkout-btn');
                if (checkoutBtn) {
                    if (data.subtotal > 0) checkoutBtn.classList.remove('disabled');
                    else checkoutBtn.classList.add('disabled');
                }
            };

            if (miniCartContainer) {
                miniCartContainer.addEventListener('click', async (e) => {
                    const btnPlus = e.target.closest('.mini-cart-btn-plus');
                    const btnMinus = e.target.closest('.mini-cart-btn-minus');
                    const btnRemove = e.target.closest('.mini-cart-btn-remove');

                    if (btnPlus) {
                        const id = btnPlus.getAttribute('data-id');
                        const input = miniCartContainer.querySelector(`.mini-cart-input-qty[data-id="${id}"]`);
                        const newQty = parseInt(input.value) + 1;
                        input.value = newQty;
                        const data = await fetchApi('{{ route('cart.update') }}', { item_id: id, quantity: newQty });
                        updateMiniCartUI(data);
                    } else if (btnMinus) {
                        const id = btnMinus.getAttribute('data-id');
                        const input = miniCartContainer.querySelector(`.mini-cart-input-qty[data-id="${id}"]`);
                        if (parseInt(input.value) > 1) {
                            const newQty = parseInt(input.value) - 1;
                            input.value = newQty;
                            const data = await fetchApi('{{ route('cart.update') }}', { item_id: id, quantity: newQty });
                            updateMiniCartUI(data);
                        }
                    } else if (btnRemove) {
                        const id = btnRemove.getAttribute('data-id');
                        const data = await fetchApi('{{ route('cart.remove') }}', { item_id: id });
                        const itemRow = miniCartContainer.querySelector(`.mini-cart-item[data-id="${id}"]`);
                        if (itemRow) itemRow.remove();
                        updateMiniCartUI(data);
                        if (!miniCartContainer.querySelector('.mini-cart-item')) {
                            miniCartContainer.innerHTML = '<div class="text-center py-5 text-muted" id="mini-cart-empty-msg"><i class="bi bi-cart-x fs-1 text-secondary opacity-50 d-block mb-2"></i>Giỏ hàng trống</div>';
                        }
                    }
                });

                miniCartContainer.addEventListener('change', async (e) => {
                    if (e.target.classList.contains('mini-cart-item-checkbox')) {
                        const id = e.target.getAttribute('data-id');
                        const checked = e.target.checked;
                        const data = await fetchApi('{{ route('cart.toggle_select') }}', { item_id: id, selected: checked });
                        updateMiniCartUI(data);
                    }
                });
            }

            if (clearAllBtn) {
                clearAllBtn.addEventListener('click', async () => {
                    if (confirm('Bạn có chắc muốn xóa tất cả sản phẩm khỏi giỏ hàng?')) {
                        const data = await fetchApi('{{ route('cart.clear') }}', {});
                        if (miniCartContainer) {
                            miniCartContainer.innerHTML = '<div class="text-center py-5 text-muted" id="mini-cart-empty-msg"><i class="bi bi-cart-x fs-1 text-secondary opacity-50 d-block mb-2"></i>Giỏ hàng trống</div>';
                        }
                        updateMiniCartUI(data);
                    }
                });
            }

            // Live Chat with Support Staff (No AI)
            const toggleChatBtn = document.getElementById('toggle-live-chat-btn');
            const closeChatBtn = document.getElementById('close-live-chat-btn');
            const chatWindow = document.getElementById('live-chat-window');
            const chatMessagesContainer = document.getElementById('live-chat-messages-container');
            const chatInputField = document.getElementById('live-chat-input-field');
            const sendChatBtn = document.getElementById('send-live-chat-btn');
            const liveChatBadge = document.getElementById('live-chat-badge');
            let liveChatInterval = null;

            if (toggleChatBtn && chatWindow) {
                const toggleChat = () => {
                    const isHidden = chatWindow.classList.contains('d-none');
                    if (isHidden) {
                        chatWindow.classList.remove('d-none');
                        if (liveChatBadge) liveChatBadge.style.display = 'none';
                        fetchLiveMessages();
                        liveChatInterval = setInterval(fetchLiveMessages, 5000);
                    } else {
                        chatWindow.classList.add('d-none');
                        if (liveChatInterval) clearInterval(liveChatInterval);
                    }
                };

                toggleChatBtn.addEventListener('click', toggleChat);
                if (closeChatBtn) closeChatBtn.addEventListener('click', toggleChat);

                const appendLiveMessage = (msg) => {
                    const emptyMsg = document.getElementById('live-chat-empty-msg');
                    if (emptyMsg) emptyMsg.style.display = 'none';

                    const div = document.createElement('div');
                    const isUser = msg.sender === 'user';
                    div.className = `p-2 mb-2 rounded-3 small ${isUser ? 'bg-success text-white ms-auto text-end' : 'bg-white border text-dark me-auto text-start'}`;
                    div.style.maxWidth = '85%';
                    div.textContent = msg.message;
                    chatMessagesContainer.appendChild(div);
                    chatMessagesContainer.scrollTop = chatMessagesContainer.scrollHeight;
                };

                const fetchLiveMessages = async () => {
                    try {
                        const res = await fetch('{{ route('live-chat.fetch') }}');
                        const data = await res.json();
                        if (data && data.length > 0) {
                            chatMessagesContainer.innerHTML = '';
                            data.forEach(msg => appendLiveMessage(msg));
                        }
                    } catch (e) {}
                };

                const sendLiveMessage = async () => {
                    const text = chatInputField.value.trim();
                    if (!text) return;
                    chatInputField.value = '';
                    appendLiveMessage({ sender: 'user', message: text });

                    try {
                        await fetch('{{ route('live-chat.send') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ message: text })
                        });
                    } catch (e) {}
                };

                if (sendChatBtn) sendChatBtn.addEventListener('click', sendLiveMessage);
                if (chatInputField) chatInputField.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') sendLiveMessage();
                });
            }

            // Mega Menu Dropdown Hover cho Desktop (Chỉ mở khi hover đúng vào nút Sản phẩm)
            const toggleBtn = document.getElementById('productMegaDropdownToggle');
            const megaMenu = document.getElementById('productMegaMenuContainer');
            if (toggleBtn && megaMenu) {
                let hoverTimer = null;

                const openMenu = () => {
                    if (window.innerWidth >= 992) {
                        clearTimeout(hoverTimer);
                        megaMenu.classList.add('is-open');
                    }
                };

                const closeMenu = () => {
                    if (window.innerWidth >= 992) {
                        hoverTimer = setTimeout(() => {
                            megaMenu.classList.remove('is-open');
                        }, 180);
                    }
                };

                // CHỈ bắt sự kiện khi chuột đi vào đúng nút "Sản phẩm"
                toggleBtn.addEventListener('mouseenter', openMenu);
                toggleBtn.addEventListener('mouseleave', closeMenu);

                // Giữ menu mở khi rê chuột bên trong bảng menu
                megaMenu.addEventListener('mouseenter', openMenu);
                megaMenu.addEventListener('mouseleave', closeMenu);

                // Nếu click vào nút trên desktop: chuyển thẳng đến trang danh sách sản phẩm
                toggleBtn.addEventListener('click', (e) => {
                    if (window.innerWidth >= 992) {
                        window.location.href = "{{ route('products.index') }}";
                    }
                });
            }

            // User Profile Dropdown Click Handler (Đảm bảo click mở/đóng mượt mà 100%)
            const profileBtn = document.getElementById('userProfileDropdown');
            if (profileBtn) {
                profileBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const menu = this.nextElementSibling;
                    if (menu) {
                        const isShown = menu.classList.contains('show');
                        document.querySelectorAll('.dropdown-menu.show').forEach(m => {
                            if (!m.classList.contains('mega-menu-3cols')) {
                                m.classList.remove('show');
                            }
                        });
                        if (!isShown) {
                            menu.classList.add('show');
                            this.setAttribute('aria-expanded', 'true');
                        } else {
                            menu.classList.remove('show');
                            this.setAttribute('aria-expanded', 'false');
                        }
                    }
                });
            }

            // Đóng dropdown khi click ra ngoài
            document.addEventListener('click', function (e) {
                if (!e.target.closest('.dropdown')) {
                    document.querySelectorAll('.dropdown-menu.show').forEach(m => {
                        if (!m.classList.contains('mega-menu-3cols')) {
                            m.classList.remove('show');
                        }
                    });
                    const pBtn = document.getElementById('userProfileDropdown');
                    if (pBtn) pBtn.setAttribute('aria-expanded', 'false');
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
