<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartService
{
    public function sessionId(): string
    {
        if (! Session::has('guest_cart_id')) {
            Session::put('guest_cart_id', (string) str()->uuid());
        }

        return Session::get('guest_cart_id');
    }

    public function query()
    {
        if (Auth::check()) {
            return CartItem::with('productVariant.product')
                ->where('user_id', Auth::id());
        }

        return CartItem::with('productVariant.product')
            ->where('session_id', $this->sessionId());
    }

    public function items(): Collection
    {
        return $this->query()->get();
    }

    public function count(): int
    {
        return (int) $this->query()->sum('quantity');
    }

    public function subtotal(): float
    {
        return $this->items()->sum(fn (CartItem $item) => $item->subtotal());
    }

    public function add(int $variantId, int $quantity = 1): void
    {
        $variant = ProductVariant::findOrFail($variantId);

        if (! $variant->inStock()) {
            throw new \RuntimeException('Sản phẩm đã hết hàng.');
        }

        $attributes = Auth::check()
            ? ['user_id' => Auth::id(), 'product_variant_id' => $variantId]
            : ['session_id' => $this->sessionId(), 'product_variant_id' => $variantId];

        $item = CartItem::firstOrNew($attributes);
        $item->quantity = min(($item->quantity ?? 0) + $quantity, $variant->stock);
        $item->save();
    }

    public function addMany(array $variantQuantities): void
    {
        foreach ($variantQuantities as $variantId => $quantity) {
            $this->add((int) $variantId, (int) $quantity);
        }
    }

    public function update(int $itemId, int $quantity): void
    {
        $item = $this->query()->findOrFail($itemId);
        $item->quantity = max(1, min($quantity, $item->productVariant->stock));
        $item->save();
    }

    public function remove(int $itemId): void
    {
        $this->query()->where('id', $itemId)->delete();
    }

    public function clear(): void
    {
        $this->query()->delete();
    }

    public function selectedItems(): Collection
    {
        return $this->query()->where('is_selected', true)->get();
    }

    public function selectedSubtotal(): float
    {
        return $this->selectedItems()->sum(fn (CartItem $item) => $item->subtotal());
    }

    public function toggleSelect(int $itemId, bool $selected): void
    {
        $item = $this->query()->findOrFail($itemId);
        $item->is_selected = $selected;
        $item->save();
    }

    public function toggleSelectAll(bool $selected): void
    {
        $this->query()->update(['is_selected' => $selected]);
    }

    public function clearSelected(): void
    {
        $this->query()->where('is_selected', true)->delete();
    }

    public function mergeGuestCartToUser(int $userId): void
    {
        if (! Session::has('guest_cart_id')) {
            return;
        }

        $sessionId = Session::get('guest_cart_id');
        $guestItems = CartItem::where('session_id', $sessionId)->get();

        foreach ($guestItems as $guestItem) {
            $userItem = CartItem::firstOrNew([
                'user_id' => $userId,
                'product_variant_id' => $guestItem->product_variant_id,
            ]);

            $userItem->quantity = ($userItem->quantity ?? 0) + $guestItem->quantity;
            $userItem->is_selected = $guestItem->is_selected;
            $userItem->session_id = null;
            $userItem->save();
            $guestItem->delete();
        }

        Session::forget('guest_cart_id');
    }
}
