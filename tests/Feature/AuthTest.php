<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\JWTGuard;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function jwtGuard(): JWTGuard
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');

        return $guard;
    }

    /**
     * Test successful user registration.
     */
    public function test_register_with_valid_data(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email', 'role'],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'role' => 'customer',
        ]);
    }

    /**
     * Test registration with invalid data.
     */
    public function test_register_with_invalid_email(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors']);
    }

    /**
     * Test registration with password mismatch.
     */
    public function test_register_with_password_mismatch(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'DifferentPassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors']);
    }

    /**
     * Test registration with duplicate email.
     */
    public function test_register_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'john@example.com']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors']);
    }

    /**
     * Test successful login with valid credentials.
     */
    public function test_login_with_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('Password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'Password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'access_token',
                'token_type',
                'expires_in',
            ]);
    }

    /**
     * Test login with invalid credentials.
     */
    public function test_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('CorrectPassword'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'WrongPassword',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Invalid credentials']);
    }

    /**
     * Test login with non-existent email.
     */
    public function test_login_with_non_existent_email(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'Password123',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Invalid credentials']);
    }

    /**
     * Test protected route access with valid token.
     */
    public function test_protected_route_with_valid_token(): void
    {
        $user = User::factory()->create();
        $token = $this->jwtGuard()->login($user);

        $response = $this->getJson('/api/auth/me', [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['user' => ['id', 'email', 'name']]);
    }

    /**
     * Test protected route access without token.
     */
    public function test_protected_route_without_token(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401);
    }

    /**
     * Test protected route access with invalid token.
     */
    public function test_protected_route_with_invalid_token(): void
    {
        $response = $this->getJson('/api/auth/me', [
            'Authorization' => 'Bearer invalid-token',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test token refresh.
     */
    public function test_token_refresh(): void
    {
        $user = User::factory()->create();
        $token = $this->jwtGuard()->login($user);

        $response = $this->postJson('/api/auth/refresh', [], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'access_token',
                'token_type',
                'expires_in',
            ]);
    }

    /**
     * Test logout.
     */
    public function test_logout(): void
    {
        $user = User::factory()->create();
        $token = $this->jwtGuard()->login($user);

        $response = $this->postJson('/api/auth/logout', [], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'User logged out successfully']);
    }

    /**
     * Test admin route rejects customer users.
     */
    public function test_admin_route_rejects_customer_user(): void
    {
        $user = User::factory()->create([
            'role' => Role::CUSTOMER,
        ]);
        $token = $this->jwtGuard()->login($user);

        $response = $this->getJson('/api/admin/test', [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test admin route allows admin users.
     */
    public function test_admin_route_allows_admin_user(): void
    {
        $user = User::factory()->create([
            'role' => Role::ADMIN,
        ]);
        $token = $this->jwtGuard()->login($user);

        $response = $this->getJson('/api/admin/test', [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200)
            ->assertJson(['ok' => true]);
    }
}


