<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public function suggest(Request $request)
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            return response()->json(['products' => [], 'recipes' => []]);
        }

        $products = Product::with(['category', 'variants' => function ($q) {
                $q->where('is_active', true);
            }])
            ->where('is_active', true)
            ->where('name', 'like', "%{$q}%")
            ->limit(6)
            ->get()
            ->map(function ($p) {
                $activeVariants = $p->variants ? $p->variants->where('is_active', true)->values() : collect();
                $firstVariant = $activeVariants->first();
                $hasMultipleVariants = $activeVariants->count() > 1;

                $effectivePrices = $activeVariants->map(fn($v) => $v->effectivePrice())->filter();
                $minPrice = $effectivePrices->isNotEmpty() ? $effectivePrices->min() : (float)($p->min_price ?? 0);
                $maxPrice = $effectivePrices->isNotEmpty() ? $effectivePrices->max() : $minPrice;

                // Price display & range
                $priceFormatted = number_format($minPrice) . '₫';
                if ($hasMultipleVariants && $maxPrice > $minPrice) {
                    $priceRange = number_format($minPrice) . '₫ - ' . number_format($maxPrice) . '₫';
                } else {
                    $priceRange = $priceFormatted;
                }

                // Check discount (compare first variant effective vs regular price)
                $originalPrice = null;
                $discountPercent = null;
                if ($firstVariant && (float)$firstVariant->price > $firstVariant->effectivePrice()) {
                    $originalPrice = number_format((float)$firstVariant->price) . '₫';
                    $discountPercent = round((((float)$firstVariant->price - $firstVariant->effectivePrice()) / (float)$firstVariant->price) * 100) . '%';
                }

                $totalStock = (int)$activeVariants->sum('stock');
                $inStock = $totalStock > 0 && $activeVariants->isNotEmpty();

                $variantsData = $hasMultipleVariants ? $activeVariants->map(function ($v) {
                    return [
                        'id' => $v->id,
                        'name' => $v->name,
                        'price' => $v->effectivePrice(),
                        'price_formatted' => number_format($v->effectivePrice()) . '₫',
                        'stock' => $v->stock,
                        'in_stock' => $v->inStock(),
                    ];
                })->values() : [];

                return [
                    'type' => 'product',
                    'id' => $p->id,
                    'name' => $p->name,
                    'subtitle' => $p->category?->name,
                    'category' => $p->category?->name ?? 'Nông sản sạch',
                    'url' => route('products.show', $p),
                    'price' => $priceFormatted,
                    'price_range' => $priceRange,
                    'original_price' => $originalPrice,
                    'discount_percent' => $discountPercent,
                    'image' => \App\Support\MediaHelper::productImage($p->thumbnail, $p->id),
                    'sku' => $firstVariant?->sku ?? null,
                    'short_description' => $p->short_description ?: Str::limit(strip_tags($p->description ?? ''), 120),
                    'in_stock' => $inStock,
                    'stock_count' => $totalStock,
                    'stock_text' => $inStock ? 'Còn hàng' : 'Hết hàng',
                    'has_multiple_variants' => $hasMultipleVariants,
                    'first_variant_id' => $firstVariant?->id,
                    'add_url' => route('products.add-to-cart', $p),
                    'variants_data' => $variantsData,
                ];
            });

        $recipes = Recipe::where('is_published', true)
            ->where('title', 'like', "%{$q}%")
            ->limit(4)
            ->get()
            ->map(fn ($r) => [
                'type' => 'recipe',
                'id' => $r->id,
                'name' => $r->title,
                'subtitle' => $r->cook_time ? ($r->cook_time . ' phút') : null,
                'category' => 'Công thức nấu ăn',
                'url' => route('recipes.show', $r),
                'price' => null,
                'short_description' => Str::limit(strip_tags($r->description ?? ''), 120),
                'image' => \App\Support\MediaHelper::recipeImage($r->thumbnail, $r->id),
            ]);

        return response()->json([
            'products' => $products,
            'recipes' => $recipes,
        ]);
    }
}
