@extends('layouts.app')

@section('title', $recipe->title . ' - SFood')

@section('content')
<div class="bg-body-tertiary py-4 min-vh-100">
    <div class="container py-2">

        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb bg-white p-3 rounded-3 shadow-sm border mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-success"><i class="bi bi-house-door me-1"></i>{{ __('app.home_title') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('recipes.index') }}" class="text-decoration-none text-success">Công thức món ngon</a></li>
                <li class="breadcrumb-item active text-truncate fw-semibold" aria-current="page" style="max-width: 320px;">{{ $recipe->title }}</li>
            </ol>
        </nav>

        <div class="row g-4 align-items-start">
            {{-- Main Column: Video / Image / Instructions --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-md-5 mb-4">
                    <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                        <h1 class="fw-bold text-dark fs-2 lh-sm mb-0">{{ $recipe->title }}</h1>
                        @if ($recipe->video_url)
                            <button type="button"
                                class="btn {{ $isFavorited ? 'btn-danger' : 'btn-outline-danger' }} rounded-pill px-3 py-2 favorite-btn flex-shrink-0"
                                data-url="{{ route('recipes.favorite', $recipe) }}"
                                data-favorited="{{ $isFavorited ? 'true' : 'false' }}"
                                data-login-url="{{ route('login') }}"
                                data-authenticated="{{ auth()->check() ? 'true' : 'false' }}"
                                @guest title="Đăng nhập để yêu thích" @endguest>
                                <span class="favorite-icon"><i class="bi {{ $isFavorited ? 'bi-heart-fill' : 'bi-heart' }}"></i></span>
                            </button>
                        @endif
                    </div>

                    <div class="d-flex align-items-center gap-3 text-muted small mb-4 pb-3 border-bottom">
                        <span><i class="bi bi-stopwatch text-warning me-1"></i>{{ $recipe->cook_time }} phút</span>
                        <span>•</span>
                        <span><i class="bi bi-eye text-primary me-1"></i>{{ number_format($recipe->view_count) }} lượt xem</span>
                        <span>•</span>
                        <span><i class="bi bi-shield-check text-success me-1"></i>Công thức chuẩn bếp SFood</span>
                    </div>

                    {{-- MEDIA: VIDEO OR THUMBNAIL --}}
                    @if ($recipe->video_url)
                        <div class="ratio ratio-16x9 rounded-4 overflow-hidden shadow-sm mb-4 bg-dark">
                            @if (str_contains($recipe->video_url, 'youtube.com') || str_contains($recipe->video_url, 'youtu.be'))
                                @php
                                    preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $recipe->video_url, $m);
                                    $ytId = $m[1] ?? '';
                                @endphp
                                <iframe src="https://www.youtube.com/embed/{{ $ytId }}" allowfullscreen class="w-100 h-100 border-0"></iframe>
                            @else
                                <video controls class="w-100 h-100 object-fit-cover" poster="{{ $recipe->thumbnail ? asset('storage/'.$recipe->thumbnail) : '' }}">
                                    <source src="{{ $recipe->video_url }}">
                                </video>
                            @endif
                        </div>
                    @elseif ($recipe->thumbnail)
                        <div class="ratio ratio-16x9 rounded-4 overflow-hidden shadow-sm mb-4">
                            <img src="{{ asset('storage/'.$recipe->thumbnail) }}" class="w-100 h-100 object-fit-cover" alt="{{ $recipe->title }}">
                        </div>
                    @endif

                    {{-- INSTRUCTIONS --}}
                    <h3 class="fw-bold text-dark fs-4 mb-3">
                        <i class="bi bi-journal-text text-success me-2"></i>Hướng dẫn thực hiện
                    </h3>
                    <div class="lh-lg text-secondary fst-normal" style="white-space: pre-line;">
                        {{ $recipe->description }}
                    </div>
                </div>
            </div>

            {{-- Sidebar Column: Ingredients & Quick Buy --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4 position-sticky" style="top: 90px;">
                    <h4 class="fw-bold text-dark fs-5 mb-3 d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-basket3-fill text-success me-2"></i>Nguyên liệu cần chuẩn bị</span>
                        <span class="badge bg-success-subtle text-success rounded-pill">{{ $recipe->ingredients->count() }} món</span>
                    </h4>

                    @php $totalPrice = 0; @endphp
                    <div class="list-group list-group-flush mb-4">
                        @foreach ($recipe->ingredients as $ingredient)
                            @php 
                                $v = $ingredient->productVariant; 
                                $qty = max(1, (int) ceil($ingredient->quantity));
                                $itemPrice = $v ? ($v->effectivePrice() * $qty) : 0;
                                $totalPrice += $itemPrice;
                            @endphp
                            @if($v && $v->product)
                                <div class="list-group-item px-0 py-3 d-flex align-items-center justify-content-between border-bottom">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0 fs-6">{{ $v->product->name }}</h6>
                                        <small class="text-muted">{{ $ingredient->quantity_label }} ({{ $v->name }})</small>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-bold text-danger small">{{ number_format($itemPrice) }}đ</span>
                                        <form method="POST" action="{{ route('products.add-to-cart', $v->product->slug) }}">
                                            @csrf
                                            <input type="hidden" name="variant_id" value="{{ $v->id }}">
                                            <input type="hidden" name="quantity" value="{{ $qty }}">
                                            <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-2 py-1" title="Thêm vào giỏ">
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    {{-- TOTAL & QUICK BUY --}}
                    <div class="p-3 bg-light rounded-3 mb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="fw-bold text-dark">Tổng tiền nguyên liệu:</span>
                            <span class="fs-4 fw-bold text-danger">{{ number_format($totalPrice) }}đ</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('recipes.quick-buy', $recipe) }}">
                        @csrf
                        <button type="submit" class="btn btn-success w-100 py-3 fw-bold rounded-pill shadow-sm d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-lightning-charge-fill"></i>
                            <span>Mua nhanh tất cả nguyên liệu</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
