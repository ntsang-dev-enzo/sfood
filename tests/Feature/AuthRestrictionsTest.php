<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthRestrictionsTest extends TestCase
{
    use RefreshDatabase;
    public function test_guest_can_access_login_and_register(): void
    {
        $resLogin = $this->get('/dang-nhap');
        $resLogin->assertStatus(200);

        $resRegister = $this->get('/dang-ky');
        $resRegister->assertStatus(200);
    }

    public function test_logged_in_user_cannot_access_register(): void
    {
        // Mock a user
        $user = new User([
            'id' => 99999,
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($user)->get('/dang-ky');
        $response->assertStatus(302);
        $response->assertRedirect(route('home'));
    }

    public function test_logged_in_user_cannot_access_login(): void
    {
        $user = new User([
            'id' => 99999,
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($user)->get('/dang-nhap');
        $response->assertStatus(302);
        $response->assertRedirect(route('home'));
    }

    public function test_non_existent_account_cannot_login(): void
    {
        $response = $this->post('/dang-nhap', [
            'email' => 'khongtontai@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'email' => 'Tài khoản chưa tồn tại trên hệ thống. Vui lòng đăng ký tài khoản mới trước khi đăng nhập.',
        ]);
    }

    public function test_existing_account_with_wrong_password_shows_password_error(): void
    {
        $user = User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => Hash::make('correct_password'),
            'role' => 'user',
        ]);

        $response = $this->post('/dang-nhap', [
            'email' => 'existing@example.com',
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['password']);
    }

    public function test_user_can_register_with_unique_phone_number(): void
    {
        $response = $this->post('/dang-ky', [
            'name' => 'Nguyễn Văn Đăng Ký',
            'email' => 'dangky@example.com',
            'phone' => '0939111222',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('home'));

        $this->assertDatabaseHas('users', [
            'email' => 'dangky@example.com',
            'phone' => '0939111222',
        ]);
        $this->assertAuthenticated();
    }

    public function test_user_cannot_register_with_duplicate_phone_number(): void
    {
        User::create([
            'name' => 'Người Dùng 1',
            'email' => 'user1@example.com',
            'phone' => '0988777666',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $response = $this->post('/dang-ky', [
            'name' => 'Người Dùng 2',
            'email' => 'user2@example.com',
            'phone' => '0988777666', // Trùng số điện thoại
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['phone']);
        $this->assertDatabaseMissing('users', [
            'email' => 'user2@example.com',
        ]);
    }

    public function test_user_can_login_using_phone_number_and_password(): void
    {
        User::create([
            'name' => 'Đăng Nhập Phone',
            'email' => 'phoneuser@example.com',
            'phone' => '0912999888',
            'password' => Hash::make('secret123'),
            'role' => 'user',
        ]);

        $response = $this->post('/dang-nhap', [
            'email' => '0912999888', // Nhập số điện thoại vào ô đăng nhập
            'password' => 'secret123',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();
    }

    public function test_user_can_login_using_formatted_phone_number(): void
    {
        User::create([
            'name' => 'Đăng Nhập Phone Cách',
            'email' => 'phoneuser2@example.com',
            'phone' => '0912333444',
            'password' => Hash::make('secret123'),
            'role' => 'user',
        ]);

        // Người dùng nhập có khoảng trắng hoặc dấu gạch nối
        $response = $this->post('/dang-nhap', [
            'email' => '0912 333 444',
            'password' => 'secret123',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();
    }

    public function test_shipping_address_phone_does_not_affect_user_registration(): void
    {
        // Có một địa chỉ giao hàng sử dụng số điện thoại 0977112233
        \App\Models\ShippingAddress::create([
            'user_id' => User::factory()->create()->id,
            'recipient_name' => 'Người Nhận Hộ',
            'phone' => '0977112233',
            'address_line' => '123 Đường ABC',
            'city' => 'Cần Thơ',
        ]);

        // Người dùng mới hoàn toàn có thể đăng ký tài khoản với số điện thoại này
        $response = $this->post('/dang-ky', [
            'name' => 'Chủ Tài Khoản Mới',
            'email' => 'chutaikhoan@example.com',
            'phone' => '0977112233',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('home'));
        $this->assertDatabaseHas('users', [
            'email' => 'chutaikhoan@example.com',
            'phone' => '0977112233',
        ]);
    }
}
