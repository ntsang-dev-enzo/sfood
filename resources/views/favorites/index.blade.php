@extends('layouts.app')
@section('title', 'Yêu thích - SFood')
@section('content')
<div class="bg-earth-800 text-cream-50 py-12">
    <div class="max-w-7xl mx-auto px-4">
        <h1 class="text-3xl font-bold">Yêu thích</h1>
        <p class="text-cream-300 mt-2">{{ $products->count() }} sản phẩm</p>
    </div>
</div>

@include('favorites._tabs')

<div class="max-w-7xl mx-auto px-4 py-8">
    @if ($products->isEmpty())
        <div class="card p-16 text-center">
            <p class="text-5xl mb-4">🤍</p>
            <p class="text-earth-600 text-lg mb-6">Bạn chưa yêu thích sản phẩm nào</p>
            <a href="{{ route('products.index') }}" class="btn-primary">Khám phá sản phẩm</a>
        </div>
    @else
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
            @foreach ($products as $product)
                @include('partials.product-card', ['product' => $product])
            @endforeach
        </div>
    @endif
</div>
@endsection
