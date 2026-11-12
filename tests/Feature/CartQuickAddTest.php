<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartQuickAddTest extends TestCase
{
    use RefreshDatabase;

    public function test_quick_add_to_cart_via_ajax_returns_json(): void
    {
        $category = Category::create([
            'name' => 'Rau Củ',
            'slug' => 'rau-cu',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Cà Chua Bi',
            'slug' => 'ca-chua-bi',
            'is_active' => true,
        ]);

        $variant1 = ProductVariant::create([
            'product_id' => $product->id,
            'name' => '500g',
            'sku' => 'CCB-500',
            'price' => 25000,
            'stock' => 50,
            'is_active' => true,
        ]);

        $variant2 = ProductVariant::create([
            'product_id' => $product->id,
            'name' => '1kg',
            'sku' => 'CCB-1000',
            'price' => 45000,
            'stock' => 30,
            'is_active' => true,
        ]);

        $response = $this->postJson(route('products.add-to-cart', $product), [
            'variant_id' => $variant1->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'count' => 2,
        ]);
        $this->assertNotEmpty($response->json('items_html'));
    }

    public function test_cart_index_redirects_to_products_when_empty(): void
    {
        $response = $this->get(route('cart.index'));
        $response->assertRedirect(route('products.index'));
    }

    public function test_checkout_page_renders_without_route_error(): void
    {
        $user = \App\Models\User::factory()->create();

        $category = Category::create([
            'name' => 'Trái Cây',
            'slug' => 'trai-cay',
            'is_active' => true,
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Cam Sành',
            'slug' => 'cam-sanh',
            'is_active' => true,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => '1kg',
            'sku' => 'CS-1',
            'price' => 30000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($user)->post(route('products.add-to-cart', $product), [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('checkout.index'));
        $response->assertStatus(200);
        $response->assertSee(route('cart.index'));
    }

    public function test_checkout_saves_new_shipping_address(): void
    {
        $user = \App\Models\User::factory()->create();

        $category = Category::create([
            'name' => 'Rau Sạch',
            'slug' => 'rau-sach',
            'is_active' => true,
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Xà Lách',
            'slug' => 'xa-lach',
            'is_active' => true,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => '500g',
            'sku' => 'XL-1',
            'price' => 20000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($user)->post(route('products.add-to-cart', $product), [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'shipping_address_id' => '',
            'recipient_name' => 'Trần Văn B',
            'phone' => '0987654321',
            'address_line' => '456 Đường 3/2',
            'city' => 'TP Cần Thơ',
            'district' => 'Ninh Kiều',
            'ward' => 'Xuân Khánh',
            'payment_gateway' => 'cod',
            'save_address' => '1',
        ]);

        $this->assertDatabaseHas('shipping_addresses', [
            'user_id' => $user->id,
            'recipient_name' => 'Trần Văn B',
            'phone' => '0987654321',
            'address_line' => '456 Đường 3/2',
            'city' => 'TP Cần Thơ',
        ]);
    }

    public function test_checkout_uses_selected_saved_address(): void
    {
        $user = \App\Models\User::factory()->create();
        $savedAddress = \App\Models\ShippingAddress::create([
            'user_id' => $user->id,
            'recipient_name' => 'Nguyễn Thị C',
            'phone' => '0909123456',
            'address_line' => '789 Nguyễn Văn Cừ',
            'city' => 'TP Cần Thơ',
            'district' => 'Ninh Kiều',
            'ward' => 'An Khánh',
            'is_default' => true,
        ]);

        $category = Category::create([
            'name' => 'Củ Quả',
            'slug' => 'cu-qua',
            'is_active' => true,
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Cà Rốt',
            'slug' => 'ca-rot',
            'is_active' => true,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => '1kg',
            'sku' => 'CR-1',
            'price' => 25000,
            'stock' => 15,
            'is_active' => true,
        ]);

        $this->actingAs($user)->post(route('products.add-to-cart', $product), [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'shipping_address_id' => $savedAddress->id,
            'payment_gateway' => 'cod',
        ]);

        $order = \App\Models\Order::where('user_id', $user->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals('Nguyễn Thị C', $order->shipping_address['recipient_name']);
        $this->assertEquals('0909123456', $order->shipping_address['phone']);
        $this->assertStringContainsString('789 Nguyễn Văn Cừ', $order->shipping_address['address']);
    }

    public function test_user_can_cancel_qr_payment_and_switch_to_cod(): void
    {
        $user = \App\Models\User::factory()->create();

        $order = \App\Models\Order::create([
            'order_code' => 'ORD-TESTQR1',
            'user_id' => $user->id,
            'subtotal' => 100000,
            'shipping_fee' => 0,
            'discount_amount' => 0,
            'total_amount' => 100000,
            'order_status' => 'pending',
            'payment_status' => 'pending',
            'shipping_address' => [
                'recipient_name' => 'Nguyễn Văn Test',
                'phone' => '0912345678',
                'address' => '123 Đường 3/2, Ninh Kiều, Cần Thơ',
            ],
        ]);

        $order->payment()->create([
            'gateway' => 'payos',
            'transaction_id' => 'PAYOS_12345',
            'amount' => 100000,
            'status' => 'pending',
        ]);

        // 1. Khi thoát PayOS về cancel URL
        $cancelResponse = $this->actingAs($user)->get(route('orders.payment-cancel', $order));
        $cancelResponse->assertRedirect(route('orders.index', ['repay' => $order->id]));
        $cancelResponse->assertSessionHas('info');

        // 2. Kiểm tra trang danh sách đơn hàng có nút Tiếp tục QR và Đổi sang COD
        $indexResponse = $this->actingAs($user)->get(route('orders.index', ['repay' => $order->id]));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee(route('orders.repay-qr', $order));
        $indexResponse->assertSee(route('orders.switch-cod', $order));

        // 3. Người dùng bấm Đổi sang COD
        $switchResponse = $this->actingAs($user)->post(route('orders.switch-cod', $order));
        $switchResponse->assertRedirect(route('orders.index'));
        $switchResponse->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('cod', $order->payment->gateway);
        $this->assertEquals('pending', $order->payment_status);
    }
}

