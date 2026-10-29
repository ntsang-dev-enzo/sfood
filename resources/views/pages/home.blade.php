@extends('layouts.app')
@section('title', 'SFood - Nông Sản & Thực Phẩm Tươi Sống Chuẩn VietGAP')

@section('content')
<style>
    /* Hero Carousel & Banner Dimensions - Đồng bộ kích thước chuẩn 100% */
    .hero-carousel-container {
        border-radius: 1.25rem;
        overflow: hidden;
    }
    #homeCarousel, 
    #homeCarousel .carousel-inner, 
    #homeCarousel .carousel-item {
        height: 440px;
    }
    @media (max-width: 991px) {
        #homeCarousel, 
        #homeCarousel .carousel-inner, 
        #homeCarousel .carousel-item {
            height: 340px;
        }
    }
    @media (max-width: 575px) {
        .hero-carousel-container {
            border-radius: 0.85rem;
        }
        #homeCarousel, 
        #homeCarousel .carousel-inner, 
        #homeCarousel .carousel-item {
            height: 170px;
        }
    }
    #homeCarousel img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }
    .promo-card-side {
        height: calc(220px - 0.5rem);
        border-radius: 1.25rem;
        background-size: cover;
        background-position: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .promo-card-side:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.12) !important;
    }
    .hover-lift {
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .hover-lift:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 20px rgba(45, 106, 79, 0.12) !important;
    }
    .cat-circle-card {
        transition: all 0.25s ease;
    }
    .cat-circle-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
        border-color: #2d6a4f !important;
    }
    .cat-circle-card:hover .cat-icon-wrap {
        transform: scale(1.1);
        background-color: #2d6a4f !important;
        color: #ffffff !important;
    }
    .cat-icon-wrap {
        transition: all 0.3s ease;
    }
</style>

