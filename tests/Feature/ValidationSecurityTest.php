<?php

namespace Tests\Feature;

use App\Models\GroceryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\JWTGuard;

class ValidationSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function jwtGuard(): JWTGuard
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');

        return $guard;
    }

    // Task 44: Input Validation Rules.

    // Password rules: min 8 chars, uppercase, number
    public function test_password_must_contain_uppercase_letter(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_password_must_contain_number(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'PasswordABC',
            'password_confirmation' => 'PasswordABC',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_password_must_be_at_least_8_chars(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Pass1',
            'password_confirmation' => 'Pass1',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_valid_password_with_uppercase_and_number_passes(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $response->assertStatus(201);
    }

    // Price validation: decimal 2 places, > 0
    public function test_price_must_be_greater_than_zero(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $this->jwtGuard()->fromUser($admin);

        $response = $this->postJson('/api/admin/grocery-items', [
            'name' => 'Test Product',
            'price' => 0,
            'stock' => 10,
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    public function test_price_must_be_numeric(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $this->jwtGuard()->fromUser($admin);

        $response = $this->postJson('/api/admin/grocery-items', [
            'name' => 'Test Product',
            'price' => 'not-a-number',
            'stock' => 10,
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    public function test_valid_price_passes_validation(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $this->jwtGuard()->fromUser($admin);

        $response = $this->postJson('/api/admin/grocery-items', [
            'name' => 'Test Product',
            'price' => 99.99,
            'stock' => 10,
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.price', '99.99');
    }

    // Quantity validation: integer > 0
    public function test_quantity_must_be_integer(): void
    {
        $customer = User::factory()->customer()->create();
        $token = $this->jwtGuard()->fromUser($customer);

        $item = GroceryItem::factory()->create(['stock' => 10]);

        $response = $this->postJson('/api/customer/orders', [
            'items' => [
                ['product_id' => $item->id, 'quantity' => 1.5],
            ],
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.quantity']);
    }

    // Task 45: SQL Injection Prevention.
    public function test_sql_injection_in_search_is_safe(): void
    {
        GroceryItem::factory()->create(['name' => 'Apple', 'is_active' => true]);

        // Attempt SQL injection via search parameter
        $response = $this->getJson('/api/customer/products?search=\' OR 1=1 --');

        $response->assertOk();
        // Should return empty results, not all products
        $this->assertCount(0, $response->json('data'));
    }

    public function test_sql_injection_in_product_name_is_safe(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $this->jwtGuard()->fromUser($admin);

        // Attempt SQL injection via product name
        $response = $this->postJson('/api/admin/grocery-items', [
            'name' => 'Product\'; DROP TABLE grocery_items; --',
            'price' => 10,
            'stock' => 5,
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        // Should either succeed (with escaped name) or fail validation, but not execute SQL
        $this->assertTrue(in_array($response->status(), [201, 422]));

        // Verify the table still exists by creating another product
        $response2 = $this->postJson('/api/admin/grocery-items', [
            'name' => 'Safe Product',
            'price' => 10,
            'stock' => 5,
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response2->assertStatus(201);
    }

    public function test_sql_injection_in_order_product_id_is_safe(): void
    {
        $customer = User::factory()->customer()->create();
        $token = $this->jwtGuard()->fromUser($customer);

        // Attempt SQL injection via product_id
        $response = $this->postJson('/api/customer/orders', [
            'items' => [
                ['product_id' => '1 OR 1=1', 'quantity' => 1],
            ],
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        // Should fail validation (product_id must be integer), not execute SQL
        $response->assertStatus(422);
    }

    // Task 47: Rate Limiting.
    public function test_auth_endpoints_are_rate_limited(): void
    {
        // Make 5 requests (the limit)
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/auth/login', [
                'email' => "test{$i}@example.com",
                'password' => 'Password123',
            ]);
        }

        // The 6th request should be rate limited
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test6@example.com',
            'password' => 'Password123',
        ]);

        $response->assertStatus(429);
    }

    public function test_rate_limit_includes_retry_after_header(): void
    {
        // Make 5 requests (the limit)
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/auth/login', [
                'email' => "test{$i}@example.com",
                'password' => 'Password123',
            ]);
        }

        // The 6th request should be rate limited with retry-after header
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test6@example.com',
            'password' => 'Password123',
        ]);

        $response->assertStatus(429);
        $this->assertNotNull($response->headers->get('Retry-After'));
    }

    public function test_order_placement_is_rate_limited(): void
    {
        $customer = User::factory()->customer()->create();
        $token = $this->jwtGuard()->fromUser($customer);

        $item = GroceryItem::factory()->create(['stock' => 100]);

        // Make 10 requests (the limit)
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/customer/orders', [
                'items' => [
                    ['product_id' => $item->id, 'quantity' => 1],
                ],
            ], [
                'Authorization' => 'Bearer '.$token,
            ]);
        }

        // The 11th request should be rate limited
        $response = $this->postJson('/api/customer/orders', [
            'items' => [
                ['product_id' => $item->id, 'quantity' => 1],
            ],
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(429);
    }

    // Task 46: CSRF Protection.
    public function test_logout_form_includes_csrf_token(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $response = $this->get('/admin/products');

        $response->assertStatus(200);
        $response->assertSee('_token');
    }

    public function test_web_routes_require_csrf_token(): void
    {
        // POST to logout without CSRF token should fail
        $response = $this->post('/logout');

        // Should get 419 (CSRF token mismatch) or redirect
        $this->assertTrue(in_array($response->status(), [419, 302]));
    }
}
