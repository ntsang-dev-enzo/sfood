@php use App\Support\MediaHelper; @endphp
<a href="{{ route('recipes.show', $recipe) }}" class="card group">
    <div class="aspect-video bg-cream-200 relative overflow-hidden">
        <img src="{{ MediaHelper::recipeImage($recipe->thumbnail, $recipe->id) }}" alt="{{ $recipe->title }}"
            class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
        <div class="absolute inset-0 bg-earth-900/30 group-hover:bg-earth-900/20 transition flex items-center justify-center">
            <span class="w-14 h-14 rounded-full bg-cream-50/90 flex items-center justify-center text-2xl shadow-lg">▶</span>
        </div>
        <span class="absolute top-3 right-3 badge bg-earth-700 text-cream-50">⏱ {{ $recipe->cook_time }} phút</span>
    </div>
    <div class="p-4">
        <h3 class="font-semibold text-earth-800 group-hover:text-leaf-700 transition line-clamp-2">{{ $recipe->title }}</h3>
        <p class="text-sm text-earth-500 mt-2 line-clamp-2">{{ Str::limit($recipe->description, 80) }}</p>
    </div>
</a>
