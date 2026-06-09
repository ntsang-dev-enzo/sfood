<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PromoController extends Controller
{
    public function collect()
    {
        return view('promo.collect');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'coupon_code' => 'nullable|string|max:100',
            'shipping_fee' => 'nullable|numeric|min:0',
        ]);

        if (!empty($data['coupon_code'])) {
            Session::put('promo.coupon_code', $data['coupon_code']);
        }

        if (isset($data['shipping_fee'])) {
            Session::put('promo.shipping_fee', (float) $data['shipping_fee']);
        }

        return redirect()->route('promo.collect')->with('success', 'Đã lưu mã/ phí ship vào phiên làm việc.');
    }
}
