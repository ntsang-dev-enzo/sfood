@extends('layouts.app')
@section('title', 'Sản phẩm yêu thích - SFood')

@section('content')
<div class="bg-body-tertiary py-4 min-vh-100">
    <div class="container py-2">
        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb bg-white p-3 rounded-3 shadow-sm border mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-success"><i class="bi bi-house-door me-1"></i>{{ __('app.home_title') }}</a></li>
                <li class="breadcrumb-item active fw-semibold" aria-current="page">Sản phẩm yêu thích</li>
            </ol>
        </nav>

        @include('favorites._tabs')

        @if ($products->isEmpty())
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white my-3">
                <i class="bi bi-heart display-3 text-muted opacity-50 mb-3"></i>
                <h5 class="fw-bold text-dark">Bạn chưa lưu sản phẩm yêu thích nào</h5>
                <p class="text-muted small">Nhấn vào biểu tượng trái tim ở từng sản phẩm để lưu lại xem sau.</p>
                <div class="mt-2">
                    <a href="{{ route('products.index') }}" class="btn btn-success rounded-pill px-4 py-2 fw-semibold">
                        <i class="bi bi-compass me-1"></i> Khám phá sản phẩm
                    </a>
                </div>
            </div>
        @else
            {{-- 3 sản phẩm 1 hàng trên mobile: row-cols-3 --}}
            <div class="row row-cols-3 row-cols-md-3 row-cols-lg-4 g-2 g-md-3 my-3">
                @foreach ($products as $product)
                    <div class="col">
                        @include('partials.product-card', ['product' => $product])
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