{{-- 1. HERO SECTION: Carousel Đồng Bộ + Bento Promo Cards Bên Cạnh --}}
<div class="container py-3">
    <div class="row g-3">
        {{-- Carousel Chính (Col 8 trên desktop, Col 12 trên mobile) --}}
        <div class="col-lg-8">
            <div id="homeCarousel" class="carousel slide hero-carousel-container shadow-sm position-relative" data-bs-ride="carousel" data-bs-interval="4500">
                @if(isset($banners) && $banners->count() > 0)
                    <div class="carousel-indicators">
                        @foreach($banners as $index => $banner)
                            <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="{{ $index }}" class="{{ $index === 0 ? 'active' : '' }}" aria-label="Slide {{ $index + 1 }}"></button>
                        @endforeach
                    </div>
                    <div class="carousel-inner">
                        @foreach($banners as $index => $banner)
                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }} position-relative bg-dark">
                                <a href="{{ $banner->link ?: route('products.index') }}" class="d-block w-100 h-100">
                                    <img src="{{ asset('storage/' . $banner->image_path) }}" 
                                         class="d-block w-100 h-100 object-fit-cover" 
                                         alt="{{ $banner->title ?: 'SFood Banner' }}">
                                    @if($banner->title)
                                        <div class="carousel-caption d-none d-md-block text-start p-3 p-lg-4 rounded-4" 
                                             style="background: rgba(27, 38, 30, 0.75); backdrop-filter: blur(8px); max-width: 55%; bottom: 2rem; left: 2.5rem; border: 1px solid rgba(255,255,255,0.15);">
                                            <h3 class="fw-bold text-white mb-2">{{ $banner->title }}</h3>
                                            <span class="btn btn-sm btn-warning fw-bold rounded-pill px-3 text-dark shadow-sm">
                                                Khám phá ngay <i class="bi bi-arrow-right ms-1"></i>
                                            </span>
                                        </div>
                                    @endif
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="carousel-indicators">
                        <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                        <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                        <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
                    </div>
                    <div class="carousel-inner">
                        {{-- Slide 1: Rau Củ Tươi Sạch --}}
                        <div class="carousel-item active" style="background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 60%, #40916c 100%);">
                            <div class="container h-100 d-flex align-items-center">
                                <div class="row align-items-center w-100 g-3">
                                    <div class="col-md-8 text-white px-4 px-md-5">
                                        <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold mb-2 d-inline-flex align-items-center">
                                            <i class="bi bi-patch-check-fill me-1"></i> 100% Tươi Sạch &amp; Hữu Cơ
                                        </span>
                                        <h2 class="display-6 fw-black text-white mb-2 lh-sm">Nông Sản Sạch Cho Gia Đình</h2>
                                        <p class="lead fs-6 text-white-50 mb-3 pe-lg-3 d-none d-md-block">
                                            Thu hoạch từ nông trại trong lành mỗi sáng. Giao hàng tươi trong 2 giờ tại Cần Thơ, TP.HCM &amp; Hà Nội.
                                        </p>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="{{ route('products.index') }}" class="btn btn-warning fw-bold rounded-pill px-4 text-dark shadow-sm">
                                                <i class="bi bi-cart3 me-1"></i> Mua sắm ngay
                                            </a>
                                            <a href="{{ route('recipes.index') }}" class="btn btn-outline-light fw-bold rounded-pill px-4 shadow-sm">
                                                <i class="bi bi-journal-bookmark me-1"></i> Xem công thức
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-center d-none d-md-block">
                                        <div class="p-3 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-20 text-white backdrop-blur">
                                            <i class="bi bi-basket2-fill text-warning display-4"></i>
                                            <h6 class="fw-bold mt-2 mb-1">Giao Tươi Mỗi Sáng</h6>
                                            <small class="text-white-50">Đóng thùng giữ lạnh chuyên dụng</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Slide 2: Công thức món ngon --}}
                        <div class="carousel-item" style="background: linear-gradient(135deg, #2b2d42 0%, #3d2c1e 60%, #5c4033 100%);">
                            <div class="container h-100 d-flex align-items-center">
                                <div class="row align-items-center w-100 g-3">
                                    <div class="col-md-8 text-white px-4 px-md-5">
                                        <span class="badge bg-danger px-3 py-1.5 rounded-pill fw-bold mb-2 d-inline-flex align-items-center">
                                            <i class="bi bi-fire me-1"></i> Món Ngon Mỗi Ngày
                                        </span>
                                        <h2 class="display-6 fw-black text-white mb-2 lh-sm">Hôm Nay Bạn Muốn Ăn Gì?</h2>
                                        <p class="lead fs-6 text-white-50 mb-3 pe-lg-3 d-none d-md-block">
                                            Khám phá hàng chục video công thức chuẩn bếp gia đình. Mua trọn gói nguyên liệu cần thiết chỉ với 1 click!
                                        </p>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="{{ route('recipes.index') }}" class="btn btn-danger fw-bold rounded-pill px-4 shadow-sm">
                                                <i class="bi bi-journal-bookmark me-1"></i> Xem công thức
                                            </a>
                                            <a href="{{ route('vouchers.index') }}" class="btn btn-outline-light fw-bold rounded-pill px-4 shadow-sm">
                                                <i class="bi bi-ticket-perforated me-1"></i> Săn voucher
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-center d-none d-md-block">
                                        <div class="p-3 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-20 text-white backdrop-blur">
                                            <i class="bi bi-egg-fried text-danger display-4"></i>
                                            <h6 class="fw-bold mt-2 mb-1">Chuẩn Vị Đầu Bếp</h6>
                                            <small class="text-white-50">Dinh dưỡng &amp; Lành mạnh</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Slide 3: Thực Đơn Bữa Sáng Dinh Dưỡng --}}
                        <div class="carousel-item" style="background: linear-gradient(135deg, #0d3b66 0%, #1d3557 60%, #457b9d 100%);">
                            <div class="container h-100 d-flex align-items-center">
                                <div class="row align-items-center w-100 g-3">
                                    <div class="col-md-8 text-white px-4 px-md-5">
                                        <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold mb-2 d-inline-flex align-items-center">
                                            <i class="bi bi-cup-hot-fill me-1"></i> Khởi Đầu Ngày Mới Tràn Đầy Năng Lượng
                                        </span>
                                        <h2 class="display-6 fw-black text-white mb-2 lh-sm">Thực Đơn Bữa Sáng Tươi Ngon</h2>
                                        <p class="lead fs-6 text-white-50 mb-3 pe-lg-3 d-none d-md-block">
                                            Thưởng thức bánh mì nướng thơm giòn, phở bò tươi sạch cùng các loại rau củ hữu cơ tươi ngon dinh dưỡng cho cả gia đình.
                                        </p>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="{{ route('products.index', ['category' => 'mon_an_sang']) }}" class="btn btn-warning fw-bold rounded-pill px-4 text-dark shadow-sm">
                                                <i class="bi bi-cart3 me-1"></i> Khám Phá Món Ngon
                                            </a>
                                            <a href="{{ route('products.index') }}" class="btn btn-outline-light fw-bold rounded-pill px-4 shadow-sm">
                                                <i class="bi bi-cart-check me-1"></i> Đặt Hàng Ngay
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-center d-none d-md-block">
                                        <div class="p-3 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-20 text-white backdrop-blur">
                                            <i class="bi bi-egg-fried text-warning display-4"></i>
                                            <h6 class="fw-bold mt-2 mb-1">Ngon &amp; Tiện Lợi</h6>
                                            <small class="text-white-50">Giao tươi ngon tận nơi trong 2H</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <button class="carousel-control-prev" type="button" data-bs-target="#homeCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Trước</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#homeCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Tiếp</span>
                </button>
            </div>
        </div>

        {{-- Bento Promo Cards Bên Cạnh (Col 4 - Chỉ hiển thị trên Desktop d-none d-lg-flex) --}}
        <div class="col-lg-4 d-none d-lg-flex flex-column justify-content-between gap-3">
            {{-- Card 1 --}}
            <div class="promo-card-side p-4 text-white d-flex flex-column justify-content-between shadow-sm position-relative overflow-hidden" 
                 style="background: linear-gradient(135deg, #1b4332 0%, #40916c 100%);">
                <div class="position-absolute end-0 bottom-0 opacity-25 pe-3 pb-2 text-white">
                    <i class="bi bi-flower1" style="font-size: 6rem; line-height: 1;"></i>
                </div>
                <div class="position-relative z-1">
                    <span class="badge bg-warning text-dark px-2.5 py-1 rounded-pill fw-bold small mb-2">Ưu Đãi Hôm Nay</span>
                    <h5 class="fw-bold text-white mb-1">Rau Củ Hữu Cơ Đà Lạt</h5>
                    <p class="small text-white-50 mb-0">Giảm thêm 20% cho đơn hàng đầu tiên trong tuần</p>
                </div>
                <div class="position-relative z-1 pt-2">
                    <a href="{{ route('products.index') }}" class="btn btn-sm btn-light fw-bold text-success rounded-pill px-3 shadow-sm">
                        Xem ngay <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            {{-- Card 2 --}}
            <div class="promo-card-side p-4 text-white d-flex flex-column justify-content-between shadow-sm position-relative overflow-hidden" 
                 style="background: linear-gradient(135deg, #8b1e0f 0%, #e63946 100%);">
                <div class="position-absolute end-0 bottom-0 opacity-25 pe-3 pb-2 text-white">
                    <i class="bi bi-fire" style="font-size: 6rem; line-height: 1;"></i>
                </div>
                <div class="position-relative z-1">
                    <span class="badge bg-white text-danger px-2.5 py-1 rounded-pill fw-bold small mb-2">Tươi Sống Mỗi Ngày</span>
                    <h5 class="fw-bold text-white mb-1">Thịt &amp; Thủy Hải Sản Tươi</h5>
                    <p class="small text-white-50 mb-0">Nhập tươi trong ngày, chứng nhận kiểm dịch an toàn</p>
                </div>
                <div class="position-relative z-1 pt-2">
                    <a href="{{ route('products.index') }}" class="btn btn-sm btn-light fw-bold text-danger rounded-pill px-3 shadow-sm">
                        Đặt giao ngay <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 2. CAM KẾT & CHẤT LƯỢNG (Chỉ hiển thị trên desktop d-none d-lg-block) --}}
