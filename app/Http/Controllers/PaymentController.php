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

        if ($gateway === 'payos' && ($request->get('status') === 'CANCELLED' || $request->boolean('cancel'))) {
            $orderCode = $request->get('orderCode');
            $payment = $orderCode ? \App\Models\Payment::where('transaction_id', 'PAYOS_' . $orderCode)->first() : null;

            return redirect()
                ->route('orders.index', array_filter(['repay' => $payment?->order_id]))
                ->with('info', 'Bạn đã tạm dừng thanh toán QR. Bạn có thể bấm "Tiếp tục thanh toán QR" để quét lại mã hoặc đổi sang thanh toán khi nhận hàng (COD).');
        }

        return redirect()->route('orders.index')->with('success', 'Thanh toán đã được xử lý thành công.');
    }

    public function payosWebhook(Request $request)
    {
        $this->paymentService->handleCallback('payos', $request->all());

        return response()->json(['success' => true]);
    }
}
