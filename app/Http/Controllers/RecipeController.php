<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Services\CartService;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');

        $recipes = Recipe::where('is_published', true)
            ->when($search, fn ($q) => $q->where('title', 'like', "%{$search}%"))
            ->orderByDesc('view_count')
            ->paginate(9)
            ->withQueryString();

        return view('recipes.index', compact('recipes', 'search'));
    }

    public function show(Recipe $recipe)
    {
        $recipe->load(['ingredients.productVariant.product']);
        $recipe->increment('view_count');

        return view('recipes.show', compact('recipe'));
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

        return redirect()->route('cart')->with('success', 'Đã thêm tất cả nguyên liệu vào giỏ hàng!');
    }
}