<div class="container py-3 d-none d-lg-block">
    <div class="row g-3">
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 shadow-sm p-3 rounded-4 bg-white hover-lift">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success-subtle text-success p-3 fs-4 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-lightning-charge-fill"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Giao nhanh 2h</h6>
                        <small class="text-muted">Nội thành trong ngày</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 shadow-sm p-3 rounded-4 bg-white hover-lift">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-warning-subtle text-warning p-3 fs-4 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-patch-check-fill"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Chuẩn VietGAP</h6>
                        <small class="text-muted">100% Nguồn gốc rõ ràng</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 shadow-sm p-3 rounded-4 bg-white hover-lift">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary-subtle text-primary p-3 fs-4 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Thanh toán an toàn</h6>
                        <small class="text-muted">PayOS, COD tận nơi</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 shadow-sm p-3 rounded-4 bg-white hover-lift">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-danger-subtle text-danger p-3 fs-4 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Đổi trả 24h</h6>
                        <small class="text-muted">Hoàn tiền nếu không tươi</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 3. FLASH SALE GIỜ VÀNG (E-commerce Flashsale Banner) --}}
@if(!empty($flashSales) && $flashSales->count())
    <div class="container py-4">
        <div class="p-4 rounded-4 text-white mb-4 shadow-sm position-relative overflow-hidden" 
             style="background: linear-gradient(90deg, #d90429 0%, #ef233c 60%, #f72585 100%);">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 position-relative z-1">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-lightning-charge-fill text-warning fs-1"></i>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h3 class="fw-black mb-0 text-white tracking-wider">FLASHSALE GIỜ VÀNG</h3>
                            <span class="badge bg-warning text-dark fw-bold">GIẢM ĐẾN 50%</span>
                        </div>
                        <small class="text-white-50">Số lượng có hạn, ưu đãi kết thúc khi hết hàng</small>
                    </div>
                </div>
                <a href="{{ route('products.index') }}" class="btn btn-light text-danger fw-bold rounded-pill px-4 shadow-sm">
                    Xem tất cả deal sốc <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        {{-- 3 Sản phẩm 1 hàng trên điện thoại (row-cols-3) --}}
        <div class="row row-cols-3 row-cols-md-3 row-cols-lg-6 g-2 g-md-3">
            @foreach($flashSales as $p)
                <div class="col">
                    @include('partials.product-card', ['product' => $p])
                </div>
            @endforeach
        </div>
    </div>
