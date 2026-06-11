@extends('layouts.app')
@section('title', 'Video yêu thích - SFood')
@section('content')
<div class="bg-earth-800 text-cream-50 py-12">
    <div class="max-w-7xl mx-auto px-4">
        <h1 class="text-3xl font-bold">Yêu thích</h1>
        <p class="text-cream-300 mt-2">{{ $recipes->count() }} video</p>
    </div>
</div>

@include('favorites._tabs')

<div class="max-w-7xl mx-auto px-4 py-8">
    @if ($recipes->isEmpty())
        <div class="card p-16 text-center">
            <p class="text-5xl mb-4">🤍</p>
            <p class="text-earth-600 text-lg mb-6">Bạn chưa yêu thích video nào</p>
            <a href="{{ route('recipes.index') }}" class="btn-primary">Khám phá công thức</a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($recipes as $recipe)
                @include('partials.recipe-card', ['recipe' => $recipe])
            @endforeach
        </div>
    @endif
</div>
@endsection
