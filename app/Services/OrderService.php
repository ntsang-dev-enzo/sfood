<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ShippingAddress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        protected CartService $cartService,
    ) {}

    public function create(array $data): Order
    {
        $items = $this->cartService->items();

        if ($items->isEmpty()) {
            throw new \RuntimeException('Giỏ hàng trống.');
        }

        return DB::transaction(function () use ($data, $items) {
            $subtotal = $this->cartService->subtotal();
            $shippingFee = $subtotal >= 300000 ? 0 : 30000;
            $discount = 0;
            $coupon = null;

            if (! empty($data['coupon_code'])) {
                $coupon = Coupon::where('code', $data['coupon_code'])->first();
                if ($coupon && $coupon->isValid($subtotal)) {
                    $discount = $coupon->calculateDiscount($subtotal, $shippingFee);
                    if ($coupon->type === 'free_shipping') {
                        $shippingFee = 0;
                    }
                    $coupon->increment('used_count');
                }
            }

            $address = $this->resolveAddress($data);

            $order = Order::create([
                'order_code' => 'SF'.strtoupper(Str::random(8)),
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

            return $order->load('items', 'payment');
        });
    }

    protected function resolveAddress(array $data): array
    {
        if (! empty($data['shipping_address_id'])) {
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
