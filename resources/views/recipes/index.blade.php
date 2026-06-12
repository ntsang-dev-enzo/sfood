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
    <div class="max-w-xl mb-6">
        @include('partials.search-box', [
            'action' => route('recipes.index'),
            'aiAction' => route('recipes.ai-search'),
            'value' => $search,
            'placeholder' => 'Tìm công thức, món ăn...',
            'showVoice' => true,
        ])
    </div>

    <!-- Filter Module -->
    <form method="GET" action="{{ route('recipes.index') }}" class="bg-white p-4 rounded-xl shadow-sm border border-cream-200 mb-8 flex flex-wrap gap-4 items-end">
        @if($search)
            <input type="hidden" name="search" value="{{ $search }}">
        @endif
        
        <div class="flex-1 min-w-[150px]">
            <label class="block text-sm font-bold text-earth-700 mb-1">Danh mục</label>
            <select name="category" class="w-full border-cream-200 rounded-lg focus:ring-leaf-500 focus:border-leaf-500 text-sm">
                <option value="">Tất cả danh mục</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->slug }}" {{ $categorySlug === $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-bold text-earth-700 mb-1">Thời gian nấu (phút)</label>
            <div class="flex items-center gap-2">
                <input type="number" name="min_time" value="{{ $minTime }}" placeholder="Từ" class="w-full border-cream-200 rounded-lg focus:ring-leaf-500 focus:border-leaf-500 text-sm">
                <span class="text-earth-400">-</span>
                <input type="number" name="max_time" value="{{ $maxTime }}" placeholder="Đến" class="w-full border-cream-200 rounded-lg focus:ring-leaf-500 focus:border-leaf-500 text-sm">
            </div>
        </div>

        <div class="flex-1 min-w-[150px]">
            <label class="block text-sm font-bold text-earth-700 mb-1">Sắp xếp</label>
            <select name="sort" class="w-full border-cream-200 rounded-lg focus:ring-leaf-500 focus:border-leaf-500 text-sm">
                <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Mới nhất</option>
                <option value="shortest" {{ $sort === 'shortest' ? 'selected' : '' }}>Thời gian ngắn nhất</option>
                <option value="popular" {{ $sort === 'popular' ? 'selected' : '' }}>Phổ biến nhất</option>
            </select>
        </div>

        <div class="flex gap-2 w-full md:w-auto">
            <button type="submit" class="btn-primary flex-1 md:w-auto h-[42px] px-6">Lọc kết quả</button>
            <a href="{{ route('recipes.index') }}" class="flex items-center justify-center h-[42px] px-4 text-earth-600 bg-cream-100 hover:bg-cream-200 rounded-xl transition font-medium border border-cream-200">Xóa lọc</a>
        </div>
    </form>

    @if(isset($aiResults) && isset($aiResults['recipes']) && count($aiResults['recipes']) > 0)
        <div class="mb-8 bg-gradient-to-br from-orange-50 to-cream-50 dark:from-gray-800 dark:to-gray-900 border border-orange-200 dark:border-gray-700 shadow-sm rounded-xl p-5">
            <div class="flex items-center gap-2 mb-4">
                <span class="text-2xl">✨</span>
                <h2 class="text-lg font-bold text-orange-800 dark:text-orange-400">Kết quả tìm kiếm bằng hình ảnh</h2>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach(collect($aiResults['recipes'] ?? [])->take(6) as $res)
                    @php
                        $r = null;
                        if ($res instanceof \App\Models\Recipe) {
                            $r = $res;
                        } elseif (is_array($res)) {
                            $rId = $res['id'] ?? $res['recipe_id'] ?? null;
                            if ($rId) $r = \App\Models\Recipe::find($rId);
                        } elseif (is_numeric($res) || is_string($res)) {
                            $r = \App\Models\Recipe::find($res);
                        } elseif (is_object($res)) {
                            $rId = $res->id ?? $res->recipe_id ?? null;
                            if ($rId) $r = \App\Models\Recipe::find($rId);
                        }
                    @endphp
                    @if($r)
                        @include('partials.recipe-card', ['recipe' => $r])
                    @else
                        <div class="p-4 bg-red-100 text-red-800">
                            <p>Debug: Không thể hiển thị công thức.</p>
                            <p>Type of res: {{ gettype($res) }}</p>
                            <p>Class of res: {{ is_object($res) ? get_class($res) : 'N/A' }}</p>
                            <p>Dump: {{ json_encode($res) }}</p>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

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
