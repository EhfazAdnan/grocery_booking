<?php

namespace Tests\Feature;

use App\Models\GroceryItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\JWTGuard;

class ApiContractTest extends TestCase
{
    use RefreshDatabase;

    protected function jwtGuard(): JWTGuard
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');

        return $guard;
    }

    protected function adminToken(): string
    {
        $admin = User::factory()->admin()->create();

        return $this->jwtGuard()->fromUser($admin);
    }

    protected function customerToken(): string
    {
        $customer = User::factory()->customer()->create();

        return $this->jwtGuard()->fromUser($customer);
    }

    /**
     * Task 40: Verify all endpoints return correct status codes.
     */
    public function test_register_endpoint_status_codes(): void
    {
        // Valid registration → 201
        $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ])->assertStatus(201);

        // Invalid registration → 422
        $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ])->assertStatus(422);
    }

    public function test_login_endpoint_status_codes(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('Password123'),
        ]);

        // Valid login → 200
        $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'Password123',
        ])->assertStatus(200);

        // Invalid login → 401
        $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'WrongPassword123',
        ])->assertStatus(401);
    }

    public function test_customer_products_endpoint_status_code(): void
    {
        GroceryItem::factory()->count(3)->create();

        $this->getJson('/api/customer/products')->assertStatus(200);
    }

    public function test_protected_endpoints_require_authentication(): void
    {
        $this->getJson('/api/auth/me')->assertStatus(401);
        $this->getJson('/api/customer/orders')->assertStatus(401);
        $this->getJson('/api/customer/profile')->assertStatus(401);
        $this->getJson('/api/admin/grocery-items')->assertStatus(401);
        $this->getJson('/api/admin/orders')->assertStatus(401);
        $this->getJson('/api/admin/analytics/revenue')->assertStatus(401);
    }

    public function test_admin_endpoints_reject_customer_role(): void
    {
        $this->getJson('/api/admin/grocery-items', [
            'Authorization' => 'Bearer '.$this->customerToken(),
        ])->assertStatus(403);

        $this->getJson('/api/admin/orders', [
            'Authorization' => 'Bearer '.$this->customerToken(),
        ])->assertStatus(403);

        $this->getJson('/api/admin/analytics/revenue', [
            'Authorization' => 'Bearer '.$this->customerToken(),
        ])->assertStatus(403);
    }

    // Task 40: Test error response format consistency.
    public function test_validation_error_response_format(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => '',
            'email' => 'invalid',
            'password' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors']);
    }

    public function test_authentication_error_response_format(): void
    {
        $this->getJson('/api/auth/me')->assertStatus(401);
    }

    public function test_authorization_error_response_format(): void
    {
        $response = $this->getJson('/api/admin/grocery-items', [
            'Authorization' => 'Bearer '.$this->customerToken(),
        ]);

        $response->assertStatus(403);
    }

    public function test_not_found_error_response_format(): void
    {
        // Non-existent grocery item
        $response = $this->getJson('/api/admin/grocery-items/99999', [
            'Authorization' => 'Bearer '.$this->adminToken(),
        ]);

        $response->assertStatus(404);
    }

    /**
     * Task 40: Test pagination metadata.
     */
    public function test_admin_grocery_items_pagination_metadata(): void
    {
        GroceryItem::factory()->count(20)->create();

        $response = $this->getJson('/api/admin/grocery-items?per_page=10&page=1', [
            'Authorization' => 'Bearer '.$this->adminToken(),
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'last_page', 'total'],
            ])
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.total', 20)
            ->assertJsonPath('meta.last_page', 2);
    }

    public function test_customer_products_pagination_metadata(): void
    {
        GroceryItem::factory()->count(20)->create(['is_active' => true]);

        $response = $this->getJson('/api/customer/products?per_page=10&page=2');

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'last_page', 'total'],
            ])
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.total', 20)
            ->assertJsonPath('meta.last_page', 2);
    }

    public function test_customer_orders_pagination_metadata(): void
    {
        $customer = User::factory()->customer()->create();
        Order::factory()->count(20)->create(['user_id' => $customer->id]);

        $response = $this->getJson('/api/customer/orders?page=2', [
            'Authorization' => 'Bearer '.$this->jwtGuard()->fromUser($customer),
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'last_page', 'total'],
            ])
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.total', 20)
            ->assertJsonPath('meta.last_page', 2);
    }

    public function test_admin_orders_pagination_metadata(): void
    {
        $customer = User::factory()->customer()->create();
        Order::factory()->count(15)->create(['user_id' => $customer->id]);

        $response = $this->getJson('/api/admin/orders?per_page=10&page=1', [
            'Authorization' => 'Bearer '.$this->adminToken(),
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'pagination' => ['total', 'per_page', 'current_page', 'last_page'],
            ])
            ->assertJsonPath('pagination.total', 15)
            ->assertJsonPath('pagination.per_page', 10)
            ->assertJsonPath('pagination.current_page', 1)
            ->assertJsonPath('pagination.last_page', 2);
    }

    /**
     * Task 40: Test filtering & search parameters.
     */
    public function test_customer_products_search_filter(): void
    {
        GroceryItem::factory()->create(['name' => 'Apple', 'is_active' => true]);
        GroceryItem::factory()->create(['name' => 'Banana', 'is_active' => true]);
        GroceryItem::factory()->create(['name' => 'Cherry', 'is_active' => true]);

        $response = $this->getJson('/api/customer/products?search=app');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Apple');
    }

    public function test_customer_products_price_range_filter(): void
    {
        GroceryItem::factory()->create(['name' => 'Cheap', 'price' => 10, 'is_active' => true]);
        GroceryItem::factory()->create(['name' => 'Medium', 'price' => 50, 'is_active' => true]);
        GroceryItem::factory()->create(['name' => 'Expensive', 'price' => 100, 'is_active' => true]);

        $response = $this->getJson('/api/customer/products?min_price=20&max_price=80');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Medium');
    }

    public function test_customer_products_hide_inactive(): void
    {
        GroceryItem::factory()->create(['name' => 'Active Product', 'is_active' => true]);
        GroceryItem::factory()->create(['name' => 'Inactive Product', 'is_active' => false]);

        $response = $this->getJson('/api/customer/products');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Active Product');
    }
}
