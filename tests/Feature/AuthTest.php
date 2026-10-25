<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_successfully(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Nguyễn Văn Test',
            'email' => 'testuser@example.com',
            'phone' => '0988776655',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'testuser@example.com',
            'role' => User::ROLE_CUSTOMER,
            'is_active' => true,
        ]);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->customer()->create([
            'email' => 'customer_login@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->post(route('login.store'), [
            'email' => 'customer_login@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'valid@example.com',
            'password' => bcrypt('correct_password'),
        ]);

        $response = $this->from(route('login'))->post(route('login.store'), [
            'email' => 'valid@example.com',
            'password' => 'wrong_password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_locked_user_is_prevented_from_login(): void
    {
        User::factory()->inactive()->create([
            'email' => 'locked@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->from(route('login'))->post(route('login.store'), [
            'email' => 'locked@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_update_profile(): void
    {
        $user = User::factory()->customer()->create([
            'name' => 'Tên Ban Đầu',
            'phone' => '0911111111',
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Tên Mới Sau Cập Nhật',
            'phone' => '0999999999',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Tên Mới Sau Cập Nhật',
            'phone' => '0999999999',
        ]);
    }

    public function test_user_can_logout_safely(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
