@extends('layouts.app')
@section('title', 'Video yêu thích - SFood')

@section('content')
<div class="bg-body-tertiary py-4 min-vh-100">
    <div class="container py-2">
        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb bg-white p-3 rounded-3 shadow-sm border mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-success"><i class="bi bi-house-door me-1"></i>{{ __('app.home_title') }}</a></li>
                <li class="breadcrumb-item active fw-semibold" aria-current="page">Video yêu thích</li>
            </ol>
        </nav>

        @include('favorites._tabs')

        @if ($recipes->isEmpty())
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white my-3">
                <i class="bi bi-play-circle display-3 text-muted opacity-50 mb-3"></i>
                <h5 class="fw-bold text-dark">Bạn chưa lưu video nấu ăn nào</h5>
                <p class="text-muted small">Lưu lại các video công thức để trổ tài nấu ăn cho gia đình nhé.</p>
                <div class="mt-2">
                    <a href="{{ route('recipes.index') }}" class="btn btn-success rounded-pill px-4 py-2 fw-semibold">
                        <i class="bi bi-journal-bookmark me-1"></i> Khám phá công thức
                    </a>
                </div>
            </div>
        @else
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 my-3">
                @foreach ($recipes as $recipe)
                    <div class="col">
                        @include('partials.recipe-card', ['recipe' => $recipe])
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
