<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Recipe;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function suggest(Request $request)
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            return response()->json(['products' => [], 'recipes' => []]);
        }

        $products = Product::with('category')
            ->where('is_active', true)
            ->where('name', 'like', "%{$q}%")
            ->limit(5)
            ->get()
            ->map(fn ($p) => [
                'type' => 'product',
                'name' => $p->name,
                'subtitle' => $p->category?->name,
                'url' => route('products.show', $p),
                'price' => number_format($p->minPrice()) . '₫',
            ]);

        $recipes = Recipe::where('is_published', true)
            ->where('title', 'like', "%{$q}%")
            ->limit(5)
            ->get()
            ->map(fn ($r) => [
                'type' => 'recipe',
                'name' => $r->title,
                'subtitle' => $r->cook_time . ' phút',
                'url' => route('recipes.show', $r),
                'price' => null,
            ]);

        return response()->json([
            'products' => $products,
            'recipes' => $recipes,
        ]);
    }
}
