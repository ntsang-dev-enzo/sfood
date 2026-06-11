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
            <form method="GET" action="{{ route('products.index') }}" id="filter-form" class="flex flex-col gap-6 mb-6">
                <!-- Search and Category -->
                <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-end">
                    <div class="flex-1 w-full">
                        <label class="block text-sm font-medium text-earth-700 mb-2">Tìm kiếm</label>
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
                    <button type="submit" class="btn-primary !py-3 !px-8">Lọc</button>
                </div>

                <!-- Price Filter -->
                <div class="pt-6 border-t border-cream-200">
                    <label class="block text-sm font-medium text-earth-700 mb-3">Khoảng giá</label>

                    <div class="flex flex-wrap gap-2 mb-4">
                        @php
                            $ranges = [
                                ['label' => 'Dưới 100k', 'min' => 0, 'max' => 100000],
                                ['label' => '100k - 200k', 'min' => 100000, 'max' => 200000],
                                ['label' => '200k - 500k', 'min' => 200000, 'max' => 500000],
                                ['label' => 'Trên 500k', 'min' => 500000, 'max' => ''],
                            ];
                        @endphp
                        @foreach ($ranges as $range)
                            @php
                                $isActive = ($minPrice == $range['min'] && $maxPrice == $range['max'] && $maxPrice !== '')
                                    || ($minPrice == $range['min'] && $range['max'] === '' && ($maxPrice === null || $maxPrice === ''));
                            @endphp
                            <button type="button" onclick="applyPriceRange('{{ $range['min'] }}', '{{ $range['max'] }}')"
                                class="px-4 py-1.5 rounded-full border text-sm transition-all {{ $isActive ? 'bg-leaf-600 border-leaf-600 text-white' : 'border-earth-200 text-earth-600 hover:border-leaf-600 hover:text-leaf-600 bg-white shadow-sm' }}">
                                {{ $range['label'] }}
                            </button>
                        @endforeach
                        <button type="button" onclick="applyPriceRange('', '')"
                            class="px-4 py-1.5 rounded-full border border-earth-100 text-sm text-earth-400 hover:border-red-300 hover:text-red-500 transition-all bg-white">
                            Xóa lọc
                        </button>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="relative w-36">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-earth-400 text-sm">đ</span>
                            <input type="number" name="min_price" id="min_price" value="{{ $minPrice }}" placeholder="Từ"
                                class="input-field !pl-7 !py-2 text-sm" min="0">
                        </div>
                        <span class="text-earth-300">—</span>
                        <div class="relative w-36">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-earth-400 text-sm">đ</span>
                            <input type="number" name="max_price" id="max_price" value="{{ $maxPrice }}" placeholder="Đến"
                                class="input-field !pl-7 !py-2 text-sm" min="0">
                        </div>
                        <button type="submit" class="btn-secondary !py-2 !px-4 !text-sm">Áp dụng</button>
                    </div>
                </div>
            </form>

            <form method="POST" action="{{ route('products.ai-search') }}" enctype="multipart/form-data"
                class="p-5 bg-leaf-50 rounded-xl border border-leaf-200">
                @csrf
                <input type="hidden" name="search" value="{{ $search }}">
                <input type="hidden" name="category" value="{{ $categorySlug }}">
                <input type="hidden" name="min_price" value="{{ $minPrice }}">
                <input type="hidden" name="max_price" value="{{ $maxPrice }}">
                <h3 class="font-semibold text-leaf-800 mb-1">🤖 Tìm kiếm bằng hình ảnh (CLIP AI)</h3>
                <p class="text-sm text-leaf-700 mb-3">Tải ảnh thực phẩm, AI nhận diện và gợi ý sản phẩm tương tự.</p>
                <div class="flex flex-wrap gap-3 items-center">
                    <input type="file" name="ai_image" accept="image/*" required class="text-sm text-earth-600">
                    <button type="submit" class="btn-primary !py-2 !px-4 !text-sm">Nhận diện ảnh</button>
                </div>
                @error('ai_image')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </form>

            @if ($aiResults)
                <div class="mt-4 p-5 bg-cream-50 border border-leaf-300 rounded-xl">
                    <p class="font-medium text-earth-800">Kết quả AI: <span
                            class="text-leaf-700">{{ $aiResults['label'] ?? '' }}</span>
                        @if (!empty($aiResults['confidence']))
                            <span class="text-sm text-earth-500">({{ round(($aiResults['confidence'] ?? 0) * 100) }}% tin
                                cậy)</span>
                        @endif
                    </p>
                    @if (!empty($aiResults['products']))
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                            @foreach ($aiResults['products'] as $p)
                                <a href="{{ route('products.show', $p['slug']) }}"
                                    class="group relative overflow-hidden rounded-2xl border border-cream-200 bg-white shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">

                                    {{-- Badge --}}
                                    <div
                                        class="absolute top-3 right-3 bg-leaf-100 text-leaf-700 text-[11px] font-semibold px-2 py-1 rounded-full z-10">
                                        AI gợi ý
                                    </div>

                                    {{-- Image --}}
                                    <div class="bg-cream-50 p-4 flex items-center justify-center h-40 overflow-hidden">
                                        <img src="{{ asset('storage/' . $p['thumbnail']) }}" alt="{{ $p['name'] }}"
                                            class="max-h-28 object-contain group-hover:scale-110 transition duration-300">
                                    </div>

                                    {{-- Content --}}
                                    <div class="p-4">
                                        <h3
                                            class="text-sm md:text-base font-semibold text-earth-800 line-clamp-2 min-h-[48px] group-hover:text-leaf-700 transition">
                                            {{ $p['name'] }}
                                        </h3>

                                        <div class="flex items-center justify-between mt-3">
                                            <p class="text-leaf-700 text-lg font-bold">
                                                {{ number_format($p['min_price']) }}đ
                                            </p>

                                            <div
                                                class="w-9 h-9 rounded-full bg-leaf-600 text-white flex items-center justify-center group-hover:bg-leaf-700 transition">
                                                →
                                            </div>
                                        </div>
                                    </div>
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
    <script>
        function applyPriceRange(min, max) {
            document.getElementById('min_price').value = min;
            document.getElementById('max_price').value = max;
            document.getElementById('filter-form').submit();
        }
    </script>
@endsection
