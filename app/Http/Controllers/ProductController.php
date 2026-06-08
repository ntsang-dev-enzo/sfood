<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\AiSearchService;
use App\Services\CartService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $categorySlug = $request->get('category', '');

        $products = Product::with(['category', 'variants'])
            ->where('is_active', true)
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->when($categorySlug, fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $categorySlug)))
            ->orderByDesc('view_count')
            ->paginate(12)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->get(),
            'search' => $search,
            'categorySlug' => $categorySlug,
            'aiResults' => session('ai_results'),
        ]);
    }

    public function show(Product $product)
    {
        $product->load(['category', 'variants', 'images']);
        $product->increment('view_count');

        $suggestions = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->orderByDesc('view_count')
            ->limit(8)
            ->get();

        return view('products.show', [
            'product' => $product,
            'defaultVariantId' => $product->defaultVariant()?->id,
            'suggestions' => $suggestions,
            'isFavorited' => auth()->check() ? \Illuminate\Support\Facades\DB::table('favorites')->where('user_id', auth()->id())->where('product_id', $product->id)->exists() : false,
        ]);
    }

    public function suggestions(Product $product,
        \Illuminate\Http\Request $request)
    {
        $offset = (int) $request->get('offset', 0);
        $limit = (int) $request->get('limit', 8);

        $rows = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->orderByDesc('view_count')
            ->skip($offset)
            ->take($limit)
            ->get(['id','name','slug','thumbnail']);

        return response()->json(['data' => $rows]);
    }

    public function aiSearch(Request $request, AiSearchService $aiSearch)
    {
        $request->validate([
            'ai_image' => 'required|image|max:5120',
        ]);

        $results = $aiSearch->searchByImage($request->file('ai_image'));

        return redirect()
            ->route('products.index', $request->only(['search', 'category']))
            ->with('ai_results', $results);
    }

    public function addToCart(Request $request, CartService $cart)
    {
        $data = $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $cart->add((int) $data['variant_id'], (int) $data['quantity']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã thêm vào giỏ hàng!');
    }

    public function toggleFavorite(Request $request, Product $product)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $exists = \Illuminate\Support\Facades\DB::table('favorites')
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->exists();

        if ($exists) {
            \Illuminate\Support\Facades\DB::table('favorites')
                ->where('user_id', $user->id)
                ->where('product_id', $product->id)
                ->delete();

            return response()->json(['favorited' => false]);
        }

        \Illuminate\Support\Facades\DB::table('favorites')->insert([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['favorited' => true]);
    }
}
