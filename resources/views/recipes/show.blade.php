@extends('layouts.app')
@section('title', $recipe->title.' - SFood')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <nav class="text-sm text-earth-500 mb-6">
        <a href="{{ route('recipes.index') }}" class="hover:text-leaf-700">← Quay lại công thức</a>
    </nav>

    <div class="grid lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
            <h1 class="text-3xl font-bold text-earth-800 mb-4">{{ $recipe->title }}</h1>

            @if ($recipe->video_url)
                <div class="card aspect-video mb-6">
                    @if (str_contains($recipe->video_url, 'youtube.com') || str_contains($recipe->video_url, 'youtu.be'))
                        @php
                            preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $recipe->video_url, $m);
                            $ytId = $m[1] ?? '';
                        @endphp
                        <iframe class="w-full h-full rounded-2xl" src="https://www.youtube.com/embed/{{ $ytId }}" allowfullscreen></iframe>
                    @else
                        <video controls class="w-full h-full rounded-2xl" poster="{{ $recipe->thumbnail ? asset('storage/'.$recipe->thumbnail) : '' }}">
                            <source src="{{ $recipe->video_url }}">
                        </video>
                    @endif
                </div>
            @elseif ($recipe->thumbnail)
                <img src="{{ asset('storage/'.$recipe->thumbnail) }}" class="w-full rounded-2xl shadow mb-6" alt="">
            @endif

            <div class="card p-6">
                <h2 class="font-bold text-xl text-earth-800 mb-3">Hướng dẫn nấu</h2>
                <p class="text-earth-600 whitespace-pre-line leading-relaxed">{{ $recipe->description }}</p>
                <p class="text-earth-500 text-sm mt-4 flex items-center gap-2">
                    <span class="badge bg-earth-100 text-earth-700">⏱ {{ $recipe->cook_time }} phút</span>
                    <span class="badge bg-leaf-100 text-leaf-800">👁 {{ number_format($recipe->view_count) }} lượt xem</span>
                </p>
            </div>
        </div>

        <div>
            <div class="card p-6 sticky top-28">
                <h2 class="font-bold text-xl text-earth-800 mb-4">🧺 Nguyên liệu</h2>
                <ul class="space-y-3 mb-6">
                    @foreach ($recipe->ingredients as $ingredient)
                        @php $v = $ingredient->productVariant; @endphp
                        <li class="flex justify-between items-center text-sm border-b border-cream-300 pb-3">
                                <div>
                                    <p class="font-medium text-earth-800">{{ $v->product->name }}</p>
                                    <p class="text-earth-500">{{ $ingredient->quantity_label }} ({{ $v->name }})</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-leaf-700 font-semibold">{{ number_format($v->effectivePrice()) }}đ</span>

                                    <form method="POST" action="{{ route('products.add-to-cart', $v->product->slug) }}">
                                        @csrf
                                        <input type="hidden" name="variant_id" value="{{ $v->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="px-3 py-1 rounded-lg bg-leaf-600 text-white text-sm hover:bg-leaf-700 transition">Thêm</button>
                                    </form>
                                </div>
                            </li>
                    @endforeach
                </ul>

                <form method="POST" action="{{ route('recipes.quick-buy', $recipe) }}">
                    @csrf
                    <button type="submit" class="w-full bg-earth-600 hover:bg-earth-700 text-cream-50 py-3.5 rounded-xl font-bold text-lg transition shadow">
                        ⚡ Mua nhanh tất cả nguyên liệu
                    </button>
                </form>
                <a href="{{ route('cart') }}" class="block text-center mt-3 text-leaf-700 text-sm font-medium hover:underline">Xem giỏ hàng →</a>
            </div>
        </div>
    </div>
</div>
@endsection
