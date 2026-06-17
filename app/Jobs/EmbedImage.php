<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class EmbedImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $productId;
    public ?int $productImageId;
    public string $imagePath;

    public function __construct(int $productId, ?int $productImageId, string $imagePath)
    {
        $this->productId = $productId;
        $this->productImageId = $productImageId;
        $this->imagePath = $imagePath;
    }

    public function handle(): void
    {
        $aiUrl = env('AI_SERVICE_URL', 'http://127.0.0.1:5000');

        $path = \Illuminate\Support\Facades\Storage::disk('public')->path($this->imagePath);
        if (!file_exists($path)) {
            return;
        }

        try {
            $response = Http::timeout(60)->attach('image', fopen($path, 'r'))
                ->post(rtrim($aiUrl, '/') . '/embed_single', [
                    'product_id' => $this->productId,
                    'product_image_id' => $this->productImageId ?? 0,
                    'image_path' => $this->imagePath,
                ]);

            // optional: log on failure
            if ($response->failed()) {
                // Log::warning('EmbedImage job failed', ['product' => $this->productId]);
            }
        } catch (\Throwable $e) {
            // ignore or log
        }
    }
}
