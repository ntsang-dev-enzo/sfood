<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
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

        // Lấy danh sách sản phẩm đã xem gần nhất từ session
        $recentIds = session()->get('recently_viewed', []);
        $recentlyViewed = collect();
        if (!empty($recentIds)) {
            $recentlyViewed = Product::with(['variants', 'images', 'category'])
                ->whereIn('id', $recentIds)
                ->where('is_active', true)
                ->get()
                ->sortBy(fn($p) => array_search($p->id, $recentIds));
        }

        // Nếu session chưa có, gợi ý các sản phẩm xem nhiều nhất
        if ($recentlyViewed->isEmpty()) {
            $recentlyViewed = Product::with(['variants', 'images', 'category'])
                ->where('is_active', true)
                ->orderByDesc('view_count')
                ->take(4)
                ->get();
        }

        return view('products.index', [
            'products' => $products,
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->get(),
            'recentlyViewed' => $recentlyViewed,
            'search' => $search,
            'categorySlug' => $categorySlug,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'sort' => $sort,
        ]);
    }

    public function show(Product $product)
    {
        $product->load(['category', 'variants', 'images']);
        $product->increment('view_count');

        // Lưu sản phẩm đã xem vào session
        $recentIds = session()->get('recently_viewed', []);
        $recentIds = array_diff($recentIds, [$product->id]);
        array_unshift($recentIds, $product->id);
        $recentIds = array_slice($recentIds, 0, 8);
        session()->put('recently_viewed', $recentIds);

        $suggestions = Product::with('reviews')
            ->where('category_id', $product->category_id)
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

        $rows = Product::with(['category', 'variants', 'reviews'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->orderByDesc('view_count')
            ->skip($offset)
            ->take($limit)
            ->get();

        $html = '';
        foreach ($rows as $s) {
            $html .= '<div class="col">';
            $html .= view('partials.product-card', ['product' => $s])->render();
            $html .= '</div>';
        }

        return response()->json([
            'html' => $html,
            'count' => $rows->count()
        ]);
    }

    public function addToCart(\Illuminate\Http\Request $request, \App\Services\CartService $cart)
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
            return redirect()->route('checkout.index');
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
