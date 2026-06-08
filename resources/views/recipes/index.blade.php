@extends('layouts.app')
@section('title', 'Công thức nấu ăn - SFood')
@section('content')
<div class="bg-earth-800 text-cream-50 py-12">
    <div class="max-w-7xl mx-auto px-4">
        <h1 class="text-3xl font-bold">Công thức & Video nấu ăn</h1>
        <p class="text-cream-300 mt-2">Học nấu ăn và mua nhanh nguyên liệu</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="max-w-xl mb-8">
        @include('partials.search-box', [
            'action' => route('recipes.index'),
            'value' => $search,
            'placeholder' => 'Tìm công thức, món ăn...',
            'showVoice' => true,
        ])
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($recipes as $recipe)
            @include('partials.recipe-card', ['recipe' => $recipe])
        @empty
            <p class="col-span-full text-center text-earth-500 py-12">Chưa có công thức nào.</p>
        @endforelse
    </div>

    <div class="mt-10">{{ $recipes->links() }}</div>
</div>
@endsection
