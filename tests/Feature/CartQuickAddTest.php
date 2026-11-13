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

    public function test_cart_index_renders_view_with_empty_state_when_empty(): void
    {
        $response = $this->get(route('cart.index'));
        $response->assertStatus(200);
        $response->assertSee('Giỏ hàng của bạn đang trống');
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

    public function test_user_can_reorder_delivered_order(): void
    {
        $user = \App\Models\User::factory()->create();

        $category = Category::create([
            'name' => 'Trái Cây Mới',
            'slug' => 'trai-cay-moi',
            'is_active' => true,
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Táo Envy',
            'slug' => 'tao-envy',
            'is_active' => true,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => '1kg',
            'sku' => 'TE-1',
            'price' => 120000,
            'stock' => 20,
            'is_active' => true,
        ]);

        $order = \App\Models\Order::create([
            'order_code' => 'ORD-DELIVERED1',
            'user_id' => $user->id,
            'subtotal' => 120000,
            'shipping_fee' => 0,
            'discount_amount' => 0,
            'total_amount' => 120000,
            'order_status' => 'delivered',
            'payment_status' => 'paid',
            'shipping_address' => [
                'recipient_name' => 'Nguyễn Mua Lại',
                'phone' => '0912345678',
                'address' => 'Cần Thơ',
            ],
        ]);

        $order->items()->create([
            'product_variant_id' => $variant->id,
            'product_name' => $product->name,
            'variant_name' => $variant->name,
            'unit_price' => 120000,
            'quantity' => 2,
            'subtotal' => 240000,
        ]);

        $response = $this->actingAs($user)->post(route('orders.reorder', $order));
        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);
    }

    public function test_payos_checkout_preserves_cart_until_switched_to_cod(): void
    {
        $user = \App\Models\User::factory()->create();

        $category = Category::create([
            'name' => 'Rau Ăn Lá',
            'slug' => 'rau-an-la',
            'is_active' => true,
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Cải Bó Xôi',
            'slug' => 'cai-bo-xoi',
            'is_active' => true,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => '500g',
            'sku' => 'CBX-1',
            'price' => 35000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($user)->post(route('products.add-to-cart', $product), [
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        // Đặt hàng chọn PayOS
        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'shipping_address_id' => '',
            'recipient_name' => 'Nguyễn Văn QR',
            'phone' => '0911223344',
            'address_line' => '123 Đường 30/4',
            'city' => 'TP Cần Thơ',
            'payment_gateway' => 'payos',
        ]);

        // Giỏ hàng vẫn giữ nguyên
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
        ]);

        $order = \App\Models\Order::where('user_id', $user->id)->first();
        $this->assertNotNull($order);

        // Khi người dùng bấm đổi sang COD
        $this->actingAs($user)->post(route('orders.switch-cod', $order));

        // Giỏ hàng sau khi đổi sang COD thành công thì mới xóa
        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
        ]);
    }

    public function test_user_can_update_order_shipping_address_only_once(): void
    {
        $user = \App\Models\User::factory()->create();

        $order = \App\Models\Order::create([
            'order_code' => 'ORD-ADDR-TEST',
            'user_id' => $user->id,
            'subtotal' => 100000,
            'shipping_fee' => 0,
            'discount_amount' => 0,
            'total_amount' => 100000,
            'order_status' => 'pending',
            'payment_status' => 'pending',
            'shipping_address' => [
                'recipient_name' => 'Nguyễn Văn A',
                'phone' => '0912345678',
                'address' => '123 Đường 30/4, Cần Thơ',
            ],
        ]);

        $this->assertTrue($order->canUpdateAddress());

        // Lần cập nhật đầu tiên -> thành công
        $response = $this->actingAs($user)->post(route('orders.update-address', $order), [
            'recipient_name' => 'Nguyễn Văn B',
            'phone' => '0987654321',
            'address' => '456 Đường 3/2, Cần Thơ',
        ]);

        $response->assertRedirect(route('orders.index'));
        $response->assertSessionHas('address_updated_popup');

        $order->refresh();
        $this->assertEquals('Nguyễn Văn B', $order->shipping_address['recipient_name']);
        $this->assertEquals('0987654321', $order->shipping_address['phone']);
        $this->assertEquals('456 Đường 3/2, Cần Thơ', $order->shipping_address['address']);
        $this->assertTrue($order->shipping_address['address_updated']);
        $this->assertFalse($order->canUpdateAddress());

        // Lần cập nhật thứ 2 -> bị từ chối
        $response2 = $this->actingAs($user)->post(route('orders.update-address', $order), [
            'recipient_name' => 'Nguyễn Văn C',
            'phone' => '0999999999',
            'address' => '789 Đường Hòa Bình, Cần Thơ',
        ]);

        $response2->assertSessionHas('error');
        $order->refresh();
        $this->assertEquals('Nguyễn Văn B', $order->shipping_address['recipient_name']);

        // Kiểm tra trang đơn hàng hiển thị huy hiệu 'Đã cập nhật' và ẩn nút 'Đổi địa chỉ'
        $indexResponse = $this->actingAs($user)->get(route('orders.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Đã cập nhật');
        $indexResponse->assertDontSee('data-action-url="' . route('orders.update-address', $order) . '"', false);
    }

    public function test_user_can_update_order_shipping_address_with_saved_address(): void
    {
        $user = \App\Models\User::factory()->create();

        $savedAddr = \App\Models\ShippingAddress::create([
            'user_id' => $user->id,
            'recipient_name' => 'Lê Thị Lưu Sẵn',
            'phone' => '0911223344',
            'address_line' => '789 Đường 30 Tháng 4',
            'ward' => 'Phường Hưng Lợi',
            'district' => 'Quận Ninh Kiều',
            'city' => 'Cần Thơ',
            'is_default' => true,
        ]);

        $order = \App\Models\Order::create([
            'order_code' => 'ORD-SAVED-ADDR',
            'user_id' => $user->id,
            'subtotal' => 100000,
            'shipping_fee' => 0,
            'discount_amount' => 0,
            'total_amount' => 100000,
            'order_status' => 'pending',
            'payment_status' => 'pending',
            'shipping_address' => [
                'recipient_name' => 'Người Cũ',
                'phone' => '0900000000',
                'address' => 'Địa chỉ cũ',
            ],
        ]);

        $response = $this->actingAs($user)->post(route('orders.update-address', $order), [
            'shipping_address_id' => $savedAddr->id,
        ]);

        $response->assertRedirect(route('orders.index'));
        $order->refresh();
        $this->assertEquals('Lê Thị Lưu Sẵn', $order->shipping_address['recipient_name']);
        $this->assertEquals('0911223344', $order->shipping_address['phone']);
        $this->assertStringContainsString('789 Đường 30 Tháng 4', $order->shipping_address['address']);
        $this->assertTrue($order->shipping_address['address_updated']);
    }

    public function test_user_can_update_order_shipping_address_with_manual_city_district(): void
    {
        $user = \App\Models\User::factory()->create();

        $order = \App\Models\Order::create([
            'order_code' => 'ORD-MANUAL-ADDR',
            'user_id' => $user->id,
            'subtotal' => 100000,
            'shipping_fee' => 0,
            'discount_amount' => 0,
            'total_amount' => 100000,
            'order_status' => 'pending',
            'payment_status' => 'pending',
            'shipping_address' => [
                'recipient_name' => 'Người Cũ',
                'phone' => '0900000000',
                'address' => 'Địa chỉ cũ',
            ],
        ]);

        $response = $this->actingAs($user)->post(route('orders.update-address', $order), [
            'recipient_name' => 'Phạm Văn Mới',
            'phone' => '0988112233',
            'address_line' => 'Số 10 Lý Tự Trọng',
            'ward' => 'Phường An Cư',
            'district' => 'Quận Ninh Kiều',
            'city' => 'Thành phố Cần Thơ',
        ]);

        $response->assertRedirect(route('orders.index'));
        $order->refresh();
        $this->assertEquals('Phạm Văn Mới', $order->shipping_address['recipient_name']);
        $this->assertEquals('0988112233', $order->shipping_address['phone']);
        $this->assertEquals('Số 10 Lý Tự Trọng, Phường An Cư, Quận Ninh Kiều, Thành phố Cần Thơ', $order->shipping_address['address']);
        $this->assertTrue($order->shipping_address['address_updated']);
    }

    public function test_user_can_switch_payment_to_cod_only_once(): void
    {
        $user = \App\Models\User::factory()->create();

        $order = \App\Models\Order::create([
            'order_code' => 'ORD-SWITCH-TEST',
            'user_id' => $user->id,
            'subtotal' => 150000,
            'shipping_fee' => 0,
            'discount_amount' => 0,
            'total_amount' => 150000,
            'order_status' => 'pending',
            'payment_status' => 'pending',
            'shipping_address' => [
                'recipient_name' => 'Trần Thị C',
                'phone' => '0901234567',
                'address' => '100 Mậu Thân, Cần Thơ',
            ],
        ]);

        $order->payment()->create([
            'gateway' => 'payos',
            'status' => 'pending',
            'amount' => 150000,
            'transaction_id' => 'PAYOS_' . $order->order_code,
        ]);

        $this->assertTrue($order->canSwitchPayment());

        // Lần đổi sang COD đầu tiên -> thành công
        $response = $this->actingAs($user)->post(route('orders.switch-cod', $order));
        $response->assertRedirect(route('orders.index'));
        $response->assertSessionHas('payment_switched_popup');

        $order->refresh();
        $this->assertEquals('cod', $order->payment->gateway);
        $this->assertTrue($order->shipping_address['payment_switched']);
        $this->assertFalse($order->canSwitchPayment());

        // Lần đổi tiếp theo -> bị từ chối
        $response2 = $this->actingAs($user)->post(route('orders.switch-cod', $order));
        $response2->assertSessionHas('error');

        // Kiểm tra view: nút đổi sang COD đã bị ẩn
        $indexResponse = $this->actingAs($user)->get(route('orders.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertDontSee('switch-cod-form-' . $order->id);
    }

    public function test_updating_shipping_address_to_outside_can_tho_recalculates_and_adds_shipping_fee(): void
    {
        $user = \App\Models\User::factory()->create();

        $order = \App\Models\Order::create([
            'order_code' => 'ORD-SHIP-TEST1',
            'user_id' => $user->id,
            'subtotal' => 200000,
            'shipping_fee' => 0,
            'discount_amount' => 0,
            'total_amount' => 200000,
            'order_status' => 'pending',
            'payment_status' => 'pending',
            'shipping_address' => [
                'recipient_name' => 'Nguyễn Cần Thơ',
                'phone' => '0912345678',
                'city' => 'Thành phố Cần Thơ',
                'district' => 'Quận Ninh Kiều',
                'address' => '123 Đường 30/4, Quận Ninh Kiều, Cần Thơ',
            ],
        ]);

        $order->payment()->create([
            'gateway' => 'cod',
            'status' => 'pending',
            'amount' => 200000,
            'transaction_id' => 'COD_' . $order->order_code,
        ]);

        // Đổi sang TP. Hồ Chí Minh (ngoài Ninh Kiều / Cái Răng) -> cộng 30.000đ tiền ship
        $response = $this->actingAs($user)->post(route('orders.update-address', $order), [
            'recipient_name' => 'Nguyễn Sài Gòn',
            'phone' => '0987654321',
            'address_line' => '123 Nguyễn Thị Minh Khai',
            'ward' => 'Phường Bến Nghé',
            'district' => 'Quận 1',
            'city' => 'Thành phố Hồ Chí Minh',
        ]);

        $response->assertRedirect(route('orders.index'));
        $response->assertSessionHas('address_updated_popup');

        $order->refresh();
        $this->assertEquals(30000, (float) $order->shipping_fee);
        $this->assertEquals(230000, (float) $order->total_amount);
        $this->assertEquals(230000, (float) $order->payment->amount);
        $this->assertStringContainsString('Thành phố Hồ Chí Minh', $order->shipping_address['address']);
    }

    public function test_updating_shipping_address_to_inner_can_tho_sets_shipping_fee_free(): void
    {
        $user = \App\Models\User::factory()->create();

        $order = \App\Models\Order::create([
            'order_code' => 'ORD-SHIP-TEST2',
            'user_id' => $user->id,
            'subtotal' => 200000,
            'shipping_fee' => 30000,
            'discount_amount' => 0,
            'total_amount' => 230000,
            'order_status' => 'pending',
            'payment_status' => 'pending',
            'shipping_address' => [
                'recipient_name' => 'Nguyễn Hà Nội',
                'phone' => '0912345678',
                'city' => 'Hà Nội',
                'district' => 'Quận Cầu Giấy',
                'address' => '123 Cầu Giấy, Hà Nội',
            ],
        ]);

        $order->payment()->create([
            'gateway' => 'cod',
            'status' => 'pending',
            'amount' => 230000,
            'transaction_id' => 'COD_' . $order->order_code,
        ]);

        // Đổi sang Quận Cái Răng (Cần Thơ) -> phí ship về 0đ (miễn phí)
        $response = $this->actingAs($user)->post(route('orders.update-address', $order), [
            'recipient_name' => 'Nguyễn Cái Răng',
            'phone' => '0987654321',
            'address_line' => '456 Đường Phạm Hùng',
            'ward' => 'Phường Lê Bình',
            'district' => 'Quận Cái Răng',
            'city' => 'Thành phố Cần Thơ',
        ]);

        $response->assertRedirect(route('orders.index'));
        $order->refresh();
        $this->assertEquals(0, (float) $order->shipping_fee);
        $this->assertEquals(200000, (float) $order->total_amount);
        $this->assertEquals(200000, (float) $order->payment->amount);
    }

    public function test_order_over_1m_remains_free_shipping_even_when_changing_city(): void
    {
        $user = \App\Models\User::factory()->create();

        $order = \App\Models\Order::create([
            'order_code' => 'ORD-SHIP-TEST3',
            'user_id' => $user->id,
            'subtotal' => 1200000,
            'shipping_fee' => 0,
            'discount_amount' => 0,
            'total_amount' => 1200000,
            'order_status' => 'pending',
            'payment_status' => 'pending',
            'shipping_address' => [
                'recipient_name' => 'Khách VIP',
                'phone' => '0912345678',
                'city' => 'Cần Thơ',
                'district' => 'Ninh Kiều',
                'address' => 'Ninh Kiều, Cần Thơ',
            ],
        ]);

        $order->payment()->create([
            'gateway' => 'cod',
            'status' => 'pending',
            'amount' => 1200000,
            'transaction_id' => 'COD_' . $order->order_code,
        ]);

        // Đơn >= 1.000.000đ khi đổi sang tỉnh thành khác vẫn được miễn phí ship
        $response = $this->actingAs($user)->post(route('orders.update-address', $order), [
            'recipient_name' => 'Khách VIP',
            'phone' => '0912345678',
            'address_line' => '100 Lê Duẩn',
            'ward' => 'Phường Bến Nghé',
            'district' => 'Quận 1',
            'city' => 'Thành phố Hồ Chí Minh',
        ]);

        $response->assertRedirect(route('orders.index'));
        $order->refresh();
        $this->assertEquals(0, (float) $order->shipping_fee);
        $this->assertEquals(1200000, (float) $order->total_amount);
        $this->assertEquals(1200000, (float) $order->payment->amount);
    }

    public function test_updating_shipping_address_with_saved_address_outside_can_tho_adds_shipping_fee(): void
    {
        $user = \App\Models\User::factory()->create();

        $savedAddrHcm = \App\Models\ShippingAddress::create([
            'user_id' => $user->id,
            'recipient_name' => 'Trần Thị Sài Gòn',
            'phone' => '0933445566',
            'address_line' => '12 Nguyễn Huệ',
            'ward' => 'Phường Bến Nghé',
            'district' => 'Quận 1',
            'city' => 'Thành phố Hồ Chí Minh',
            'is_default' => false,
        ]);

        $order = \App\Models\Order::create([
            'order_code' => 'ORD-SHIP-SAVED',
            'user_id' => $user->id,
            'subtotal' => 250000,
            'shipping_fee' => 0,
            'discount_amount' => 0,
            'total_amount' => 250000,
            'order_status' => 'pending',
            'payment_status' => 'pending',
            'shipping_address' => [
                'recipient_name' => 'Người Ninh Kiều',
                'phone' => '0901112233',
                'city' => 'Cần Thơ',
                'district' => 'Ninh Kiều',
                'address' => '30/4 Ninh Kiều Cần Thơ',
            ],
        ]);

        $order->payment()->create([
            'gateway' => 'cod',
            'status' => 'pending',
            'amount' => 250000,
            'transaction_id' => 'COD_' . $order->order_code,
        ]);

        $response = $this->actingAs($user)->post(route('orders.update-address', $order), [
            'shipping_address_id' => $savedAddrHcm->id,
        ]);

        $response->assertRedirect(route('orders.index'));
        $order->refresh();
        $this->assertEquals(30000, (float) $order->shipping_fee);
        $this->assertEquals(280000, (float) $order->total_amount);
        $this->assertEquals(280000, (float) $order->payment->amount);
        $this->assertStringContainsString('Thành phố Hồ Chí Minh', $order->shipping_address['address']);
    }

    public function test_user_can_upload_avatar_and_access_it(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $user = \App\Models\User::factory()->create();

        $file = \Illuminate\Http\UploadedFile::fake()->create('my_avatar.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Nguyễn Có Avatar',
            'phone' => '0912345678',
            'avatar' => $file,
        ]);

        $response->assertSessionHas('success');
        $user->refresh();
        $this->assertNotNull($user->avatar);
        $this->assertStringStartsWith('avatars/', $user->avatar);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($user->avatar);
    }

    public function test_storage_fallback_route_serves_public_storage_files(): void
    {
        $testDir = storage_path('app/public');
        if (! is_dir($testDir)) {
            mkdir($testDir, 0755, true);
        }
        $filePath = $testDir . '/test_avatar_fallback.txt';
        file_put_contents($filePath, 'sfood storage fallback');

        $response = $this->get('/storage/test_avatar_fallback.txt');
        $response->assertStatus(200);

        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function test_build_fallback_route_serves_manifest(): void
    {
        $response = $this->get('/build/manifest.json');
        $response->assertStatus(200);
    }

    public function test_favicon_route_serves_icon(): void
    {
        $response = $this->get('/favicon.ico');
        $response->assertStatus(200);
        $this->assertEquals('image/x-icon', $response->headers->get('Content-Type'));
    }
}