@endif

{{-- 4. DANH MỤC NỔI BẬT (Interactive Category Circles - 3 cột trên mobile) --}}
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <span class="badge bg-success-subtle text-success fw-bold uppercase">Nhóm Nông Sản</span>
            <h3 class="fw-bold text-dark mt-1 mb-0">Khám Phá Theo Danh Mục</h3>
        </div>
        <a href="{{ route('products.index') }}" class="text-success text-decoration-none fw-semibold small">
            Tất cả danh mục <i class="bi bi-chevron-right"></i>
        </a>
    </div>

    <div class="row row-cols-3 row-cols-sm-3 row-cols-md-4 row-cols-lg-6 g-2 g-md-3">
        @foreach ($categories as $cat)
            <div class="col">
                <a href="{{ route('products.index', ['category' => $cat->slug]) }}" 
                   class="card h-100 border shadow-sm text-center text-decoration-none text-dark p-2 p-md-3 rounded-4 cat-circle-card bg-white">
                    <div class="rounded-circle bg-success-subtle text-success mx-auto d-flex align-items-center justify-content-center mb-2 cat-icon-wrap" 
                         style="width: 52px; height: 52px; font-size: 1.5rem;">
                        <i class="bi bi-basket2-fill"></i>
                    </div>
                    <h6 class="fw-bold mb-1 text-dark text-truncate small">{{ $cat->name }}</h6>
                    <small class="text-muted d-none d-sm-block">Nông sản sạch</small>
                </a>
            </div>
        @endforeach
    </div>
