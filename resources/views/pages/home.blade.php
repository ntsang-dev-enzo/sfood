@extends('layouts.app')
@section('title', 'SFood - Nông Sản & Thực Phẩm Tươi Sống Chuẩn VietGAP')

@section('content')
<style>
    /* Hero Carousel & Banner Dimensions - Chuẩn hóa tỷ lệ & Responsive */
    .hero-carousel-container {
        border-radius: 1.25rem;
        overflow: hidden;
    }
    #homeCarousel, 
    #homeCarousel .carousel-inner, 
    #homeCarousel .carousel-item {
        height: 440px;
    }
    @media (max-width: 991px) and (min-width: 768px) {
        #homeCarousel, 
        #homeCarousel .carousel-inner, 
        #homeCarousel .carousel-item {
            height: 340px;
        }
    }
    @media (max-width: 767px) {
        .hero-carousel-container {
            border-radius: 0.85rem;
        }
        #homeCarousel, 
        #homeCarousel .carousel-inner, 
        #homeCarousel .carousel-item {
            height: 220px;
        }
    }
    #homeCarousel img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }
    .promo-card-side {
        border-radius: 1.25rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .promo-card-side:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12) !important;
    }
    @media (min-width: 992px) {
        .promo-side-wrapper {
            height: 440px;
        }
        .promo-card-side {
            height: calc((440px - 1rem) / 2);
        }
    }
    @media (max-width: 991px) and (min-width: 768px) {
        .promo-card-side {
            min-height: 185px;
        }
    }
    @media (max-width: 767px) {
        .promo-card-side {
            min-height: 160px;
            border-radius: 0.85rem;
        }
    }

    /* Carousel Controls & Indicators */
    .hero-carousel-container .carousel-control-prev,
    .hero-carousel-container .carousel-control-next {
        width: 38px;
        height: 38px;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(4px);
        border-radius: 50%;
        opacity: 0;
        transition: all 0.25s ease-in-out;
        margin: 0 14px;
        border: none;
        z-index: 10;
    }
    .hero-carousel-container:hover .carousel-control-prev,
    .hero-carousel-container:hover .carousel-control-next {
        opacity: 0.95;
    }
    .hero-carousel-container .carousel-control-prev:hover,
    .hero-carousel-container .carousel-control-next:hover {
        background: #ffffff;
        opacity: 1;
        transform: translateY(-50%) scale(1.08);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    .hero-carousel-container .carousel-control-prev-icon,
    .hero-carousel-container .carousel-control-next-icon {
        width: 16px;
        height: 16px;
        filter: invert(1) grayscale(100);
    }
    .hero-carousel-container .carousel-indicators {
        margin-bottom: 0.85rem;
        z-index: 10;
    }
    .hero-carousel-container .carousel-indicators [data-bs-target] {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin: 0 4px;
        background-color: rgba(255, 255, 255, 0.5);
        border: none;
        transition: all 0.3s ease;
    }
    .hero-carousel-container .carousel-indicators .active {
        width: 24px;
        border-radius: 12px;
        background-color: #ffffff;
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
    <div class="row g-3 align-items-stretch">
        {{-- Carousel Chính (Col 8 trên desktop, Col 12 trên tablet/mobile) --}}
        <div class="col-12 col-lg-8">
            <div id="homeCarousel" class="carousel slide hero-carousel-container shadow-sm position-relative h-100" data-bs-ride="carousel" data-bs-interval="4500">
                @if(isset($banners) && $banners->count() > 0)
                    <div class="carousel-indicators">
                        @foreach($banners as $index => $banner)
                            <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="{{ $index }}" class="{{ $index === 0 ? 'active' : '' }}" aria-label="Slide {{ $index + 1 }}"></button>
                        @endforeach
                    </div>
                    <div class="carousel-inner">
                        @foreach($banners as $index => $banner)
                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }} position-relative bg-dark">
                                <a href="{{ $banner->link ?: route('products.index') }}" class="d-block w-100 h-100 position-relative text-decoration-none">
                                    <img src="{{ \App\Support\MediaHelper::bannerImage($banner->image_path, $index) }}" 
                                         class="d-block w-100 h-100 object-fit-cover" 
                                         alt="{{ $banner->title ?: 'SFood Banner' }}">
                                    
                                    {{-- Lớp phủ gradient nhẹ nhàng để chữ đọc rõ mà không che mất ảnh --}}
                                    <div class="position-absolute top-0 start-0 w-100 h-100 pe-none"
                                         style="background: linear-gradient(to top, rgba(15, 23, 18, 0.75) 0%, rgba(15, 23, 18, 0.2) 45%, transparent 100%);"></div>

                                    @if($banner->title)
                                        <div class="carousel-caption text-start p-3 p-md-4" 
                                             style="bottom: 1.25rem; left: 1.5rem; right: auto; max-width: min(520px, 85%); z-index: 5;">
                                            <span class="badge bg-warning text-dark px-2.5 py-1 rounded-pill fw-bold small mb-2 d-inline-flex align-items-center">
                                                <i class="bi bi-patch-check-fill me-1"></i> Ưu Đãi Nổi Bật
                                            </span>
                                            <h3 class="fw-bold text-white mb-2 fs-5 fs-md-4 fs-lg-3 lh-sm">{{ $banner->title }}</h3>
                                            <span class="btn btn-sm btn-warning fw-bold rounded-pill px-3 py-1.5 text-dark shadow-sm">
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
                        <div class="carousel-item active position-relative" style="background: linear-gradient(135deg, rgba(27, 67, 50, 0.9) 0%, rgba(45, 106, 79, 0.82) 100%), url('https://images.unsplash.com/photo-1542838132-92c53300491e?w=1600&q=80') center/cover no-repeat;">
                            <div class="container h-100 d-flex align-items-center">
                                <div class="row align-items-center w-100 g-3">
                                    <div class="col-md-8 text-white px-3 px-md-5">
                                        <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold mb-2 d-inline-flex align-items-center">
                                            <i class="bi bi-patch-check-fill me-1"></i> 100% Tươi Sạch &amp; Hữu Cơ
                                        </span>
                                        <h2 class="display-6 fw-bold text-white mb-2 lh-sm fs-4 fs-md-3 fs-lg-2">Nông Sản Sạch Cho Gia Đình</h2>
                                        <p class="lead fs-6 text-white-50 mb-3 pe-lg-3 d-none d-md-block">
                                            Thu hoạch từ nông trại trong lành mỗi sáng. Giao hàng tươi trong 2 giờ tại Cần Thơ, TP.HCM &amp; Hà Nội.
                                        </p>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="{{ route('products.index') }}" class="btn btn-sm btn-warning fw-bold rounded-pill px-3 px-md-4 text-dark shadow-sm">
                                                <i class="bi bi-cart3 me-1"></i> Mua sắm ngay
                                            </a>
                                            <a href="{{ route('recipes.index') }}" class="btn btn-sm btn-outline-light fw-bold rounded-pill px-3 px-md-4 shadow-sm">
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
                        <div class="carousel-item position-relative" style="background: linear-gradient(135deg, rgba(43, 45, 66, 0.9) 0%, rgba(61, 44, 30, 0.85) 100%), url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=1600&q=80') center/cover no-repeat;">
                            <div class="container h-100 d-flex align-items-center">
                                <div class="row align-items-center w-100 g-3">
                                    <div class="col-md-8 text-white px-3 px-md-5">
                                        <span class="badge bg-danger px-3 py-1.5 rounded-pill fw-bold mb-2 d-inline-flex align-items-center">
                                            <i class="bi bi-fire me-1"></i> Món Ngon Mỗi Ngày
                                        </span>
                                        <h2 class="display-6 fw-bold text-white mb-2 lh-sm fs-4 fs-md-3 fs-lg-2">Hôm Nay Bạn Muốn Ăn Gì?</h2>
                                        <p class="lead fs-6 text-white-50 mb-3 pe-lg-3 d-none d-md-block">
                                            Khám phá hàng chục video công thức chuẩn bếp gia đình. Mua trọn gói nguyên liệu cần thiết chỉ với 1 click!
                                        </p>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="{{ route('recipes.index') }}" class="btn btn-sm btn-danger fw-bold rounded-pill px-3 px-md-4 shadow-sm">
                                                <i class="bi bi-journal-bookmark me-1"></i> Xem công thức
                                            </a>
                                            <a href="{{ route('vouchers.index') }}" class="btn btn-sm btn-outline-light fw-bold rounded-pill px-3 px-md-4 shadow-sm">
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
                        <div class="carousel-item position-relative" style="background: linear-gradient(135deg, rgba(13, 59, 102, 0.9) 0%, rgba(29, 53, 87, 0.85) 100%), url('https://images.unsplash.com/photo-1498837167922-ddd27525d352?w=1600&q=80') center/cover no-repeat;">
                            <div class="container h-100 d-flex align-items-center">
                                <div class="row align-items-center w-100 g-3">
                                    <div class="col-md-8 text-white px-3 px-md-5">
                                        <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold mb-2 d-inline-flex align-items-center">
                                            <i class="bi bi-cup-hot-fill me-1"></i> Bữa Sáng Dinh Dưỡng
                                        </span>
                                        <h2 class="display-6 fw-bold text-white mb-2 lh-sm fs-4 fs-md-3 fs-lg-2">Thực Đơn Bữa Sáng Tươi Ngon</h2>
                                        <p class="lead fs-6 text-white-50 mb-3 pe-lg-3 d-none d-md-block">
                                            Thưởng thức bánh mì nướng thơm giòn, phở bò tươi sạch cùng các loại rau củ hữu cơ dinh dưỡng cho cả gia đình.
                                        </p>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="{{ route('products.index', ['category' => 'mon_an_sang']) }}" class="btn btn-sm btn-warning fw-bold rounded-pill px-3 px-md-4 text-dark shadow-sm">
                                                <i class="bi bi-cart3 me-1"></i> Khám Phá Món Ngon
                                            </a>
                                            <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-light fw-bold rounded-pill px-3 px-md-4 shadow-sm">
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

        {{-- Bento Promo Cards Bên Cạnh (Col 4 trên Desktop, 2 Cột trên Tablet, Xếp Dọc trên Mobile) --}}
        <div class="col-12 col-lg-4">
            <div class="d-flex flex-column flex-md-row flex-lg-column gap-3 h-100 promo-side-wrapper">
                {{-- Card 1: Rau Củ Hữu Cơ Đà Lạt --}}
                <div class="promo-card-side flex-fill p-3 p-md-4 text-white d-flex flex-column justify-content-between shadow-sm position-relative overflow-hidden" 
                     style="background: linear-gradient(135deg, rgba(27, 67, 50, 0.92) 0%, rgba(45, 106, 79, 0.88) 100%), url('https://images.unsplash.com/photo-1540420773420-3366772f4999?w=600&q=80') center/cover no-repeat;">
                    <div class="position-absolute end-0 bottom-0 opacity-15 pe-3 pb-2 text-white pe-none">
                        <i class="bi bi-flower1" style="font-size: 5rem; line-height: 1;"></i>
                    </div>
                    <div class="position-relative z-1">
                        <span class="badge bg-warning text-dark px-2.5 py-1 rounded-pill fw-bold small mb-2 d-inline-block">Ưu Đãi Hôm Nay</span>
                        <h5 class="fw-bold text-white mb-1 fs-6 fs-md-5">Rau Củ Hữu Cơ Đà Lạt</h5>
                        <p class="small text-white-50 mb-0">Giảm thêm 20% cho đơn hàng đầu tiên trong tuần</p>
                    </div>
                    <div class="position-relative z-1 pt-2">
                        <a href="{{ route('products.index') }}" class="btn btn-sm btn-light fw-bold text-success rounded-pill px-3 shadow-sm">
                            Xem ngay <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>

                {{-- Card 2: Thịt & Thủy Hải Sản Tươi --}}
                <div class="promo-card-side flex-fill p-3 p-md-4 text-white d-flex flex-column justify-content-between shadow-sm position-relative overflow-hidden" 
                     style="background: linear-gradient(135deg, rgba(139, 30, 15, 0.92) 0%, rgba(193, 18, 31, 0.88) 100%), url('https://images.unsplash.com/photo-1544025162-d76694265947?w=600&q=80') center/cover no-repeat;">
                    <div class="position-absolute end-0 bottom-0 opacity-15 pe-3 pb-2 text-white pe-none">
                        <i class="bi bi-fire" style="font-size: 5rem; line-height: 1;"></i>
                    </div>
                    <div class="position-relative z-1">
                        <span class="badge bg-white text-danger px-2.5 py-1 rounded-pill fw-bold small mb-2 d-inline-block">Tươi Sống Mỗi Ngày</span>
                        <h5 class="fw-bold text-white mb-1 fs-6 fs-md-5">Thịt &amp; Thủy Hải Sản Tươi</h5>
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
