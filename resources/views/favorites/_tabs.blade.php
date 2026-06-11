<div class="max-w-7xl mx-auto px-4 -mb-4">
    <div class="flex gap-2">
        <a href="{{ route('favorites.index') }}"
            class="px-5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('favorites.index') ? 'bg-leaf-600 text-white' : 'bg-cream-50 text-earth-700 hover:bg-cream-200 border border-cream-300' }}">
            🛒 Sản phẩm
        </a>
        <a href="{{ route('favorites.videos') }}"
            class="px-5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('favorites.videos') ? 'bg-leaf-600 text-white' : 'bg-cream-50 text-earth-700 hover:bg-cream-200 border border-cream-300' }}">
            ▶ Video
        </a>
    </div>
</div>
