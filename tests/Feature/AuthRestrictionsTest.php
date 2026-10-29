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
}
