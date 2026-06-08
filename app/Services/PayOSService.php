<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayOSService
{
    public function createPaymentLink(Order $order, Payment $payment): ?string
    {
        $clientId = config('services.payos.client_id');
        $apiKey = config('services.payos.api_key');
        $checksumKey = config('services.payos.checksum_key');

        if (! $clientId || ! $apiKey || ! $checksumKey) {
            Log::warning('PayOS chưa được cấu hình trong .env');

            return null;
        }

        $orderCode = (int) ($order->id.time() % 100000);
        $amount = (int) $order->total_amount;
        $description = 'Thanh toan '.$order->order_code;
        $returnUrl = route('payment.callback', 'payos');
        $cancelUrl = route('cart');

        $data = [
            'orderCode' => $orderCode,
            'amount' => $amount,
            'description' => $description,
            'returnUrl' => $returnUrl,
            'cancelUrl' => $cancelUrl,
        ];

        $dataStr = "amount={$amount}&cancelUrl={$cancelUrl}&description={$description}&orderCode={$orderCode}&returnUrl={$returnUrl}";
        $signature = hash_hmac('sha256', $dataStr, $checksumKey);

        $payment->update([
            'transaction_id' => 'PAYOS_'.$orderCode,
            'gateway_response' => ['orderCode' => $orderCode],
        ]);

        try {
            $response = Http::withHeaders([
                'x-client-id' => $clientId,
                'x-api-key' => $apiKey,
            ])->post('https://api-merchant.payos.vn/v2/payment-requests', array_merge($data, [
                'signature' => $signature,
            ]));

            if ($response->successful() && $response->json('data.checkoutUrl')) {
                return $response->json('data.checkoutUrl');
            }

            Log::error('PayOS error', ['response' => $response->json()]);
        } catch (\Throwable $e) {
            Log::error('PayOS exception: '.$e->getMessage());
        }

        return null;
    }

    public function verifyWebhook(array $data): bool
    {
        $checksumKey = config('services.payos.checksum_key');
        if (! $checksumKey || empty($data['signature'])) {
            return false;
        }

        $signature = $data['signature'];
        unset($data['signature']);

        ksort($data);
        $dataStr = implode('&', array_map(fn ($k, $v) => "$k=$v", array_keys($data), $data));

        return hash_hmac('sha256', $dataStr, $checksumKey) === $signature;
    }
}
