<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ShippingAddress;
use App\Notifications\OrderUpdated;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        protected CartService $cartService,
    ) {
    }

    public function create(array $data): Order
    {
        $items = $this->cartService->items();

        if ($items->isEmpty()) {
            throw new \RuntimeException('Giỏ hàng trống.');
        }

        return DB::transaction(function () use ($data, $items) {
            $subtotal = $this->cartService->subtotal();
            // Prefer shipping fee computed by frontend/controller when provided
            // Important: ensure 0 stays 0 (avoid empty-string/boolean edge cases)
            $shippingFee = array_key_exists('shipping_fee', $data)
                ? (float) ($data['shipping_fee'] ?? 0)
                : ($subtotal >= 300000 ? 0 : 30000);


            $discount = 0;
            $coupon = null;


            // Backend hard guarantee: if frontend/controller passed shipping_fee=0, keep it.
            // (Đặt sớm để tránh bị override bởi các rule khác)
            if (array_key_exists('shipping_fee', $data) && (float) ($data['shipping_fee'] ?? 0) === 0.0) {
                $shippingFee = 0;
            }
            if (! empty($data['coupon_code'])) {
                $coupon = Coupon::where('code', $data['coupon_code'])->first();
                if ($coupon && $coupon->isValid($subtotal)) {
                    $categoryIds = $items->pluck('productVariant.product.category_id')->filter()->unique()->values()->all();
                    if ($coupon->appliesToCategories($categoryIds)) {
                        // calculate discount based on current shippingFee
                        $discount = $coupon->calculateDiscount($subtotal, $shippingFee);
                        if ($coupon->type === 'free_shipping') {
                            // coupon removes shipping
                            $shippingFee = 0;
                        }
                        $coupon->increment('used_count');
                    }
                }
            }



            // If net total after discount reaches threshold, grant free shipping

            $netTotal = $subtotal - ($discount ?: 0);
            if ($netTotal >= 1000000) {
                $shippingFee = 0;
            }


            $address = $this->resolveAddress($data);

            $order = Order::create([
                'order_code' => 'SF' . strtoupper(Str::random(8)),
                'user_id' => Auth::id(),
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'discount_amount' => $discount,
                'total_amount' => max(0, $subtotal + $shippingFee - $discount),

                'order_status' => 'pending',
                'payment_status' => 'pending',
                'coupon_id' => $coupon?->id,
                'shipping_address' => $address,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                $variant = $item->productVariant;
                $price = $variant->effectivePrice();

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $variant->product->name,
                    'variant_name' => $variant->name,
                    'unit_price' => $price,
                    'quantity' => $item->quantity,
                    'subtotal' => $price * $item->quantity,
                ]);

                $variant->decrement('stock', $item->quantity);
            }

            Payment::create([
                'order_id' => $order->id,
                'gateway' => $data['payment_gateway'] ?? 'cod',
                'amount' => $order->total_amount,
                'status' => 'pending',
            ]);

            $this->cartService->clear();

            // Gửi thông báo cho người dùng
            $order->user->notify(new OrderUpdated([
                'title' => 'Đặt hàng thành công 🌿',
                'message' => 'Đơn hàng ' . $order->order_code . ' đã được đặt thành công. Cảm ơn bạn đã tin dùng SFood!',
                'url' => route('orders.show', $order),
                'type' => 'order',
            ]));

            return $order->load('items', 'payment');
        });
    }

    protected function resolveAddress(array $data): array
    {
        if (!empty($data['shipping_address_id'])) {
            $address = ShippingAddress::where('user_id', Auth::id())
                ->findOrFail($data['shipping_address_id']);

            return [
                'recipient_name' => $address->recipient_name,
                'phone' => $address->phone,
                'address' => $address->fullAddress(),
            ];
        }

        return [
            'recipient_name' => $data['recipient_name'],
            'phone' => $data['phone'],
            'address' => collect([
                $data['address_line'] ?? '',
                $data['ward'] ?? '',
                $data['district'] ?? '',
                $data['city'] ?? '',
            ])->filter()->implode(', '),
        ];
    }
}
