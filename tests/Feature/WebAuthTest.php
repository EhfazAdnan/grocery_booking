<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 18: Hybrid Session + JWT authentication.
 *
 * The API keeps authenticating with JWT (tymon/jwt-auth); the auth endpoints
 * additionally establish the web (session) guard so Blade pages (@auth,
 * auth()->user()) work server-side.
 */
class WebAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_pages_to_login(): void
    {
        $this->get('/admin/products')->assertRedirect(route('login'));
        $this->get('/admin/orders')->assertRedirect(route('login'));
        $this->get('/admin/analytics')->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_from_customer_booking_pages_to_login(): void
    {
        $this->get('/customer/orders')->assertRedirect(route('login'));
        $this->get('/customer/checkout')->assertRedirect(route('login'));
        $this->get('/customer/order-confirmation')->assertRedirect(route('login'));
    }

    public function test_guest_can_still_browse_public_pages(): void
    {
        $this->get('/')->assertStatus(200)->assertViewIs('welcome');
        $this->get('/customer/products')->assertStatus(200)->assertViewIs('customer.products');
        $this->get('/login')->assertStatus(200);
        $this->get('/register')->assertStatus(200);
    }

    public function test_api_login_establishes_web_session_for_blade(): void
    {
        User::factory()->admin()->create([
            'email' => 'admin@example.com',
            'password' => 'Password123',
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'Password123',
        ])->assertStatus(200)->assertJsonStructure(['access_token']);

        $this->assertTrue(auth('web')->check(), 'API login must establish the web (session) guard');
        $this->assertTrue(auth()->check(), 'Default guard must be the web (session) guard');

        // The same client can now load protected Blade pages (session cookie).
        $this->get('/admin/products')->assertStatus(200)->assertViewIs('admin.products');
    }

    public function test_customer_can_access_booking_pages_after_api_login(): void
    {
        User::factory()->customer()->create([
            'email' => 'customer@example.com',
            'password' => 'Password123',
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'customer@example.com',
            'password' => 'Password123',
        ])->assertStatus(200);

        $this->get('/customer/orders')->assertStatus(200);
        $this->get('/customer/checkout')->assertStatus(200);
    }

    public function test_customer_cannot_open_admin_pages_after_api_login(): void
    {
        User::factory()->customer()->create([
            'email' => 'customer@example.com',
            'password' => 'Password123',
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'customer@example.com',
            'password' => 'Password123',
        ])->assertStatus(200);

        $this->get('/admin/products')->assertRedirect('/customer/products');
    }

    public function test_session_does_not_authorize_api_endpoints(): void
    {
        // A browser context with an active Blade (session) auth and no JWT:
        // exactly what a real browser holds after login (session cookie +
        // localStorage token, but no Authorization header on raw page loads).
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin); // web (session) guard only

        // The session cookie alone must NOT grant API access — JWT is required.
        $this->getJson('/api/admin/grocery-items')->assertStatus(401);
        $this->getJson('/api/auth/me')->assertStatus(401);
    }

    public function test_register_auto_login_establishes_session(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ])->assertStatus(201);

        $this->assertTrue(auth('web')->check(), 'Registration must auto-login on the web guard');
        $this->get('/customer/orders')->assertStatus(200);
    }

    public function test_api_logout_ends_session_and_blade_auth(): void
    {
        User::factory()->admin()->create([
            'email' => 'admin@example.com',
            'password' => 'Password123',
        ]);

        $token = $this->postJson('/api/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'Password123',
        ])->assertStatus(200)->json('access_token');

        $this->assertTrue(auth('web')->check());

        $this->postJson('/api/auth/logout', [], [
            'Authorization' => 'Bearer '.$token,
        ])->assertStatus(200);

        $this->assertFalse(auth('web')->check(), 'Logout must end the web (session) guard');
        $this->get('/admin/products')->assertRedirect(route('login'));
    }

    public function test_web_logout_route_ends_session(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $this->post('/logout', ['_token' => csrf_token()])->assertRedirect('/login');
        $this->assertFalse(auth('web')->check());
        $this->get('/admin/products')->assertRedirect(route('login'));
    }

    public function test_blade_renders_authenticated_user_server_side(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Server Rendered']);
        $this->actingAs($admin);

        $response = $this->get('/admin/products');

        $response->assertStatus(200);
        $response->assertSee('Server Rendered', false);
    }
}