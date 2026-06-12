<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VoucherController extends Controller
{
    public function index()
    {
        $coupons = Coupon::where('is_active', true)
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->get();

        $collectedIds = Auth::check() 
            ? Auth::user()->collectedCoupons()->pluck('coupons.id')->toArray() 
            : [];

        return view('vouchers.index', compact('coupons', 'collectedIds'));
    }

    public function collect(Request $request, Coupon $coupon)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Bạn cần đăng nhập để thu thập voucher'], 401);
        }

        $user = Auth::user();

        if ($user->collectedCoupons()->where('coupon_id', $coupon->id)->exists()) {
            return response()->json(['message' => 'Bạn đã thu thập voucher này rồi'], 400);
        }

        $user->collectedCoupons()->attach($coupon->id);

        return response()->json(['message' => 'Đã lưu voucher vào ví thành công']);
    }
}
