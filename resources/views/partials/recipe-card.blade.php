@php use App\Support\MediaHelper; @endphp
<div class="card card-product h-100 border-0 shadow-sm overflow-hidden">
    <a href="{{ route('recipes.show', $recipe) }}" class="text-decoration-none text-dark d-flex flex-column h-100">
        <div class="position-relative overflow-hidden bg-light" style="aspect-ratio: 16/9;">
            <img src="{{ MediaHelper::recipeImage($recipe->thumbnail, $recipe->id) }}" alt="{{ $recipe->title }}"
                class="card-img-top w-100 h-100 object-fit-cover">
            <span class="position-absolute top-0 end-0 m-2 badge bg-dark bg-opacity-75 shadow-sm">
                <i class="bi bi-clock-history me-1"></i>{{ $recipe->cook_time }} phút
            </span>
            @if($recipe->category)
                <span class="position-absolute top-0 start-0 m-2 badge bg-warning text-dark shadow-sm">
                    {{ $recipe->category->name }}
                </span>
            @endif
        </div>
        <div class="card-body p-3 d-flex flex-column flex-grow-1">
            <h6 class="card-title fw-bold text-dark mb-2 text-truncate-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.5rem; line-height: 1.25rem;">
                {{ $recipe->title }}
            </h6>
            <p class="card-text text-muted small mb-3 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                {{ Str::limit($recipe->description, 80) }}
            </p>
            <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                <span class="small text-success fw-semibold"><i class="bi bi-fire me-1 text-danger"></i>Món ngon mỗi ngày</span>
                <span class="btn btn-sm btn-outline-success rounded-pill px-2 py-0.5 small">Xem công thức</span>
            </div>
        </div>
    </a>
</div>
