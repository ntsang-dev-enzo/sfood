<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductImage;
use App\Jobs\EmbedImage;
use Illuminate\Console\Command;

class EmbedProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sfood:embed-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate embeddings for all products and product images';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $products = Product::whereNotNull('thumbnail')->where('thumbnail', '!=', '')->get();
        $this->info("Found {$products->count()} products with thumbnails.");

        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        foreach ($products as $product) {
            EmbedImage::dispatch($product->id, 0, $product->thumbnail);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $images = ProductImage::whereNotNull('image_path')->where('image_path', '!=', '')->get();
        $this->info("Found {$images->count()} additional product images.");

        $bar2 = $this->output->createProgressBar($images->count());
        $bar2->start();

        foreach ($images as $img) {
            EmbedImage::dispatch($img->product_id, $img->id, $img->image_path);
            $bar2->advance();
        }

        $bar2->finish();
        $this->newLine();
        $this->info('Done dispatching jobs! Ensure your queue worker is running to process them.');
    }
}
