<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');

        $orders = Order::with('items')
            ->where('user_id', Auth::id())
            ->when($status, function ($query, $status) {
                return $query->where('order_status', $status);
            })
            ->orderByDesc('created_at')
            ->get();

        return view('orders.index', compact('orders', 'status'));
    }

    public function show(Order $order)
    {
        abort_unless($order->user_id === Auth::id(), 403);

        return redirect()
            ->route('orders.index')
            ->with('success', 'Đặt hàng #'.$order->order_code.' thành công!');
    }

    public function cancel(Order $order)
    {
        abort_unless($order->user_id === Auth::id(), 403);

        if (! in_array($order->order_status, ['pending', 'confirmed'])) {
            return back()->with('error', 'Đơn hàng không thể hủy ở trạng thái hiện tại.');
        }

        $payment = $order->payment;

        if ($payment && $payment->gateway === 'payos' && $order->payment_status === 'pending') {
            $clientId = config('services.payos.client_id');
            $apiKey = config('services.payos.api_key');
            $orderCode = $payment->gateway_response['orderCode'] ?? null;

            if ($orderCode) {
                try {
                    \Illuminate\Support\Facades\Http::withHeaders([
                        'x-client-id' => $clientId,
                        'x-api-key' => $apiKey,
                    ])->post("https://api-merchant.payos.vn/v2/payment-requests/{$orderCode}/cancel", [
                        'cancellationReason' => 'Người dùng hủy đơn',
                    ]);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Cancel PayOS link failed: '.$e->getMessage());
                }
            }
            $payment->update(['status' => 'failed']);
            $order->update(['payment_status' => 'failed', 'order_status' => 'cancelled']);
        } elseif ($order->payment_status === 'paid') {
            $order->update(['payment_status' => 'refund_pending', 'order_status' => 'cancelled']);
        } else {
            $order->update(['order_status' => 'cancelled']);
        }

        return back()->with('success', 'Đơn hàng đã được hủy thành công.');
    }
}
