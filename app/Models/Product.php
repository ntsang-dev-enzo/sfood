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
        'name_en',
        'slug',
        'short_description',
        'short_description_en',
        'description',
        'description_en',
        'thumbnail',
        'is_active',
        'is_hot',
        'is_new',
        'view_count',
        'min_price',
    ];

    public function getNameAttribute($value)
    {
        if (app()->getLocale() === 'en' && !empty($this->attributes['name_en'])) {
            return $this->attributes['name_en'];
        }
        return $value;
    }

    public function getShortDescriptionAttribute($value)
    {
        if (app()->getLocale() === 'en' && !empty($this->attributes['short_description_en'])) {
            return $this->attributes['short_description_en'];
        }
        return $value;
    }

    public function getDescriptionAttribute($value)
    {
        if (app()->getLocale() === 'en' && !empty($this->attributes['description_en'])) {
            return $this->attributes['description_en'];
        }
        return $value;
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_hot' => 'boolean',
            'is_new' => 'boolean',
            'min_price' => 'float',
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

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function averageRating(): float
    {
        return (float) $this->reviews()->avg('rating');
    }

    public function defaultVariant(): ?ProductVariant
    {
        return $this->variants()->where('is_active', true)->orderBy('price')->first();
    }

    public function minPrice($filterMin = null, $filterMax = null): float
    {
        $query = $this->variants()->where('is_active', true);

        if ($filterMin) {
            $query->whereRaw('COALESCE(sale_price, price) >= ?', [$filterMin]);
        }
        if ($filterMax) {
            $query->whereRaw('COALESCE(sale_price, price) <= ?', [$filterMax]);
        }

        $val = $query->min(\Illuminate\Support\Facades\DB::raw('COALESCE(sale_price, price)'));

        if ($val === null && ($filterMin || $filterMax)) {
            $val = $this->variants()->where('is_active', true)->min(\Illuminate\Support\Facades\DB::raw('COALESCE(sale_price, price)'));
        }

        if ($val === null) {
            $val = $this->variants()->where('is_active', true)->min('price');
        }

        return (float) ($val ?? 0);
    }

    public function getFilteredImage($filterMin = null, $filterMax = null): ?string
    {
        if (!$filterMin && !$filterMax) {
            return $this->thumbnail;
        }

        $query = $this->variants()->where('is_active', true);

        if ($filterMin) {
            $query->whereRaw('COALESCE(sale_price, price) >= ?', [$filterMin]);
        }
        if ($filterMax) {
            $query->whereRaw('COALESCE(sale_price, price) <= ?', [$filterMax]);
        }

        $variant = $query->whereNotNull('image')->where('image', '!=', '')->orderBy('price')->first();

        if ($variant) {
            return $variant->image;
        }

        return $this->thumbnail;
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')
            ->withTimestamps();
    }

    public function isFavoritedBy(?User $user): bool
    {
        if (!$user) {
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
            } catch (\Throwable $e) {
            }
        });
    }
}
