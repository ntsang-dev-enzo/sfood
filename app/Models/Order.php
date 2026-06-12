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
            // Reward points when order is delivered (1% of total_amount)
            if ($order->isDirty('order_status') && $order->order_status === 'delivered') {
                if ($order->user) {
                    $pointsText = '';
                    if ($order->total_amount > 0) {
                        $points = floor($order->total_amount * 0.01);
                        if ($points > 0) {
                            $order->user->increment('reward_points', $points);
                            $pointsText = ' Bạn nhận được ' . number_format($points) . ' điểm thưởng!';
                        }
                    }
                    $order->user->notify(new \App\Notifications\OrderUpdated([
                        'title' => 'Đơn hàng đã giao thành công',
                        'message' => 'Đơn hàng #' . $order->order_code . ' đã được giao thành công.' . $pointsText,
                        'action' => 'review_order',
                        'url' => route('orders.index', ['status' => 'delivered']),
                        'type' => 'order'
                    ]));
                }
            }

            // Restore coupon if order is cancelled
            if ($order->isDirty('order_status') && $order->order_status === 'cancelled') {
                if ($order->coupon_id) {
                    $coupon = $order->coupon;
                    if ($coupon) {
                        if ($coupon->used_count > 0) {
                            $coupon->decrement('used_count');
                        }
                        if ($order->user_id) {
                            $order->user->collectedCoupons()->updateExistingPivot($coupon->id, [
                                'is_used' => false
                            ]);
                        }
                    }
                }
                
                // Also restore reward points if they were used in this order
                if ($order->points_used > 0 && $order->user_id) {
                    $order->user->increment('reward_points', $order->points_used);
                }
            }
        });
    }
}
