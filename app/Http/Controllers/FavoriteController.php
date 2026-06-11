<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function index()
    {
        $products = Auth::user()
            ->favoriteProducts()
            ->with(['category', 'variants'])
            ->where('is_active', true)
            ->orderByPivot('created_at', 'desc')
            ->get();

        return view('favorites.index', compact('products'));
    }
}
