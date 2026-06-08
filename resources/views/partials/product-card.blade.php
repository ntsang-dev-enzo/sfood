@php use App\Support\MediaHelper; @endphp
<a href="{{ route('products.show', $product) }}" class="card group">
    <div class="aspect-square bg-cream-200 relative overflow-hidden">
        <img src="{{ MediaHelper::productImage($product->thumbnail, $product->id) }}" alt="{{ $product->name }}"
            class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
        @if ($product->category)
            <span class="absolute top-3 left-3 badge bg-leaf-600 text-white">{{ $product->category->name }}</span>
        @endif
    </div>
    <div class="p-4">
        <h3 class="font-semibold text-earth-800 line-clamp-2 group-hover:text-leaf-700 transition">{{ $product->name }}</h3>
        <div class="flex items-center justify-between mt-2">
            <p class="text-leaf-700 font-bold text-lg">{{ number_format($product->minPrice()) }}đ</p>
            <span class="text-xs text-earth-500">Xem chi tiết →</span>
        </div>
    </div>
</a>
