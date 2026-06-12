<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PromoController extends Controller
{
    public function index()
    {
        // Get public active coupons (user_id is null)
        $coupons = \App\Models\Coupon::whereNull('user_id')
            ->where('is_active', true)
            ->orderBy('id', 'desc')
            ->get();

        // If user logged in, get their collected public coupons to avoid showing 'Save' again
        // Actually, we can just check if they have a coupon with the same base code.
        $userCoupons = collect();
        if (auth()->check()) {
            $userCoupons = \App\Models\Coupon::where('user_id', auth()->id())->get();
        }

        return view('promo.index', compact('coupons', 'userCoupons'));
    }

    public function save(Request $request, \App\Models\Coupon $coupon)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để lưu mã giảm giá.');
        }

        if ($coupon->user_id !== null) {
            return back()->with('error', 'Mã này không thể lưu.');
        }

        // Check if user already collected it
        // We assume cloned coupons have a specific pattern, or we just check if a coupon with same value/type exists (simple for now)
        $alreadyCollected = \App\Models\Coupon::where('user_id', auth()->id())
            ->where('code', 'like', $coupon->code . '_%')
            ->exists();

        if ($alreadyCollected) {
            return back()->with('error', 'Bạn đã lưu mã này rồi.');
        }

        // Clone the coupon
        $newCoupon = $coupon->replicate();
        $newCoupon->user_id = auth()->id();
        $newCoupon->code = $coupon->code . '_' . strtoupper(\Illuminate\Support\Str::random(4));
        $newCoupon->save();

        return back()->with('success', 'Đã lưu mã giảm giá vào ví của bạn.');
    }
}
