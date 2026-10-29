<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(CartService $cart)
    {
        if ($cart->items()->isEmpty()) {
            return redirect()->route('products.index')->with('info', 'Giỏ hàng của bạn đang trống.');
        }

        return redirect()->route('checkout.index');
    }

    public function update(Request $request, CartService $cart)
    {
        $data = $request->validate([
            'item_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart->update((int) $data['item_id'], (int) $data['quantity']);

        if ($request->expectsJson()) {
            return response()->json($this->cartPayload($cart, (int) $data['item_id']));
        }

        return back()->with('success', 'Đã cập nhật giỏ hàng.');
    }

    public function remove(Request $request, CartService $cart)
    {
        $data = $request->validate([
            'item_id' => 'required|integer',
        ]);

        $cart->remove((int) $data['item_id']);

        if ($request->expectsJson()) {
            return response()->json($this->cartPayload($cart));
        }

        return back()->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng.');
    }

    public function clear(Request $request, CartService $cart)
    {
        $cart->clear();

        if ($request->expectsJson()) {
            return response()->json([
                'subtotal' => 0,
                'subtotal_formatted' => '0đ',
                'count' => 0,
                'empty' => true,
            ]);
        }

        return back()->with('success', 'Đã xóa toàn bộ giỏ hàng.');
    }

    public function toggleSelect(Request $request, CartService $cart)
    {
        $data = $request->validate([
            'item_id' => 'required|integer',
            'selected' => 'required|boolean',
        ]);

        $cart->toggleSelect((int) $data['item_id'], (bool) $data['selected']);

        if ($request->expectsJson()) {
            return response()->json($this->cartPayload($cart, (int) $data['item_id']));
        }

        return back();
    }

    public function toggleSelectAll(Request $request, CartService $cart)
    {
        $data = $request->validate([
            'selected' => 'required|boolean',
        ]);

        $cart->toggleSelectAll((bool) $data['selected']);

        if ($request->expectsJson()) {
            return response()->json($this->cartPayload($cart));
        }

        return back();
    }

    protected function cartPayload(CartService $cart, ?int $itemId = null): array
    {
        $payload = [
            'subtotal' => $cart->subtotal(),
            'subtotal_formatted' => number_format($cart->subtotal()).'đ',
            'selected_subtotal' => $cart->selectedSubtotal(),
            'selected_subtotal_formatted' => number_format($cart->selectedSubtotal()).'đ',
            'count' => $cart->count(),
            'empty' => $cart->items()->isEmpty(),
        ];

        if ($itemId) {
            $item = $cart->items()->firstWhere('id', $itemId);
            if ($item) {
                $payload['item'] = [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                    'is_selected' => $item->is_selected,
                    'subtotal' => $item->subtotal(),
                    'subtotal_formatted' => number_format($item->subtotal()).'đ',
                    'unit_price_formatted' => number_format($item->productVariant->effectivePrice()).'đ',
                ];
            }
        }

        return $payload;
    }
}
