<?php

namespace App\Jobs;

use App\Models\Recipe;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmbedRecipeImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Recipe $recipe
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $thumbnailPath = $this->recipe->thumbnail;
        if (!$thumbnailPath) {
            return;
        }

        $absolutePath = \Illuminate\Support\Facades\Storage::disk('public')->path($thumbnailPath);
        if (!file_exists($absolutePath)) {
            Log::warning("Recipe image not found: {$absolutePath}");
            return;
        }

        try {
            $response = Http::attach(
                'image', 
                file_get_contents($absolutePath), 
                basename($absolutePath)
            )->post('http://127.0.0.1:5000/embed_recipe', [
                'recipe_id' => $this->recipe->id,
                'image_path' => $thumbnailPath,
            ]);

            if (!$response->successful() || !$response->json('success')) {
                Log::error("Failed to embed recipe {$this->recipe->id}: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Exception embedding recipe {$this->recipe->id}: " . $e->getMessage());
        }
    }
}
