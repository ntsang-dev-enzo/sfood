<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'order_code',
        'user_id',
        'subtotal',
        'shipping_fee',
        'discount_amount',
        'total_amount',
        'order_status',
        'payment_status',
        'coupon_id',
        'shipping_address',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'shipping_address' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    protected static function booted(): void
    {
        static::updated(function (Order $order) {
            // Reward lucky spin when order is delivered
            if ($order->isDirty('order_status') && $order->order_status === 'delivered') {
                if ($order->total_amount >= 500000) {
                    $spins = floor($order->total_amount / 500000);
                    if ($spins > 0 && $order->user) {
                        $order->user->increment('lucky_spins', $spins);
                        
                        // Notify user (optional)
                        // ...
                    }
                }
            }
        });
    }
}
