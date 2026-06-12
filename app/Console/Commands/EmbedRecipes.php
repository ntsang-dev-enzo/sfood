<?php

namespace App\Console\Commands;

use App\Models\Recipe;
use App\Jobs\EmbedRecipeImage;
use Illuminate\Console\Command;

class EmbedRecipes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sfood:embed-recipes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate embeddings for all recipes with thumbnails';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $recipes = Recipe::whereNotNull('thumbnail')->get();
        $this->info("Found {$recipes->count()} recipes with thumbnails.");

        $bar = $this->output->createProgressBar($recipes->count());
        $bar->start();

        foreach ($recipes as $recipe) {
            EmbedRecipeImage::dispatch($recipe);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Done dispatching jobs!');
    }
}
