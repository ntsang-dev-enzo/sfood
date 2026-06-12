<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WheelPrize extends Model
{
    protected $fillable = [
        'name',
        'type',
        'value',
        'min_order_amount',
        'weight',
        'is_miss',
        'is_active',
        'applicable_category_ids',
        'applicable_product_ids',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'is_miss' => 'boolean',
            'is_active' => 'boolean',
            'applicable_category_ids' => 'array',
            'applicable_product_ids' => 'array',
        ];
    }
}
