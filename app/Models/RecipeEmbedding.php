<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeEmbedding extends Model
{
    protected $fillable = [
        'recipe_id',
        'image_path',
        'embedding',
        'color_hist',
    ];

    protected function casts(): array
    {
        return [
            'embedding' => 'array',
            'color_hist' => 'array',
        ];
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }
}
