@extends('layouts.app')
@section('title', 'Sản phẩm - SFood')
@section('content')
<div class="bg-earth-800 text-cream-50 py-12">
    <div class="max-w-7xl mx-auto px-4">
        <h1 class="text-3xl font-bold">Sản phẩm</h1>
        <p class="text-cream-300 mt-2">Thực phẩm tươi sạch, giao tận nhà</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="card p-6 mb-8">
        <form method="GET" action="{{ route('products.index') }}" class="flex flex-col lg:flex-row gap-4 items-start lg:items-end mb-6">
            <div class="flex-1 w-full">
                <label class="block text-sm font-medium text-earth-700 mb-2">Tìm kiếm có gợi ý</label>
                @include('partials.search-box', ['value' => $search, 'standalone' => false])
            </div>
            <div>
                <label class="block text-sm font-medium text-earth-700 mb-2">Danh mục</label>
                <select name="category" onchange="this.form.submit()" class="input-field !w-auto min-w-[160px]">
                    <option value="">Tất cả</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->slug }}" @selected($categorySlug === $cat->slug)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-primary !py-3 !px-5">Tìm</button>
        </form>

        <form method="POST" action="{{ route('products.ai-search') }}" enctype="multipart/form-data" class="p-5 bg-leaf-50 rounded-xl border border-leaf-200">
            @csrf
            <input type="hidden" name="search" value="{{ $search }}">
            <input type="hidden" name="category" value="{{ $categorySlug }}">
            <h3 class="font-semibold text-leaf-800 mb-1">🤖 Tìm kiếm bằng hình ảnh (CLIP AI)</h3>
            <p class="text-sm text-leaf-700 mb-3">Tải ảnh thực phẩm, AI nhận diện và gợi ý sản phẩm tương tự.</p>
            <div class="flex flex-wrap gap-3 items-center">
                <input type="file" name="ai_image" accept="image/*" required class="text-sm text-earth-600">
                <button type="submit" class="btn-primary !py-2 !px-4 !text-sm">Nhận diện ảnh</button>
            </div>
            @error('ai_image') <p class="text-red-600 text-sm mt-2">{{ $message }}</p> @enderror
        </form>

        @if ($aiResults)
            <div class="mt-4 p-5 bg-cream-50 border border-leaf-300 rounded-xl">
                <p class="font-medium text-earth-800">Kết quả AI: <span class="text-leaf-700">{{ $aiResults['label'] ?? '' }}</span>
                    @if (!empty($aiResults['confidence']))
                        <span class="text-sm text-earth-500">({{ round(($aiResults['confidence'] ?? 0) * 100) }}% tin cậy)</span>
                    @endif
                </p>
                @if (!empty($aiResults['products']))
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-3">
                        @foreach ($aiResults['products'] as $p)
                            <a href="{{ route('products.show', $p['slug']) }}" class="p-3 border border-cream-300 rounded-xl hover:border-leaf-500 transition bg-white">
                                <p class="text-sm font-medium text-earth-800">{{ $p['name'] }}</p>
                                <p class="text-leaf-700 text-sm font-semibold">{{ number_format($p['min_price']) }}đ</p>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
        @forelse ($products as $product)
            @include('partials.product-card', ['product' => $product])
        @empty
            <p class="col-span-full text-center text-earth-500 py-12">Không tìm thấy sản phẩm.</p>
        @endforelse
    </div>

    <div class="mt-10">{{ $products->links() }}</div>
</div>
@endsection
