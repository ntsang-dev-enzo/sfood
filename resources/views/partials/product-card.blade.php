@php use App\Support\MediaHelper; @endphp
<a href="{{ route('products.show', $product) }}" class="card group flex flex-col h-full">
    <div class="aspect-square bg-cream-200 dark:bg-gray-800 relative overflow-hidden shrink-0">
        <img src="{{ MediaHelper::productImage($product->getFilteredImage(request('min_price'), request('max_price')), $product->id) }}" alt="{{ $product->name }}"
            class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
        @if ($product->category)
            <span class="absolute top-3 left-3 badge bg-leaf-600 text-white shadow-sm">{{ $product->category->name }}</span>
        @endif
        @php
            $isNew = $product->is_new;
            $isHot = $product->is_hot;
            $hasFlashSale = $product->variants->contains(function ($v) { return $v->isFlashActive(); });
        @endphp
        <div class="absolute top-3 right-3 flex flex-col gap-2">
            @if($isHot)
                <span class="badge bg-red-600 text-white shadow-sm text-xs font-bold px-2 py-1 uppercase rounded-md animate-pulse">🔥 Hot</span>
            @endif
            @if($isNew)
                <span class="badge bg-blue-500 text-white shadow-sm text-xs font-bold px-2 py-1 uppercase rounded-md">✨ New</span>
            @endif
            @if($hasFlashSale)
                <span class="badge bg-orange-500 text-white shadow-sm text-xs font-bold px-2 py-1 uppercase rounded-md">⚡ Flash</span>
            @endif
        </div>
    </div>
    <div class="p-4 flex flex-col flex-1">
        <h3 class="font-semibold text-earth-800 dark:text-gray-300 line-clamp-2 min-h-[3rem] group-hover:text-leaf-700 dark:group-hover:text-leaf-400 transition leading-snug mb-2">{{ $product->name }}</h3>
        
        @php
            // Calculate rating
            // If eager loaded, use reviews_avg_rating, otherwise fallback to method
            $avgRating = $product->reviews_avg_rating ?? $product->averageRating();
            $reviewCount = $product->reviews_count ?? $product->reviews()->count();
        @endphp
        <div class="h-5 mb-2">
            @if($reviewCount > 0)
                <div class="flex items-center gap-1">
                    <span class="text-yellow-400 text-sm leading-none">★</span>
                    <span class="text-xs font-bold text-earth-700 dark:text-gray-300 leading-none">{{ number_format($avgRating, 1) }}</span>
                    <span class="text-xs text-earth-500 dark:text-gray-400 leading-none">({{ $reviewCount }})</span>
                </div>
            @endif
        </div>
        <div class="mt-auto flex items-center justify-between border-t border-cream-200 dark:border-gray-700 pt-3">
            <p class="text-leaf-700 dark:text-leaf-400 font-bold text-lg">{{ number_format($product->minPrice(request('min_price'), request('max_price'))) }}đ</p>
            <span class="text-[11px] font-semibold text-earth-600 dark:text-gray-300 bg-earth-100 dark:bg-gray-700 px-2.5 py-1.5 rounded-md hover:bg-earth-200 dark:hover:bg-gray-600 transition whitespace-nowrap">{!! __('app.details') !!}</span>
        </div>
    </div>
</a>
