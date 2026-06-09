<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Coupon;
use App\Services\CartService;

class CouponController extends Controller
{
    public function validateForCart(Request $request, CartService $cart)
    {
        $data = $request->validate([
            'code' => 'required|string|max:100',
        ]);

        $code = $data['code'];
        $subtotal = $cart->subtotal();
        $shipping = $subtotal >= 300000 ? 0 : 30000;

        $coupon = Coupon::where('code', $code)->first();
        if (! $coupon) {
            return response()->json(['valid' => false, 'message' => 'Mã không tồn tại.'], 404);
        }

        if (! $coupon->isValid($subtotal)) {
            return response()->json(['valid' => false, 'message' => 'Mã không hợp lệ cho giá trị đơn hàng hiện tại.'], 422);
        }

        // check categories applicability
        $categoryIds = $cart->items()->pluck('productVariant.product.category_id')->filter()->unique()->values()->all();
        if (! $coupon->appliesToCategories($categoryIds)) {
            return response()->json(['valid' => false, 'message' => 'Mã không áp dụng cho sản phẩm trong giỏ hàng.'], 422);
        }

        $discount = $coupon->calculateDiscount($subtotal, $shipping);
        if ($coupon->type === 'free_shipping') {
            $shipping = 0;
        }

        $total = max(0, $subtotal + $shipping - $discount);

        // persist applied coupon code in session
        Session::put('applied_coupon', $coupon->code);

        return response()->json([
            'valid' => true,
            'discount' => $discount,
            'shipping' => $shipping,
            'total' => $total,
            'coupon_id' => $coupon->id,
            'message' => 'Mã hợp lệ',
        ]);
    }

    public function remove(Request $request)
    {
        Session::forget('applied_coupon');

        return response()->json(['ok' => true]);
    }
}
