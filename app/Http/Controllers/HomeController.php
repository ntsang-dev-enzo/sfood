<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\Banner;
use App\Models\OrderItem;

class HomeController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $suggestedProductsHabits = collect();
        if ($user) {
            $categoryIds = OrderItem::whereHas('order', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
              ->join('products', 'product_variants.product_id', '=', 'products.id')
              ->distinct()
              ->pluck('products.category_id');
              
            if ($categoryIds->isNotEmpty()) {
                $suggestedProductsHabits = Product::whereIn('category_id', $categoryIds)
                    ->where('is_active', true)
                    ->inRandomOrder()
                    ->limit(4)
                    ->get();
            }
        }

        if ($suggestedProductsHabits->isEmpty()) {
            $suggestedProductsHabits = Product::where('is_active', true)
                ->orderByDesc('view_count')
                ->limit(4)
                ->get();
        }

        return view('pages.home', [
            'banners' => Banner::where('is_active', true)->orderBy('sort_order')->get(),
            'suggestedProductsHabits' => $suggestedProductsHabits,
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->limit(6)->get(),
            'products' => Product::with('variants')->where('is_active', true)->orderByDesc('view_count')->limit(8)->get(),
            'bestSellers' => Product::with('variants')
                ->where('products.is_active', true)
                ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
                ->join('order_items', 'product_variants.id', '=', 'order_items.product_variant_id')
                ->select('products.*', \Illuminate\Support\Facades\DB::raw('SUM(order_items.quantity) as total_sold'))
                ->groupBy('products.id')
                ->orderByDesc('total_sold')
                ->limit(8)
                ->get(),
            'flashSales' => Product::whereHas('variants', function ($q) {
                $q->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'price');
            })->with(['variants' => function ($q) { $q->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'price'); }])->limit(6)->get(),
            'recipes' => Recipe::where('is_published', true)->orderByDesc('view_count')->limit(4)->get(),
            'suggestedRecipes' => Recipe::where('is_published', true)->inRandomOrder()->limit(6)->get(),
        ]);
    }
}
