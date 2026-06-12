<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'applicable_category_ids',
        'applicable_product_ids',
        'applicable_variant_ids',
        'min_order_amount',
        'starts_at',
        'expires_at',
        'usage_limit',
        'used_count',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'applicable_category_ids' => 'array',
            'applicable_product_ids' => 'array',
            'applicable_variant_ids' => 'array',
            'min_order_amount' => 'decimal:2',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function isValid(float $subtotal): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at && now()->lt($this->starts_at)) {
            return false;
        }

        if ($this->expires_at && now()->gt($this->expires_at)) {
            return false;
        }

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        if ($subtotal < $this->min_order_amount) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $subtotal, float $shippingFee): float
    {
        return match ($this->type) {
            'percent' => round($subtotal * ($this->value / 100), 2),
            'fixed' => min($this->value, $subtotal),
            'free_shipping' => $shippingFee,
            default => 0,
        };
    }

    public function appliesToCategories(array $categoryIds): bool
    {
        if (empty($this->applicable_category_ids) || count($this->applicable_category_ids) === 0) {
            return true;
        }

        return count(array_intersect($this->applicable_category_ids, $categoryIds)) > 0;
    }

    public function appliesToProducts(array $productIds): bool
    {
        if (empty($this->applicable_product_ids) || count($this->applicable_product_ids) === 0) {
            return true;
        }

        return count(array_intersect($this->applicable_product_ids, $productIds)) > 0;
    }

    public function appliesToVariants(array $variantIds): bool
    {
        if (empty($this->applicable_variant_ids) || count($this->applicable_variant_ids) === 0) {
            return true;
        }

        return count(array_intersect($this->applicable_variant_ids, $variantIds)) > 0;
    }

    public function getEligibleSubtotal($items): float
    {
        return $items->filter(function ($item) {
            if ($item->productVariant->isFlashActive()) return false;
            
            $catId = $item->productVariant->product->category_id;
            $prodId = $item->productVariant->product_id;
            $varId = $item->productVariant->id;

            if (!$this->appliesToCategories([$catId])) return false;
            if (!$this->appliesToProducts([$prodId])) return false;
            if (!$this->appliesToVariants([$varId])) return false;

            return true;
        })->sum(fn($item) => $item->subtotal());
    }

    public function collectedByUsers()
    {
        return $this->belongsToMany(User::class, 'user_coupons')
            ->withPivot('is_used')
            ->withTimestamps();
    }
}
