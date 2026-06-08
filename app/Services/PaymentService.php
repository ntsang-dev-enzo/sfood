<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        protected PayOSService $payOS,
    ) {}

    public function initiateOnlinePayment(Order $order, string $gateway): string
    {
        $payment = $order->payment;
        $payment->update([
            'gateway' => $gateway,
            'transaction_id' => strtoupper($gateway).'_'.Str::random(12),
        ]);

        return match ($gateway) {
            'payos' => $this->payOS->createPaymentLink($order, $payment) ?? route('orders.show', $order),
            'vnpay' => $this->buildVnPayUrl($order, $payment),
            'momo' => $this->buildMoMoUrl($order, $payment),
            default => route('orders.show', $order),
        };
    }

    public function handleCallback(string $gateway, array $data): void
    {
        if ($gateway === 'payos') {
            $this->handlePayOSCallback($data);

            return;
        }

        $transactionId = $data['transaction_id'] ?? $data['vnp_TxnRef'] ?? $data['orderId'] ?? null;

        if (! $transactionId) {
            return;
        }

        $payment = Payment::where('transaction_id', $transactionId)->first();

        if (! $payment) {
            return;
        }

        $success = ($data['status'] ?? $data['vnp_ResponseCode'] ?? '') === '00'
            || ($data['resultCode'] ?? null) === 0;

        $this->markPayment($payment, $success, $data);
    }

    protected function handlePayOSCallback(array $data): void
    {
        $orderCode = $data['orderCode'] ?? null;
        if (! $orderCode) {
            return;
        }

        $payment = Payment::where('transaction_id', 'PAYOS_'.$orderCode)->first();
        if (! $payment) {
            return;
        }

        $success = ($data['code'] ?? '') === '00' || ($data['status'] ?? '') === 'PAID';

        $this->markPayment($payment, $success, $data);
    }

    protected function markPayment(Payment $payment, bool $success, array $data): void
    {
        $payment->update([
            'status' => $success ? 'paid' : 'failed',
            'gateway_response' => $data,
        ]);

        $payment->order->update([
            'payment_status' => $success ? 'paid' : 'failed',
            'order_status' => $success ? 'confirmed' : 'pending',
        ]);
    }

    protected function buildVnPayUrl(Order $order, Payment $payment): string
    {
        $params = http_build_query([
            'vnp_Amount' => (int) ($order->total_amount * 100),
            'vnp_OrderInfo' => 'Thanh toan don hang '.$order->order_code,
            'vnp_TxnRef' => $payment->transaction_id,
            'vnp_ReturnUrl' => route('payment.callback', 'vnpay'),
        ]);

        return config('services.vnpay.url', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html').'?'.$params;
    }

    protected function buildMoMoUrl(Order $order, Payment $payment): string
    {
        $params = http_build_query([
            'partnerCode' => config('services.momo.partner_code', 'MOMO'),
            'orderId' => $payment->transaction_id,
            'amount' => (int) $order->total_amount,
            'orderInfo' => 'Thanh toan don hang '.$order->order_code,
            'redirectUrl' => route('payment.callback', 'momo'),
        ]);

        return config('services.momo.url', 'https://test-payment.momo.vn/v2/gateway/pay').'?'.$params;
    }
}
