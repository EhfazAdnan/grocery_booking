<?php

namespace Tests\Feature;

use App\Models\GroceryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\JWTGuard;

class ErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    protected function jwtGuard(): JWTGuard
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');

        return $guard;
    }

    /**
     * Task 41 & 42: Global exception handler and consistent error response format.
     */

    // AuthenticationException → 401
    public function test_unauthenticated_request_returns_401_with_consistent_format(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated. Please provide a valid token.',
            ]);
    }

    public function test_invalid_token_returns_401(): void
    {
        $response = $this->getJson('/api/auth/me', [
            'Authorization' => 'Bearer invalid-token-here',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
            ]);
    }

    // AuthorizationException → 403
    public function test_forbidden_request_returns_403_with_consistent_format(): void
    {
        $customer = User::factory()->customer()->create();
        $token = $this->jwtGuard()->fromUser($customer);

        $response = $this->getJson('/api/admin/grocery-items', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Forbidden. You do not have permission to access this resource.',
            ]);
    }

    // ValidationException → 422
    public function test_validation_error_returns_422_with_errors(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
            ])
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed.',
            ]);
    }

    public function test_validation_errors_contain_field_details(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => '',
            'email' => 'invalid',
            'password' => 'short',
        ]);

        $response->assertStatus(422);

        $errors = $response->json('errors');
        $this->assertArrayHasKey('name', $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('password', $errors);
    }

    // ModelNotFoundException → 404
    public function test_model_not_found_returns_404_with_consistent_format(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $this->jwtGuard()->fromUser($admin);

        $response = $this->getJson('/api/admin/grocery-items/99999', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(404)
            ->assertJsonStructure([
                'success',
                'message',
            ])
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_model_not_found_includes_model_name(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $this->jwtGuard()->fromUser($admin);

        $response = $this->getJson('/api/admin/grocery-items/99999', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'GroceryItem not found.']);
    }

    // InsufficientStockException → 422
    public function test_insufficient_stock_returns_422(): void
    {
        $customer = User::factory()->customer()->create();
        $token = $this->jwtGuard()->fromUser($customer);

        $item = GroceryItem::factory()->create(['stock' => 2]);

        $response = $this->postJson('/api/customer/orders', [
            'items' => [
                ['product_id' => $item->id, 'quantity' => 5],
            ],
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
            ])
            ->assertJson([
                'success' => false,
            ]);
    }

    // No sensitive data in error messages
    public function test_error_messages_do_not_leak_sensitive_data(): void
    {
        // Validation error should not expose internal details
        $response = $this->postJson('/api/auth/register', [
            'email' => 'invalid',
        ]);

        $response->assertStatus(422);

        $content = $response->getContent();
        $this->assertStringNotContainsString('SQL', $content);
        $this->assertStringNotContainsString('database', strtolower($content));
    }

    public function test_not_found_route_returns_404(): void
    {
        $response = $this->getJson('/api/nonexistent-route');

        $response->assertStatus(404);
    }

    // Verify all error responses have success=false
    public function test_all_error_responses_include_success_false(): void
    {
        // 401
        $this->getJson('/api/auth/me')
            ->assertJson(['success' => false]);

        // 403
        $customer = User::factory()->customer()->create();
        $token = $this->jwtGuard()->fromUser($customer);
        $this->getJson('/api/admin/grocery-items', [
            'Authorization' => 'Bearer '.$token,
        ])->assertJson(['success' => false]);

        // 422
        $this->postJson('/api/auth/register', [])
            ->assertJson(['success' => false]);

        // 404
        $admin = User::factory()->admin()->create();
        $adminToken = $this->jwtGuard()->fromUser($admin);
        $this->getJson('/api/admin/grocery-items/99999', [
            'Authorization' => 'Bearer '.$adminToken,
        ])->assertJson(['success' => false]);
    }
}
