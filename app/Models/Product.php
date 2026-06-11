<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Jobs\EmbedImage;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'thumbnail',
        'is_active',
        'view_count',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function defaultVariant(): ?ProductVariant
    {
        return $this->variants()->where('is_active', true)->orderBy('price')->first();
    }

    public function minPrice(): float
    {
        return (float) ($this->variants()->where('is_active', true)->min('sale_price')
            ?? $this->variants()->where('is_active', true)->min('price')
            ?? 0);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')
            ->withTimestamps();
    }

    public function isFavoritedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->favoritedBy()->where('user_id', $user->id)->exists();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        // when thumbnail changes, re-embed
        static::updated(function (Product $p) {
            try {
                if ($p->isDirty('thumbnail')) {
                    // remove existing product-level embedding (product_image_id = 0) for the old thumbnail
                    DB::table('product_embeddings')
                        ->where('product_id', $p->id)
                        ->where('product_image_id', 0)
                        ->where('image_path', $p->getOriginal('thumbnail'))
                        ->delete();

                    if (!empty($p->thumbnail)) {
                        EmbedImage::dispatch($p->id, 0, $p->thumbnail);
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }
        });

        static::created(function (Product $p) {
            try {
                if (!empty($p->thumbnail)) {
                    EmbedImage::dispatch($p->id, 0, $p->thumbnail);
                }
            } catch (\Throwable $e) {}
        });
    }
}
