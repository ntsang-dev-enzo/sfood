<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
