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
    <form method="GET" action="{{ route('recipes.index') }}" class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-cream-200 dark:border-gray-700 mb-8 flex flex-wrap gap-4 items-end">
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

    <div class="grid lg:grid-cols-4 gap-8">
        <!-- Recipes List -->
        <div class="lg:col-span-3">
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse ($recipes as $recipe)
                    @include('partials.recipe-card', ['recipe' => $recipe])
                @empty
                    <p class="col-span-full text-center text-earth-500 py-12">Chưa có công thức nào.</p>
                @endforelse
            </div>
            
            <div class="mt-10">{{ $recipes->links() }}</div>
        </div>

        <!-- Voucher Sidebar -->
        <div class="lg:col-span-1">
            <div class="sticky top-24">
                <div class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-gray-800 dark:to-gray-900 rounded-2xl p-5 border border-orange-200 dark:border-gray-700 shadow-sm mb-6">
                    <h3 class="font-bold text-lg text-orange-800 dark:text-orange-400 mb-4 flex items-center gap-2">
                        <span>🎁</span> Trạm Khuyến Mãi
                    </h3>
                    <p class="text-sm text-orange-700 dark:text-orange-300 mb-4">Sưu tầm mã giảm giá để mua nguyên liệu rẻ hơn nhé!</p>
                    
                    <div class="space-y-4">
                        @forelse ($coupons as $coupon)
                            @php
                                $isCollected = in_array($coupon->id, $collectedIds);
                                $isUsedUp = $coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit;
                            @endphp
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-orange-200 dark:border-gray-700 overflow-hidden {{ $isUsedUp ? 'opacity-70 grayscale' : '' }}">
                                <div class="p-4 border-b border-dashed border-orange-200 dark:border-gray-700 relative">
                                    <div class="absolute -bottom-2 -left-2 w-4 h-4 bg-orange-50 dark:bg-gray-900 rounded-full border border-orange-200 dark:border-gray-700"></div>
                                    <div class="absolute -bottom-2 -right-2 w-4 h-4 bg-orange-50 dark:bg-gray-900 rounded-full border border-orange-200 dark:border-gray-700"></div>
                                    
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-bold text-earth-800 dark:text-gray-200">
                                            @if ($coupon->type === 'percent') Giảm {{ $coupon->value }}%
                                            @elseif ($coupon->type === 'fixed') Giảm {{ number_format($coupon->value) }}đ
                                            @else Free Ship
                                            @endif
                                        </h4>
                                    </div>
                                    @if ($coupon->min_order_amount > 0)
                                        <p class="text-xs text-earth-600 dark:text-gray-400">Đơn từ {{ number_format($coupon->min_order_amount) }}đ</p>
                                    @endif
                                    <p class="font-mono font-bold text-sm text-leaf-600 mt-2 bg-cream-50 dark:bg-gray-700 px-2 py-1 rounded inline-block">{{ $coupon->code }}</p>
                                </div>
                                <div class="p-3 bg-cream-50 dark:bg-gray-900 flex flex-col gap-2">
                                    @if($isUsedUp)
                                        <button class="w-full text-xs py-2 rounded-lg font-medium bg-gray-200 dark:bg-gray-700 text-gray-500 cursor-not-allowed" disabled>Hết lượt</button>
                                    @elseif($isCollected)
                                        <button class="w-full text-xs py-2 rounded-lg font-medium bg-cream-200 dark:bg-gray-800 text-earth-600 dark:text-gray-400 border border-cream-300 dark:border-gray-600 pointer-events-none" disabled>✓ Đã thu thập</button>
                                    @else
                                        <button onclick="collectVoucher({{ $coupon->id }}, this)" class="w-full text-xs py-2 rounded-lg font-medium bg-leaf-600 text-white hover:bg-leaf-700 transition shadow-sm">Thu thập ngay</button>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-earth-500">Chưa có mã giảm giá nào.</p>
                        @endforelse
                    </div>
                    
                    <a href="{{ route('vouchers.index') }}" class="block text-center text-sm font-medium text-orange-700 dark:text-orange-400 hover:text-orange-800 mt-4 transition">Xem tất cả ></a>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
    async function collectVoucher(id, btn) {
        @if(!Auth::check())
            window.location.href = '{{ route('login') }}';
            return;
        @endif

        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="animate-pulse">Đang lưu...</span>';
        btn.disabled = true;

        try {
            const res = await fetch(`/vouchers/${id}/collect`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            
            if (res.ok) {
                btn.className = "w-full text-xs py-2 rounded-lg font-medium bg-cream-200 dark:bg-gray-800 text-earth-600 dark:text-gray-400 border border-cream-300 dark:border-gray-600 pointer-events-none";
                btn.innerHTML = "✓ Đã thu thập";
                alert(data.message);
            } else {
                btn.innerHTML = originalText;
                btn.disabled = false;
                alert(data.message || 'Có lỗi xảy ra');
            }
        } catch(e) {
            btn.innerHTML = originalText;
            btn.disabled = false;
            alert('Lỗi kết nối');
        }
    }
</script>
@endpush
</div>
@endsection
