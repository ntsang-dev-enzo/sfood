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
        $minPrice = $request->get('min_price');
        $maxPrice = $request->get('max_price');
        $sort = $request->get('sort', 'popular'); // default to popular

        $productsQuery = Product::with(['category', 'variants'])
            ->where('is_active', true)
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->when($categorySlug, fn($q) => $q->whereHas('category', fn($c) => $c->where('slug', $categorySlug)))
            ->when($minPrice || $maxPrice, function ($q) use ($minPrice, $maxPrice) {
                $q->whereHas('variants', function ($v) use ($minPrice, $maxPrice) {
                    $v->where('is_active', true)
                        ->where(function ($vq) use ($minPrice, $maxPrice) {
                            if ($minPrice) {
                                $vq->whereRaw('COALESCE(sale_price, price) >= ?', [$minPrice]);
                            }
                            if ($maxPrice) {
                                $vq->whereRaw('COALESCE(sale_price, price) <= ?', [$maxPrice]);
                            }
                        });
                });
            });

        // Áp dụng sắp xếp
        switch ($sort) {
            case 'price_asc':
                $productsQuery->select('products.*')
                    ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
                    ->groupBy('products.id')
                    ->orderByRaw('MIN(COALESCE(product_variants.sale_price, product_variants.price)) ASC');
                break;
            case 'price_desc':
                $productsQuery->select('products.*')
                    ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
                    ->groupBy('products.id')
                    ->orderByRaw('MAX(COALESCE(product_variants.sale_price, product_variants.price)) DESC');
                break;
            case 'name_asc':
                $productsQuery->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $productsQuery->orderBy('name', 'desc');
                break;
            case 'newest':
                $productsQuery->orderBy('created_at', 'desc');
                break;
            case 'popular':
            default:
                $productsQuery->orderByDesc('view_count');
                break;
        }

        $products = $productsQuery->paginate(12)->withQueryString();

        return view('products.index', [
            'products' => $products,
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->get(),
            'search' => $search,
            'categorySlug' => $categorySlug,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'sort' => $sort,
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
            'isFavorited' => $product->isFavoritedBy(auth()->user()),
        ]);
    }

    public function suggestions(
        Product $product,
        \Illuminate\Http\Request $request
    ) {
        $offset = (int) $request->get('offset', 0);
        $limit = (int) $request->get('limit', 8);

        $rows = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->orderByDesc('view_count')
            ->skip($offset)
            ->take($limit)
            ->get(['id', 'name', 'slug', 'thumbnail']);

        return response()->json(['data' => $rows]);
    }

    public function aiSearch(Request $request, AiSearchService $aiSearch)
    {
        $request->validate([
            'ai_image' => 'required|image|max:5120',
        ]);

        $results = $aiSearch->searchByImage($request->file('ai_image'));

        return redirect()
            ->route('products.index', $request->only(['search', 'category', 'min_price', 'max_price']))
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

        if ($request->input('action') === 'buy_now') {
            return redirect()->route('checkout');
        }

        return back()->with('success', 'Đã thêm vào giỏ hàng!');
    }

    public function toggleFavorite(Request $request, Product $product)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $favorited = $user->favoriteProducts()->toggle($product->id);

        return response()->json([
            'favorited' => !empty($favorited['attached']),
        ]);
    }
}
