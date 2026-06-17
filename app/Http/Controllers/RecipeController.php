<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Services\CartService;
use App\Support\VietnameseSearch;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    public function index(Request $request)
    {
        $search = VietnameseSearch::normalize($request->get('search', ''));
        $categorySlug = $request->get('category');
        $minTime = $request->get('min_time');
        $maxTime = $request->get('max_time');
        $sort = $request->get('sort', 'newest'); // newest, shortest
        
        $aiResults = session('ai_results');

        $query = Recipe::where('is_published', true)
            ->when($search, fn ($q) => VietnameseSearch::applyLike($q, 'title', $search))
            ->when($categorySlug, function ($q) use ($categorySlug) {
                $q->whereHas('category', fn ($c) => $c->where('slug', $categorySlug));
            })
            ->when($minTime !== null, fn ($q) => $q->where('cook_time', '>=', $minTime))
            ->when($maxTime !== null, fn ($q) => $q->where('cook_time', '<=', $maxTime));

        if ($sort === 'shortest') {
            $query->orderBy('cook_time', 'asc');
        } elseif ($sort === 'popular') {
            $query->orderBy('view_count', 'desc');
        } else {
            // newest
            $query->orderBy('created_at', 'desc');
        }

        $recipes = $query->paginate(9)->withQueryString();
        
        $categories = \App\Models\RecipeCategory::all();

        $coupons = \App\Models\Coupon::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        $user = $request->user();
        $collectedIds = $user ? $user->collectedCoupons()->pluck('coupons.id')->toArray() : [];

        return view('recipes.index', compact('recipes', 'search', 'aiResults', 'categories', 'categorySlug', 'minTime', 'maxTime', 'sort', 'coupons', 'collectedIds'));
    }

    public function aiSearch(\Illuminate\Http\Request $request, \App\Services\AiSearchService $aiSearch)
    {
        $request->validate([
            'ai_image' => 'required|image|max:5120',
        ]);

        $results = $aiSearch->searchRecipeByImage($request->file('ai_image'));

        return redirect()
            ->route('recipes.index', $request->only(['search']))
            ->with('ai_results', $results);
    }

    public function show(Recipe $recipe)
    {
        $recipe->load(['ingredients.productVariant.product']);
        $recipe->increment('view_count');

        return view('recipes.show', [
            'recipe' => $recipe,
            'isFavorited' => $recipe->isFavoritedBy(auth()->user()),
        ]);
    }

    public function toggleFavorite(Request $request, Recipe $recipe)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $favorited = $user->favoriteRecipes()->toggle($recipe->id);

        return response()->json([
            'favorited' => ! empty($favorited['attached']),
        ]);
    }

    public function quickBuy(Recipe $recipe, CartService $cart)
    {
        $items = [];

        foreach ($recipe->ingredients as $ingredient) {
            if ($ingredient->productVariant->inStock()) {
                $items[$ingredient->product_variant_id] = (int) ceil($ingredient->quantity);
            }
        }

        if (empty($items)) {
            return back()->with('error', 'Không có nguyên liệu khả dụng.');
        }

        $cart->addMany($items);

        return back()->with('success', 'Đã thêm tất cả nguyên liệu vào giỏ hàng!');
    }
}
