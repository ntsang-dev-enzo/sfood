<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\ProductImage;

class EmbedProductImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ProductImage $image;

    public function __construct(ProductImage $image)
    {
        $this->image = $image;
    }

    public function handle(): void
    {
        $aiUrl = env('AI_SERVICE_URL', 'http://127.0.0.1:5000');

        $path = storage_path('app/public/' . $this->image->image_path);
        if (!file_exists($path)) {
            return;
        }

        try {
            $response = Http::timeout(60)->attach('image', fopen($path, 'r'))
                ->post(rtrim($aiUrl, '/') . '/embed_single', [
                    'product_id' => $this->image->product_id,
                    'product_image_id' => $this->image->id,
                    'image_path' => $this->image->image_path,
                ]);

            // optional: check response status
            if ($response->failed()) {
                // log if needed
            }
        } catch (\Throwable $e) {
            // ignore or log
        }
    }
}
