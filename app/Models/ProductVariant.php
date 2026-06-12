<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Jobs\EmbedImage;
use Illuminate\Support\Facades\DB;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'price',
        'sale_price',
        'flash_sale_price',
        'flash_sale_starts_at',
        'flash_sale_ends_at',
        'flash_sale_stock',
        'flash_sale_sold',
        'stock',
        'image',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'flash_sale_price' => 'decimal:2',
            'flash_sale_starts_at' => 'datetime',
            'flash_sale_ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function isFlashActive(): bool
    {
        if (empty($this->flash_sale_price)) return false;

        $now = now();
        if ($this->flash_sale_starts_at && $now->lt($this->flash_sale_starts_at)) return false;
        if ($this->flash_sale_ends_at && $now->gt($this->flash_sale_ends_at)) return false;

        return true;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function effectivePrice(): float
    {
        if ($this->isFlashActive()) {
            return (float) $this->flash_sale_price;
        }
        return (float) ($this->sale_price ?? $this->price);
    }

    public function inStock(): bool
    {
        return $this->stock > 0 && $this->is_active;
    }

    protected static function booted(): void
    {
        static::created(function (ProductVariant $v) {
            try {
                if (!empty($v->image)) {
                    // store variant images as product_image_id = 0
                    EmbedImage::dispatch($v->product_id, 0, $v->image);
                }
            } catch (\Throwable $e) {}
        });

        static::updated(function (ProductVariant $v) {
            try {
                if ($v->isDirty('image')) {
                    // remove old embedding for this variant image (product_image_id = 0 and matching image_path)
                    DB::table('product_embeddings')
                        ->where('product_id', $v->product_id)
                        ->where('product_image_id', 0)
                        ->where('image_path', $v->getOriginal('image'))
                        ->delete();

                    if (!empty($v->image)) {
                        EmbedImage::dispatch($v->product_id, 0, $v->image);
                    }
                }
            } catch (\Throwable $e) {}
        });

        static::deleted(function (ProductVariant $v) {
            try {
                if (!empty($v->image)) {
                    DB::table('product_embeddings')
                        ->where('product_id', $v->product_id)
                        ->where('product_image_id', 0)
                        ->where('image_path', $v->image)
                        ->delete();
                }
            } catch (\Throwable $e) {}
        });

        // Saved covers both create and update flows (handles Filament/Livewire upload timing)
        static::saved(function (ProductVariant $v) {
            try {
                if (empty($v->image)) return;

                // if an embedding already exists for this product + image (variant-level, product_image_id=0), skip
                $exists = DB::table('product_embeddings')
                    ->where('product_id', $v->product_id)
                    ->where('product_image_id', 0)
                    ->where('image_path', $v->image)
                    ->exists();

                if (!$exists) {
                    EmbedImage::dispatch($v->product_id, 0, $v->image);
                }
            } catch (\Throwable $e) {}
        });
    }
}
