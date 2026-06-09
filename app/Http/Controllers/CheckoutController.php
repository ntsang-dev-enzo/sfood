<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Models\Coupon;
use App\Models\Category;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function index(CartService $cart)
    {
        if ($cart->items()->isEmpty()) {
            return redirect()->route('cart');
        }

        $subtotal = $cart->subtotal();
        $shipping = $subtotal >= 300000 ? 0 : 30000;

        // compute available coupons for this cart
        $items = $cart->items();
        $categoryIds = $items->pluck('productVariant.product.category_id')->filter()->unique()->values()->all();

        $availableCoupons = Coupon::where('is_active', true)->get()->filter(function (Coupon $c) use ($subtotal, $categoryIds) {
            return $c->isValid($subtotal) && $c->appliesToCategories($categoryIds);
        })->values()->map(function (Coupon $c) {
            $names = [];
            if (!empty($c->applicable_category_ids)) {
                $names = Category::whereIn('id', $c->applicable_category_ids)->pluck('name')->toArray();
            }
            $c->category_names = $names;
            return $c;
        });

        return view('checkout.index', [
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $subtotal + $shipping,
            'addresses' => Auth::user()->shippingAddresses,
            'appliedCoupon' => session('applied_coupon'),
            'availableCoupons' => $availableCoupons,
        ]);
    }

    public function store(Request $request, CartService $cart, OrderService $orderService, PaymentService $paymentService)
    {
        if ($cart->items()->isEmpty()) {
            return redirect()->route('cart');
        }

        $rules = [
            'shipping_address_id' => 'nullable|exists:shipping_addresses,id',
            'coupon_code' => 'nullable|string',
            'payment_gateway' => 'required|in:cod,vnpay,momo,payos',
            'notes' => 'nullable|string',
        ];

        if (! $request->shipping_address_id) {
            $rules += [
                'recipient_name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'address_line' => 'required|string|max:500',
                'city' => 'required|string|max:100',
            ];
        }

        $data = $request->validate($rules);

        $order = $orderService->create([
            'shipping_address_id' => $data['shipping_address_id'] ?? null,
            'recipient_name' => $data['recipient_name'] ?? '',
            'phone' => $data['phone'] ?? '',
            'address_line' => $data['address_line'] ?? '',
            'ward' => $data['ward'] ?? '',
            'district' => $data['district'] ?? '',
            'city' => $data['city'] ?? '',
            'coupon_code' => $data['coupon_code'] ?? '',
            'payment_gateway' => $data['payment_gateway'],
            'notes' => $data['notes'] ?? null,
        ]);

        if (in_array($data['payment_gateway'], ['vnpay', 'momo', 'payos'])) {
            $url = $paymentService->initiateOnlinePayment($order, $data['payment_gateway']);

            return redirect()->away($url);
        }

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Đặt hàng thành công!');
    }
}
