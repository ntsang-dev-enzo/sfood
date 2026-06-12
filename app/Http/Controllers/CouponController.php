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
            'shipping_fee' => 'nullable|numeric|min:0',
        ]);

        $code = $data['code'];
        $subtotal = $cart->selectedSubtotal();
        // Use frontend's shipping fee or default if not provided
        $shipping = $request->input('shipping_fee', $subtotal >= 1000000 ? 0 : 30000);

        $coupon = Coupon::where('code', $code)->first();
        if (! $coupon) {
            return response()->json(['valid' => false, 'message' => 'Mã không tồn tại.'], 404);
        }

        // Tính tổng phụ chỉ dành cho các sản phẩm KHÔNG phải là flash sale và áp dụng được mã này
        $eligibleSubtotal = $coupon->getEligibleSubtotal($cart->selectedItems());

        if ($eligibleSubtotal <= 0 && $subtotal > 0) {
            return response()->json(['valid' => false, 'message' => 'Sản phẩm trong giỏ hàng không đủ điều kiện áp dụng mã này (hoặc đang Flash Sale).'], 422);
        }

        if (! $coupon->isValid($eligibleSubtotal)) {
            return response()->json(['valid' => false, 'message' => 'Mã không hợp lệ cho giá trị đơn hàng đủ điều kiện hiện tại.'], 422);
        }



        $discount = $coupon->calculateDiscount($eligibleSubtotal, $shipping);

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
