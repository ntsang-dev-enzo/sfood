<?php

namespace App\Services;

use App\Models\AiSearchLog;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class AiSearchService
{
    public function searchGlobalByImage(UploadedFile $image): array
    {
        $path = $image->store('ai-searches', 'public');
        $fullPath = Storage::disk('public')->path($path);

        $apiUrl = config('services.ai.url');
        $resultProducts = [];
        $resultRecipes = [];
        $label = 'Không xác định';

        if ($apiUrl) {
            try {
                $resource = fopen($fullPath, 'r');
                $response = Http::timeout(30)
                    ->attach('image', $resource, basename($fullPath))
                    ->post(rtrim($apiUrl, '/').'/search_global');
                if (is_resource($resource)) {
                    fclose($resource);
                }

                if ($response->successful()) {
                    $json = $response->json();
                    if (!empty($json['success'])) {
                        $resultProducts = $json['products'] ?? [];
                        $resultRecipes = $json['recipes'] ?? [];
                        $label = $json['label'] ?? 'Thực phẩm';
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Global AI Search Error: " . $e->getMessage());
            }
        }

        // Fetch Products
        $products = collect();
        if (!empty($resultProducts)) {
            $pIds = array_column($resultProducts, 'product_id');
            $fetchedProducts = \App\Models\Product::with(['variants', 'category'])->whereIn('id', $pIds)->where('is_active', true)->get()->keyBy('id');
            foreach ($resultProducts as $r) {
                if (isset($fetchedProducts[$r['product_id']])) {
                    $product = clone $fetchedProducts[$r['product_id']];
                    $product->setAttribute('similarity', $r['similarity'] ?? 0);
                    $products->push($product);
                }
            }
        }

        // Fetch Recipes
        $recipes = collect();
        if (!empty($resultRecipes)) {
            $rIds = array_column($resultRecipes, 'recipe_id');
            $fetchedRecipes = \App\Models\Recipe::whereIn('id', $rIds)->where('is_published', true)->get()->keyBy('id');
            foreach ($resultRecipes as $r) {
                if (isset($fetchedRecipes[$r['recipe_id']])) {
                    $recipe = clone $fetchedRecipes[$r['recipe_id']];
                    $recipe->setAttribute('similarity', $r['similarity'] ?? 0);
                    $recipes->push($recipe);
                }
            }
        }

        AiSearchLog::create([
            'user_id' => Auth::id(),
            'image_path' => $path,
            'ai_results' => ['products' => $resultProducts, 'recipes' => $resultRecipes],
            'detected_label' => $label,
            'confidence' => ($resultProducts[0]['similarity'] ?? $resultRecipes[0]['similarity'] ?? 0),
        ]);

        return [
            'label' => $label,
            'products' => $products,
            'recipes' => $recipes,
            'image_url' => Storage::disk('public')->url($path),
        ];
    }
    public function searchRecipeByImage(UploadedFile $image): array
    {
        $path = $image->store('ai-searches', 'public');
        $fullPath = Storage::disk('public')->path($path);

        $apiUrl = config('services.ai.url');
        $result = [];

        if ($apiUrl) {
            try {
                $resource = fopen($fullPath, 'r');
                $response = Http::timeout(30)
                    ->attach('image', $resource, basename($fullPath))
                    ->post(rtrim($apiUrl, '/').'/search_recipe');
                if (is_resource($resource)) {
                    fclose($resource);
                }

                \Illuminate\Support\Facades\Log::info("Recipe AI Search Response", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    if (!empty($json['success']) && !empty($json['results']) && is_array($json['results'])) {
                        $result = $json['results'];
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Recipe AI Search Error: " . $e->getMessage());
            }
        }

        $recipes = collect();
        if (!empty($result)) {
            $ids = array_column($result, 'recipe_id');
            $fetched = \App\Models\Recipe::whereIn('id', $ids)->where('is_published', true)->get()->keyBy('id');
            foreach ($result as $r) {
                if (isset($fetched[$r['recipe_id']])) {
                    $recipe = clone $fetched[$r['recipe_id']];
                    $recipe->setAttribute('similarity', $r['similarity'] ?? 0);
                    $recipes->push($recipe);
                }
            }
        }

        // Fallback: if AI returned no results, return all published recipes
        if ($recipes->isEmpty()) {
            \Illuminate\Support\Facades\Log::info("Recipe AI Search fallback: returning all published recipes");
            $recipes = \App\Models\Recipe::where('is_published', true)
                ->orderByDesc('view_count')
                ->limit(6)
                ->get();
        }

        return [
            'label' => $result[0]['name'] ?? 'Món ăn',
            'confidence' => $result[0]['similarity'] ?? 0,
            'recipes' => $recipes,
            'image_url' => Storage::disk('public')->url($path),
        ];
    }
    public function searchByImage(UploadedFile $image): array
    {
        $path = $image->store('ai-searches', 'public');
        $fullPath = Storage::disk('public')->path($path);

        $result = $this->callAiService($fullPath);

        // If AI returned very few embedding-based results, use OCR text as fallback
        $raw = $result['raw'] ?? [];
        if (isset($raw['results']) && count($raw['results']) < 3) {
            $ocrText = trim($raw['ocr_text'] ?? '');
            $ocrKeywords = $raw['ocr_keywords'] ?? [];
            if ($ocrText !== '' || !empty($ocrKeywords)) {
                // perform text-based match and merge results if necessary
                $textProducts = $this->matchProducts($ocrText, $ocrKeywords);
                // attach as fallback to result so UI can see both
                $result['text_fallback_products'] = $textProducts->pluck('id')->all();
            }
        }

        AiSearchLog::create([
            'user_id' => Auth::id(),
            'image_path' => $path,
            'ai_results' => $result,
            'detected_label' => $result['label'] ?? null,
            'confidence' => $result['confidence'] ?? null,
        ]);

        // Prioritize embedding-based results when present.
        $raw = $result['raw'] ?? [];
        $embedResults = [];
        if (!empty($raw['results']) && is_array($raw['results'])) {
            // only keep entries with product_id and numeric similarity
            foreach ($raw['results'] as $r) {
                if (isset($r['product_id'])) {
                    $sim = isset($r['similarity']) ? floatval($r['similarity']) : null;
                    $embedResults[] = ['product_id' => intval($r['product_id']), 'similarity' => $sim];
                }
            }
        }

        $products = collect();

        // similarity threshold and minimum embedding results (tunable via config)
        $SIM_THRESHOLD = config('services.ai.sim_threshold', 0.03); // default 3%
        $MIN_EMBED_RESULTS = config('services.ai.min_embed_results', 3);

        $filtered = array_values(array_filter($embedResults, function ($r) use ($SIM_THRESHOLD) {
            return is_null($r['similarity']) ? true : ($r['similarity'] >= $SIM_THRESHOLD);
        }));

        $embedIds = array_values(array_unique(array_map(fn($r)=>$r['product_id'], $filtered)));

        if (!empty($embedIds)) {
            $fetched = Product::with(['variants', 'category'])
                ->whereIn('id', $embedIds)
                ->where('is_active', true)
                ->get()
                ->keyBy('id');

            // preserve order from embedIds and attach similarity
            foreach ($filtered as $r) {
                $id = $r['product_id'];
                    if (isset($fetched[$id]) && !$products->contains(fn($x) => $x->model->id == $id)) {
                    $products->push((object)['model' => $fetched[$id], 'similarity' => $r['similarity'], 'source' => 'embedding']);
                }
            }

            // if too few embedding results, append OCR/text fallback products (lower priority)
            if ($products->count() < 4) {
                $ocrFallback = $result['text_fallback_products'] ?? [];
                if (!empty($ocrFallback) && is_array($ocrFallback)) {
                    $ocrIds = array_values(array_filter(array_map('intval', $ocrFallback)));
                    $ocrFetched = Product::with(['variants','category'])->whereIn('id', $ocrIds)->where('is_active', true)->get()->keyBy('id');
                    foreach ($ocrIds as $id) {
                        if (isset($ocrFetched[$id]) && !$products->contains(fn($x) => $x->model->id == $id)) {
                            $products->push((object)['model' => $ocrFetched[$id], 'similarity' => null, 'source' => 'ocr']);
                        }
                    }
                }
            }
        } else {
            // No reliable embedding matches; try OCR-derived product ids first
            $ocrFallback = $result['text_fallback_products'] ?? [];
            if (!empty($ocrFallback) && is_array($ocrFallback)) {
                $ids = array_values(array_filter(array_map('intval', $ocrFallback)));
                $fetched = Product::with(['variants','category'])->whereIn('id', $ids)->where('is_active', true)->get()->keyBy('id');
                foreach ($ids as $id) {
                    if (isset($fetched[$id]) && !$products->contains(fn($x) => $x->model->id == $id)) {
                        $products->push((object)['model' => $fetched[$id], 'similarity' => null, 'source' => 'ocr']);
                    }
                }
            }

            // final fallback: keyword match from label/keywords/ocr
            if ($products->isEmpty()) {
                // build cleaned keywords: prefer longer tokens
                $ocrKeywords = $raw['ocr_keywords'] ?? [];
                $label = $result['label'] ?? '';
                $keywords = array_values(array_filter(array_merge([$label], $result['keywords'] ?? [], $ocrKeywords), fn($w) => is_string($w) && strlen(trim($w))>2));
                $matched = $this->matchProducts($label, $keywords);
                $products = $matched->map(fn (Product $p) => (object)['model' => $p, 'similarity' => null, 'source' => 'text']);
            }
        }

        return [
            'label' => $result['label'] ?? 'Không xác định',
            'confidence' => $result['confidence'] ?? 0,
            'products' => $products->map(function ($item) {
                /** @var Product $p */
                $p = $item->model;
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'thumbnail' => $p->thumbnail,
                    'min_price' => $p->minPrice(),
                    'category' => $p->category?->name,
                    'similarity' => $item->similarity,
                    'match_source' => $item->source ?? 'unknown',
                ];
            })->values()->all(),
            'image_url' => Storage::disk('public')->url($path),
        ];
    }

    protected function callAiService(string $imagePath): array
    {
        $apiUrl = config('services.ai.url');

        if ($apiUrl) {
            try {
                // Attach file as stream so the receiving service sees the filename
                $resource = fopen($imagePath, 'r');
                $response = Http::timeout(30)
                    ->attach('image', $resource, basename($imagePath))
                    ->post(rtrim($apiUrl, '/').'/search');
                if (is_resource($resource)) {
                    fclose($resource);
                }

                if ($response->successful()) {
                    $json = $response->json();

                    // If the AI service returns a similarity-based result list (our Flask service)
                    if (!empty($json['success']) && !empty($json['results']) && is_array($json['results'])) {
                        $results = $json['results'];
                        $top = $results[0] ?? null;

                        return [
                            'label' => $top['name'] ?? null,
                            'confidence' => isset($top['similarity']) ? floatval($top['similarity']) : null,
                            'keywords' => $top['name'] ? preg_split('/\s+/', $top['name']) : [],
                            'product_ids' => array_values(array_map(fn ($r) => $r['product_id'] ?? null, $results)),
                            'raw' => $json,
                        ];
                    }

                    // Otherwise, return whatever the remote service gave (for backwards compatibility)
                    return $json ?? [];
                }
            } catch (\Throwable) {
                // fallback below
            }
        }

        return $this->fallbackSearch($imagePath);
    }

    protected function fallbackSearch(string $imagePath): array
    {
        $filename = strtolower(basename($imagePath));
        $keywords = ['rau', 'củ', 'thịt', 'cá', 'trái', 'gia vị', 'sữa', 'trứng'];

        foreach ($keywords as $keyword) {
            if (str_contains($filename, str_replace(' ', '', $keyword))) {
                return [
                    'label' => $keyword,
                    'confidence' => 0.75,
                    'keywords' => [$keyword],
                ];
            }
        }

        return [
            'label' => 'Không xác định',
            'confidence' => 0,
            'keywords' => [],
        ];
    }

    protected function matchProducts(string $label, array $keywords): Collection
    {
        $terms = array_filter(array_merge([$label], $keywords));

        return Product::with(['variants', 'category'])
            ->where('is_active', true)
            ->where(function ($query) use ($terms) {
                foreach ($terms as $term) {
                    $query->orWhere('name', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                }
            })
            ->limit(12)
            ->get();
    }
}