</div>

{{-- 5. SẢN PHẨM BÁN CHẠY NHẤT (3 sản phẩm 1 hàng trên điện thoại, 6 sản phẩm trên desktop) --}}
@if(isset($bestSellers) && $bestSellers->count() > 0)
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-end mb-3">
            <div>
                <span class="badge bg-danger-subtle text-danger fw-bold uppercase">Ưa chuộng nhất</span>
                <h3 class="fw-bold text-dark mt-1 mb-0">Top Sản Phẩm Bán Chạy Trong Tuần</h3>
            </div>
            <a href="{{ route('products.index') }}" class="text-success text-decoration-none fw-semibold">
                Xem tất cả <i class="bi bi-chevron-right"></i>
            </a>
        </div>
        <div class="row row-cols-3 row-cols-sm-3 row-cols-md-4 row-cols-lg-6 g-2 g-md-2.5 g-lg-3">
            @foreach ($bestSellers->take($bestSellers->count() >= 12 ? 12 : 6) as $product)
                <div class="col">
                    @include('partials.product-card', ['product' => $product])
                </div>
            @endforeach
        </div>
    </div>
@endif



{{-- 7. CÔNG THỨC MÓN NGON & MUA NHANH NGUYÊN LIỆU --}}
@if(!empty($recipes) && $recipes->count())
    <div class="container py-4 mb-3">
        <div class="d-flex justify-content-between align-items-end mb-3">
            <div>
                <span class="badge bg-warning-subtle text-warning fw-bold uppercase">Món Ngon Gia Đình</span>
                <h3 class="fw-bold text-dark mt-1 mb-0">Hôm Nay Bạn Muốn Nấu Gì?</h3>
            </div>
            <a href="{{ route('recipes.index') }}" class="text-success text-decoration-none fw-semibold">
                Xem tất cả công thức <i class="bi bi-chevron-right"></i>
            </a>
        </div>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 g-md-4">
            @foreach ($recipes->take(6) as $recipe)
                <div class="col">
                    @include('partials.recipe-card', ['recipe' => $recipe])
                </div>
            @endforeach
        </div>
    </div>
@endif

{{-- 8. NEWSLETTER / NHẬN VOUCHER (Khuyến khích đăng ký thành viên) --}}
<div class="container py-4 mb-4">
    <div class="card border-0 rounded-4 text-white p-4 p-md-5 shadow-sm" style="background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%);">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold mb-2"><i class="bi bi-gift-fill me-1"></i> Quà tặng thành viên mới</span>
                <h3 class="fw-bold text-white mb-2">Nhận Ngay Voucher Giảm 50.000đ</h3>
                <p class="text-white-50 mb-0">Đăng ký thành viên hoặc để lại email để nhận thông báo khuyến mãi và công thức nấu ăn mới nhất mỗi tuần.</p>
            </div>
            <div class="col-lg-5 text-lg-end">
                <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                    @guest
                        <a href="{{ route('register') }}" class="btn btn-warning fw-bold rounded-pill px-4 py-2.5 text-dark shadow-sm">
                            <i class="bi bi-person-plus-fill me-1"></i> Đăng Ký Tài Khoản
                        </a>
                    @else
                        <a href="{{ route('vouchers.index') }}" class="btn btn-warning fw-bold rounded-pill px-4 py-2.5 text-dark shadow-sm">
                            <i class="bi bi-ticket-perforated me-1"></i> Săn Thêm Voucher
                        </a>
                    @endguest
                    <a href="{{ route('products.index') }}" class="btn btn-outline-light fw-bold rounded-pill px-4 py-2.5 shadow-sm">
                        Mua Sắm Ngay
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
