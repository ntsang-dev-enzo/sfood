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

        $orders = Order::with(['items', 'reviews'])
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

    public function refund(Order $order)
    {
        abort_unless($order->user_id === Auth::id(), 403);

        // Chỉ cho phép hoàn tiền khi đã thanh toán
        if ($order->payment_status !== 'paid') {
            return back()->with('error', 'Đơn hàng chưa được thanh toán, không thể yêu cầu hoàn tiền.');
        }

        // Không cho hoàn tiền khi đang giao hàng
        if ($order->order_status === 'shipping') {
            return back()->with('error', 'Đơn hàng đang giao, không thể yêu cầu hoàn tiền. Vui lòng liên hệ CSKH.');
        }

        // Không cho hoàn tiền khi đã giao hoặc đã hủy
        if (in_array($order->order_status, ['delivered', 'cancelled'])) {
            return back()->with('error', 'Đơn hàng không thể yêu cầu hoàn tiền ở trạng thái hiện tại.');
        }

        // Đã yêu cầu hoàn tiền rồi
        if ($order->payment_status === 'refund_pending') {
            return back()->with('error', 'Đơn hàng đã được yêu cầu hoàn tiền, vui lòng chờ xử lý.');
        }

        $order->update([
            'payment_status' => 'refund_pending',
            'order_status' => 'cancelled',
        ]);

        // Notify user
        if ($order->user) {
            $order->user->notify(new \App\Notifications\OrderUpdated([
                'title' => 'Yêu cầu hoàn tiền đã được ghi nhận',
                'message' => 'Đơn hàng #' . $order->order_code . ' đã được ghi nhận yêu cầu hoàn tiền. Chúng tôi sẽ xử lý trong 1-3 ngày làm việc.',
                'url' => route('orders.index', ['status' => 'cancelled']),
                'type' => 'order',
            ]));
        }

        return back()->with('success', 'Yêu cầu hoàn tiền đã được ghi nhận. Chúng tôi sẽ xử lý trong 1-3 ngày làm việc.');
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
