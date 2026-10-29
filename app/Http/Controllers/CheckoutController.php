<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Models\Coupon;
use App\Models\Category;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function index(CartService $cart)
    {
        $items = $cart->selectedItems();

        if ($items->isEmpty()) {
            return redirect('/')->with('error', 'Bạn chưa chọn sản phẩm nào để thanh toán.');
        }

        $subtotal = $cart->selectedSubtotal();
        $shipping = $subtotal >= 1000000 ? 0 : 30000;

        // compute available coupons for this cart
        $items = $cart->selectedItems();
        $eligibleSubtotal = $items->filter(fn($item) => !$item->productVariant->isFlashActive())->sum(fn($item) => $item->subtotal());
        $categoryIds = $items->filter(fn($item) => !$item->productVariant->isFlashActive())->pluck('productVariant.product.category_id')->filter()->unique()->values()->all();

        $collectedIds = Auth::user()->collectedCoupons()->wherePivot('is_used', false)->pluck('coupons.id')->toArray();

        $availableCoupons = Coupon::where('is_active', true)
            ->whereIn('id', $collectedIds)
            ->get()
            ->filter(function (Coupon $c) use ($subtotal, $items) {
                $couponEligibleSubtotal = $c->getEligibleSubtotal($items);
                if ($couponEligibleSubtotal <= 0 && $subtotal > 0) return false;
                return $c->isValid($couponEligibleSubtotal);
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

    public function store(
        Request $request,
        CartService $cart,
        OrderService $orderService,
        PaymentService $paymentService
    ) {

        if ($cart->selectedItems()->isEmpty()) {
            return redirect('/')->with('error', 'Giỏ hàng của bạn đã thay đổi. Vui lòng kiểm tra lại.');
        }

        $rules = [
            'shipping_address_id' => 'nullable|exists:shipping_addresses,id',
            'coupon_code' => 'nullable|string',
            'payment_gateway' => 'required|in:cod,payos',
            'notes' => 'nullable|string',

            // shipping fee từ frontend
            'shipping_fee' => 'nullable|numeric|min:0',
            'use_points' => 'nullable|boolean',
            'save_address' => 'nullable|boolean',
        ];

        if (! $request->shipping_address_id) {

            $rules += [
                'recipient_name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'address_line' => 'required|string|max:500',
                'city' => 'required|string|max:100',
                'district' => 'nullable|string|max:100',
                'ward' => 'nullable|string|max:100',
            ];
        }

        $data = $request->validate($rules);

        // If user selected a saved shipping address, load its fields
        if (!empty($data['shipping_address_id'])) {
            $saved = Auth::user()->shippingAddresses()->find($data['shipping_address_id']);
            if ($saved) {
                $data['recipient_name'] = $saved->recipient_name ?? ($data['recipient_name'] ?? '');
                $data['phone'] = $saved->phone ?? ($data['phone'] ?? '');
                $data['address_line'] = $saved->address_line ?? ($data['address_line'] ?? '');
                $data['ward'] = $saved->ward ?? ($data['ward'] ?? '');
                $data['district'] = $saved->district ?? ($data['district'] ?? '');
                $data['city'] = $saved->city ?? ($data['city'] ?? '');
            }
        } else {
            // Lưu địa chỉ mới vào sổ địa chỉ để lần sau có sẵn
            if (Auth::check() && ($request->boolean('save_address') || Auth::user()->shippingAddresses()->count() === 0)) {
                if (Auth::user()->shippingAddresses()->count() < 10) {
                    $isFirst = Auth::user()->shippingAddresses()->count() === 0;
                    $newSaved = Auth::user()->shippingAddresses()->create([
                        'recipient_name' => $data['recipient_name'],
                        'phone' => $data['phone'],
                        'address_line' => $data['address_line'],
                        'city' => $data['city'],
                        'district' => $data['district'] ?? null,
                        'ward' => $data['ward'] ?? null,
                        'is_default' => $isFirst,
                    ]);
                    $data['shipping_address_id'] = $newSaved->id;
                }
            }
        }

        /*
    |--------------------------------------------------------------------------
    | VERIFY SHIPPING BACKEND
    |--------------------------------------------------------------------------
    */

        $subtotal = $cart->selectedSubtotal();
        $items = $cart->selectedItems();
        $eligibleSubtotal = 0;

        $shipping = 30000;

        // free ship nội thành Cần Thơ (normalize text to handle diacritics/casing)
        // Lỗi đang gặp thường do khác format chuỗi (khoảng trắng/dấu gạch) giữa frontend & request backend.
        $cityRaw = (string) ($data['city'] ?? '');
        $districtRaw = (string) ($data['district'] ?? '');

        $cityNorm = Str::ascii(mb_strtolower($cityRaw));
        $districtNorm = Str::ascii(mb_strtolower($districtRaw));

        // collapse multiple spaces and trim
        $cityNorm = trim(preg_replace('/\s+/', ' ', $cityNorm) ?? '');
        $districtNorm = trim(preg_replace('/\s+/', ' ', $districtNorm) ?? '');

        $isCanTho = str_contains($cityNorm, 'can tho');
        $isNinhKieu = (bool) preg_match('/ninh\s*kieu|ninh-kieu/', $districtNorm);
        $isCaiRang = (bool) preg_match('/cai\s*rang|cai-rang/', $districtNorm);

        if ($isCanTho && ($isNinhKieu || $isCaiRang)) {
            $shipping = 0;
        }


        // free ship đơn trên 1 triệu
        if ($subtotal >= 1000000) {
            $shipping = 0;
        }

        // Recompute discount and apply free shipping if net total after discount >= 300,000
        $discount = 0;
        if (!empty($data['coupon_code'])) {
            $coupon = $coupon ?? Coupon::where('code', $data['coupon_code'])->first();
            if ($coupon) {
                $eligibleSubtotal = $coupon->getEligibleSubtotal($items);
                if ($eligibleSubtotal > 0 && $coupon->isValid($eligibleSubtotal)) {
                    $discount = $coupon->calculateDiscount($eligibleSubtotal, $shipping);
                }
            }
        }

        $netTotal = $subtotal - ($discount ?: 0);
        if ($netTotal >= 1000000) {
            $shipping = 0;
        }

        // overwrite frontend shipping
        $data['shipping_fee'] = (float) $shipping;


        /*
    |--------------------------------------------------------------------------
    | CREATE ORDER
    |--------------------------------------------------------------------------
    */

        $order = $orderService->create([

            'shipping_address_id' =>
            $data['shipping_address_id'] ?? null,

            'recipient_name' =>
            $data['recipient_name'] ?? '',

            'phone' =>
            $data['phone'] ?? '',

            'address_line' =>
            $data['address_line'] ?? '',

            'ward' =>
            $data['ward'] ?? '',

            'district' =>
            $data['district'] ?? '',

            'city' =>
            $data['city'] ?? '',

            'coupon_code' =>
            $data['coupon_code'] ?? '',

            'payment_gateway' =>
            $data['payment_gateway'],

            'notes' =>
            $data['notes'] ?? null,

            'shipping_fee' =>
            $data['shipping_fee'],
        ]);

        $pointsUsed = 0;
        $pointsDiscount = 0;
        if (!empty($data['use_points']) && Auth::user()->reward_points > 0) {
            $availablePoints = Auth::user()->reward_points;
            $pointsUsed = min($availablePoints, $order->total_amount);
            $pointsDiscount = $pointsUsed;

            if ($pointsUsed > 0) {
                $order->points_used = $pointsUsed;
                $order->points_discount = $pointsDiscount;
                $order->total_amount = max(0, $order->total_amount - $pointsDiscount);
                $order->save();

                if ($order->payment) {
                    $order->payment->amount = $order->total_amount;
                    $order->payment->save();
                }

                Auth::user()->decrement('reward_points', $pointsUsed);
            }
        }

        /*
    |--------------------------------------------------------------------------
    | ONLINE PAYMENT
    |--------------------------------------------------------------------------
    */

        if (
            in_array(
                $data['payment_gateway'],
                ['payos']
            )
        ) {

            $url = $paymentService
                ->initiateOnlinePayment(
                    $order,
                    $data['payment_gateway']
                );

            return redirect()->away($url);
        }

        /*
    |--------------------------------------------------------------------------
    | COD SUCCESS
    |--------------------------------------------------------------------------
    */

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Đặt hàng thành công!');
    }
}
