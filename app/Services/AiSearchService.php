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

        $randomProduct = Product::where('is_active', true)->inRandomOrder()->first();

        return [
            'label' => $randomProduct?->name ?? 'thực phẩm',
            'confidence' => 0.5,
            'keywords' => $randomProduct ? explode(' ', $randomProduct->name) : ['thực phẩm'],
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
