<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Recipe;
use App\Support\MediaHelper;
use App\Support\VietnameseSearch;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function aiSearchGlobal(Request $request, \App\Services\AiSearchService $aiSearch)
    {
        $request->validate([
            'ai_image' => 'required|image|max:5120',
        ]);

        $results = $aiSearch->searchGlobalByImage($request->file('ai_image'));

        $topProduct = $results['products'][0]['similarity'] ?? 0;
        $topRecipe = $results['recipes'][0]['similarity'] ?? 0;

        // Compare max scores to determine if it's more likely a recipe
        if ($topRecipe > $topProduct && $topRecipe > 0) {
            return redirect()->route('recipes.index')->with('ai_results', $results['recipes']);
        }

        // Default to products
        return redirect()->route('products.index')->with('ai_results', $results['products']);
    }

    public function suggest(Request $request)
    {
        $q = VietnameseSearch::normalize($request->get('q', ''));

        if (VietnameseSearch::isTooShort($q)) {
            return response()->json(['products' => [], 'recipes' => []]);
        }

        $products = VietnameseSearch::applyLike(
            Product::with('category')->where('is_active', true),
            'name',
            $q
        )
            ->limit(5)
            ->get()
            ->map(fn ($p) => [
                'type' => 'product',
                'name' => $p->name,
                'subtitle' => $p->category?->name,
                'url' => route('products.show', $p),
                'price' => number_format($p->minPrice()).'đ',
                'image' => MediaHelper::productImage($p->thumbnail, $p->id),
            ]);

        $recipes = VietnameseSearch::applyLike(
            Recipe::where('is_published', true),
            'title',
            $q
        )
            ->limit(5)
            ->get()
            ->map(fn ($r) => [
                'type' => 'recipe',
                'name' => $r->title,
                'subtitle' => $r->cook_time.' phút',
                'url' => route('recipes.show', $r),
                'price' => null,
                'image' => MediaHelper::recipeImage($r->thumbnail, $r->id),
            ]);

        return response()->json([
            'products' => $products,
            'recipes' => $recipes,
        ]);
    }
}
