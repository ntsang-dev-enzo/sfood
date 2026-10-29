<div class="container mt-4 mb-2">
    <div class="d-flex gap-2">
        <a href="{{ route('favorites.index') }}"
           class="btn btn-sm rounded-pill px-4 py-2 fw-semibold {{ request()->routeIs('favorites.index') ? 'btn-success shadow-sm' : 'btn-light border' }}">
            <i class="bi bi-cart3 me-1"></i> Sản phẩm yêu thích
        </a>
        <a href="{{ route('favorites.videos') }}"
           class="btn btn-sm rounded-pill px-4 py-2 fw-semibold {{ request()->routeIs('favorites.videos') ? 'btn-success shadow-sm' : 'btn-light border' }}">
            <i class="bi bi-play-circle me-1"></i> Video &amp; Công thức
        </a>
    </div>
</div>
