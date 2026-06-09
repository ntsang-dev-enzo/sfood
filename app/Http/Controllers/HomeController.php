<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Recipe;

class HomeController extends Controller
{
    public function index()
    {
        return view('pages.home', [
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->limit(6)->get(),
            'products' => Product::with('variants')->where('is_active', true)->orderByDesc('view_count')->limit(8)->get(),
            'flashSales' => Product::whereHas('variants', function ($q) {
                $q->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'price');
            })->with(['variants' => function ($q) { $q->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'price'); }])->limit(6)->get(),
            'recipes' => Recipe::where('is_published', true)->orderByDesc('view_count')->limit(4)->get(),
            'suggestedRecipes' => Recipe::where('is_published', true)->inRandomOrder()->limit(6)->get(),
        ]);
    }
}
