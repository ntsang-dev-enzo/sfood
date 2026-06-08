<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
    ) {}

    public function callback(Request $request, string $gateway)
    {
        $this->paymentService->handleCallback($gateway, $request->all());

        $message = $gateway === 'payos' && $request->get('status') === 'CANCELLED'
            ? 'Bạn đã hủy thanh toán PayOS.'
            : 'Thanh toán đã được xử lý.';

        return redirect()->route('orders.index')->with('success', $message);
    }

    public function payosWebhook(Request $request)
    {
        $this->paymentService->handleCallback('payos', $request->all());

        return response()->json(['success' => true]);
    }
}
