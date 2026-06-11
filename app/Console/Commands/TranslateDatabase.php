<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Category;
use App\Models\Product;
use App\Models\Recipe;
use Illuminate\Support\Facades\Http;

class TranslateDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sfood:translate-db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Translate categories, products, and recipes to English';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting database translation to English...');

        // 1. Translate Categories
        $this->info('Translating Categories...');
        foreach (Category::all() as $category) {
            if (empty($category->name_en) && !empty($category->name)) {
                $category->name_en = $this->translate($category->name);
                $this->line("Translated: {$category->name} -> {$category->name_en}");
            }
            if (empty($category->description_en) && !empty($category->description)) {
                $category->description_en = $this->translate($category->description);
            }
            $category->save();
        }

        // 2. Translate Products
        $this->info('Translating Products...');
        foreach (Product::all() as $product) {
            if (empty($product->name_en) && !empty($product->name)) {
                $product->name_en = $this->translate($product->name);
                $this->line("Translated: {$product->name} -> {$product->name_en}");
            }
            if (empty($product->short_description_en) && !empty($product->short_description)) {
                $product->short_description_en = $this->translate($product->short_description);
            }
            if (empty($product->description_en) && !empty($product->description)) {
                $product->description_en = $this->translateHTML($product->description);
            }
            $product->save();
        }

        // 3. Translate Recipes
        $this->info('Translating Recipes...');
        foreach (Recipe::all() as $recipe) {
            if (empty($recipe->title_en) && !empty($recipe->title)) {
                $recipe->title_en = $this->translate($recipe->title);
                $this->line("Translated: {$recipe->title} -> {$recipe->title_en}");
            }
            if (empty($recipe->description_en) && !empty($recipe->description)) {
                $recipe->description_en = $this->translateHTML($recipe->description);
            }
            $recipe->save();
        }

        $this->info('Database translation completed!');
    }

    private function translate($text)
    {
        if (empty(trim($text))) return $text;

        try {
            $response = Http::get('https://translate.googleapis.com/translate_a/single', [
                'client' => 'gtx',
                'sl' => 'vi',
                'tl' => 'en',
                'dt' => 't',
                'q' => $text,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $translated = '';
                foreach ($data[0] as $part) {
                    $translated .= $part[0];
                }
                return $translated;
            }
        } catch (\Exception $e) {
            $this->error("Translation failed for: " . substr($text, 0, 50));
        }

        return $text;
    }

    private function translateHTML($html)
    {
        // For simple HTML, Google Translate API can break tags.
        // As a quick workaround for this demo, we just strip tags, translate, and return text.
        // A robust solution would use Google Cloud Translation API with HTML format.
        $text = strip_tags($html);
        return $this->translate($text);
    }
}
