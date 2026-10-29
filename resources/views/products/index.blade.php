@extends('layouts.app')
@section('title', 'Danh Sách Sản Phẩm - SFood Organic')

@section('content')
{{-- Banner đầu trang --}}
<div class="bg-success text-white py-4 shadow-sm" style="background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%);">
    <div class="container">
        <h1 class="h3 fw-bold mb-1"><i class="bi bi-grid-fill me-2"></i>Nông Sản &amp; Thực Phẩm Tươi Sống</h1>
        <p class="text-white-50 mb-0 small">Thực phẩm sạch đạt chuẩn VietGAP, an toàn cho mọi gia đình</p>
    </div>
</div>

<div class="container py-4">
    <div class="row g-4">
        {{-- Sidebar Lọc (Bootstrap 5 Col 3) --}}
        <div class="col-lg-3">
            {{-- Nhóm danh mục --}}
            <div class="card border-0 shadow-sm rounded-3 mb-3 bg-white">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-list-ul text-success me-2"></i>Danh Mục Sản Phẩm</h6>
                </div>
                <div class="list-group list-group-flush small">
                    <a href="{{ request()->fullUrlWithQuery(['category' => null, 'page' => null]) }}" 
                       class="list-group-item list-group-item-action {{ !$categorySlug ? 'active bg-success border-success' : '' }}">
                        <i class="bi bi-collection me-2"></i>Tất cả sản phẩm
                    </a>
                    @foreach ($categories as $cat)
                        <a href="{{ request()->fullUrlWithQuery(['category' => $cat->slug, 'page' => null]) }}" 
                           class="list-group-item list-group-item-action {{ $categorySlug === $cat->slug ? 'active bg-success border-success' : '' }}">
                            <i class="bi bi-tag me-2"></i>{{ $cat->name }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Bộ lọc giá (Form Filter) --}}
            <div class="card border-0 shadow-sm rounded-3 bg-white">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-funnel-fill text-success me-2"></i>Lọc Theo Mức Giá</h6>
                </div>
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('products.index') }}" id="filter-form">
                        @if($categorySlug)
                            <input type="hidden" name="category" value="{{ $categorySlug }}">
                        @endif
                        <input type="hidden" name="sort" id="sort-hidden" value="{{ $sort }}">
                        <input type="hidden" name="search" value="{{ $search }}">

                        {{-- Các khoảng giá mẫu --}}
                        <div class="d-flex flex-column gap-1 mb-3">
                            @php
                                $ranges = [
                                    ['label' => 'Dưới 100.000₫', 'min' => 0, 'max' => 100000],
                                    ['label' => '100.000₫ - 200.000₫', 'min' => 100000, 'max' => 200000],
                                    ['label' => '200.000₫ - 500.000₫', 'min' => 200000, 'max' => 500000],
                                    ['label' => 'Trên 500.000₫', 'min' => 500000, 'max' => ''],
                                ];
                            @endphp
                            @foreach ($ranges as $range)
                                @php
                                    $isActive = ($minPrice == $range['min'] && $maxPrice == $range['max'] && $maxPrice !== '')
                                        || ($minPrice == $range['min'] && $range['max'] === '' && ($maxPrice === null || $maxPrice === ''));
                                @endphp
                                <button type="button" onclick="applyPriceRange('{{ $range['min'] }}', '{{ $range['max'] }}')"
                                    class="btn btn-sm text-start {{ $isActive ? 'btn-success text-white fw-bold' : 'btn-outline-light text-dark' }}">
                                    <i class="bi bi-tag me-1"></i>{{ $range['label'] }}
                                </button>
                            @endforeach
                        </div>

                        {{-- Nhập khoảng giá thủ công --}}
                        <div class="mb-2">
                            <label class="form-label small text-muted mb-1">Từ (₫):</label>
                            <input type="number" name="min_price" id="min_price" value="{{ $minPrice }}" placeholder="0" class="form-control form-control-sm" min="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted mb-1">Đến (₫):</label>
                            <input type="number" name="max_price" id="max_price" value="{{ $maxPrice }}" placeholder="500000" class="form-control form-control-sm" min="0">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-sm btn-success fw-bold">Áp dụng bộ lọc</button>
                            @if($minPrice || $maxPrice || $search)
                                <a href="{{ route('products.index', ['category' => $categorySlug, 'sort' => $sort]) }}" class="btn btn-sm btn-outline-secondary">Xóa bộ lọc</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- Sản phẩm đã xem gần nhất (Ở dưới bộ lọc giá) --}}
            @if(isset($recentlyViewed) && $recentlyViewed->count())
                <div class="card border-0 shadow-sm rounded-3 bg-white mt-3">
                    <div class="card-header bg-white py-3 border-0 pb-1">
                        <h6 class="fw-bold mb-0 text-dark">Sản Phẩm Đã Xem Gần Nhất</h6>
                    </div>
                    <div class="card-body p-2 pt-1">
                        <div class="d-flex flex-column gap-1">
                            @foreach ($recentlyViewed->take(5) as $rProduct)
                                @php
                                    $rVariant = $rProduct->defaultVariant();
                                    $rPrice = $rVariant?->sale_price ?? $rVariant?->price ?? 0;
                                    $rOldPrice = $rVariant?->sale_price ? $rVariant->price : null;
                                @endphp
                                <a href="{{ route('products.show', $rProduct->slug) }}" class="d-flex align-items-center gap-2 p-2 rounded-2 text-decoration-none text-dark border-bottom border-light">
                                    <img src="{{ \App\Support\MediaHelper::productImage($rProduct->thumbnail) }}" 
                                         alt="{{ $rProduct->name }}" 
                                         class="rounded-2 border flex-shrink-0" 
                                         style="width: 44px; height: 44px; object-fit: cover;">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="small fw-semibold text-truncate mb-0 text-dark" title="{{ $rProduct->name }}">
                                            {{ $rProduct->name }}
                                        </div>
                                        <div class="d-flex align-items-baseline gap-1">
                                            <span class="small fw-bold text-success">{{ number_format($rPrice) }}₫</span>
                                            @if($rOldPrice)
                                                <span class="text-muted text-decoration-line-through" style="font-size: 0.68rem;">{{ number_format($rOldPrice) }}₫</span>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Danh Sách Sản Phẩm (Col 9) --}}
        <div class="col-lg-9">
            {{-- Toolbar Tìm kiếm & Sắp xếp --}}
            <div class="card border-0 shadow-sm rounded-3 p-3 mb-4 bg-white">
                <div class="row g-2 align-items-center">
                    <div class="col-md-6">
                        <span class="small text-muted">
                            Hiển thị <strong class="text-dark">{{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }}</strong> trong số <strong class="text-success">{{ $products->total() }}</strong> sản phẩm
                            @if($search)
                                cho từ khóa "<span class="text-danger fw-bold">{{ $search }}</span>"
                            @endif
                        </span>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="d-inline-flex align-items-center gap-2">
                            <label class="small text-muted text-nowrap mb-0"><i class="bi bi-sort-down me-1"></i>Sắp xếp:</label>
                            <select name="sort" onchange="document.getElementById('sort-hidden').value = this.value; document.getElementById('filter-form').submit();" class="form-select form-select-sm shadow-none" style="width: auto;">
                                <option value="popular" @selected($sort === 'popular')>Phổ biến nhất</option>
                                <option value="newest" @selected($sort === 'newest')>Mới nhất</option>
                                <option value="price_asc" @selected($sort === 'price_asc')>Giá tăng dần</option>
                                <option value="price_desc" @selected($sort === 'price_desc')>Giá giảm dần</option>
                                <option value="name_asc" @selected($sort === 'name_asc')>Tên A - Z</option>
                                <option value="name_desc" @selected($sort === 'name_desc')>Tên Z - A</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            @if ($products->count() > 0)
                {{-- Grid Sản phẩm (5 sản phẩm 1 hàng trên desktop: row-cols-lg-5, 3 sản phẩm trên điện thoại: row-cols-3) --}}
                <div class="row row-cols-3 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 g-2 g-md-2.5 g-xl-3">
                    @foreach ($products as $product)
                        <div class="col">
                            @include('partials.product-card', ['product' => $product])
                        </div>
                    @endforeach
                </div>

                {{-- Phân trang Bootstrap --}}
                @if($products->hasPages())
                    <div class="d-flex justify-content-center mt-5">
                        {{ $products->links() }}
                    </div>
                @endif
            @else
                {{-- Khối thông báo không tìm thấy sản phẩm - Hiển thị FULL ở Block lớn --}}
                <div class="card border-0 shadow-sm rounded-4 bg-white text-center py-5 px-3 my-2 w-100">
                    <div class="card-body py-5">
                        <div class="mb-4">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light border p-4 shadow-sm" style="width: 96px; height: 96px;">
                                <i class="bi bi-search text-secondary" style="font-size: 2.75rem;"></i>
                            </span>
                        </div>
                        <h4 class="fw-bold text-dark mb-2">Không tìm thấy sản phẩm phù hợp</h4>
                        <p class="text-muted mx-auto mb-4" style="max-width: 520px; font-size: 0.95rem;">
                            Rất tiếc, chúng tôi không tìm thấy kết quả nào
                            @if($search)
                                phù hợp với từ khóa "<strong class="text-danger">{{ $search }}</strong>"
                            @endif
                            @if($minPrice || $maxPrice)
                                trong mức giá đã chọn
                            @endif
                            @if($categorySlug)
                                thuộc danh mục này
                            @endif.
                            Bạn hãy thử kiểm tra lại chính tả, tìm kiếm với từ khóa khác hoặc xóa bớt tiêu chí lọc.
                        </p>
                        <div class="d-flex justify-content-center flex-wrap gap-2">
                            <a href="{{ route('products.index') }}" class="btn btn-success fw-bold px-4 py-2.5 rounded-pill shadow-sm">
                                Xem tất cả sản phẩm
                            </a>
                            @if($minPrice || $maxPrice || $search || $categorySlug)
                                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary px-4 py-2.5 rounded-pill">
                                    Xóa tất cả bộ lọc
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    function applyPriceRange(min, max) {
        document.getElementById('min_price').value = min;
        document.getElementById('max_price').value = max;
        document.getElementById('filter-form').submit();
    }
</script>
@endsection
