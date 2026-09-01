<?php

namespace Tests\Feature;

use App\Models\GroceryItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\JWTGuard;

class AuthorizationTest extends TestCase
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
     * Task 36: Admin routes deny customer access.
     */
    public function test_customer_cannot_access_admin_grocery_items(): void
    {
        $response = $this->getJson('/api/admin/grocery-items', [
            'Authorization' => 'Bearer '.$this->customerToken(),
        ]);

        $response->assertStatus(403);
    }

    public function test_customer_cannot_create_grocery_item(): void
    {
        $response = $this->postJson('/api/admin/grocery-items', [
            'name' => 'Hacked Item',
            'price' => 10,
            'stock' => 5,
        ], [
            'Authorization' => 'Bearer '.$this->customerToken(),
        ]);

        $response->assertStatus(403);
    }

    public function test_customer_cannot_update_grocery_item(): void
    {
        $item = GroceryItem::factory()->create();

        $response = $this->putJson('/api/admin/grocery-items/'.$item->id, [
            'name' => 'Hacked Name',
        ], [
            'Authorization' => 'Bearer '.$this->customerToken(),
        ]);

        $response->assertStatus(403);
    }

    public function test_customer_cannot_delete_grocery_item(): void
    {
        $item = GroceryItem::factory()->create();

        $response = $this->deleteJson('/api/admin/grocery-items/'.$item->id, [], [
            'Authorization' => 'Bearer '.$this->customerToken(),
        ]);

        $response->assertStatus(403);
    }

    public function test_customer_cannot_update_stock(): void
    {
        $item = GroceryItem::factory()->create();

        $response = $this->patchJson('/api/admin/grocery-items/'.$item->id.'/stock', [
            'stock' => 100,
        ], [
            'Authorization' => 'Bearer '.$this->customerToken(),
        ]);

        $response->assertStatus(403);
    }

    public function test_customer_cannot_view_admin_orders(): void
    {
        $response = $this->getJson('/api/admin/orders', [
            'Authorization' => 'Bearer '.$this->customerToken(),
        ]);

        $response->assertStatus(403);
    }

    public function test_customer_cannot_change_order_status(): void
    {
        $customer = User::factory()->customer()->create();
        $order = Order::factory()->create(['user_id' => $customer->id]);

        $response = $this->putJson('/api/admin/orders/'.$order->id.'/status', [
            'status' => 'confirmed',
        ], [
            'Authorization' => 'Bearer '.$this->customerToken(),
        ]);

        $response->assertStatus(403);
    }

    public function test_customer_cannot_access_analytics(): void
    {
        $response = $this->getJson('/api/admin/analytics/revenue', [
            'Authorization' => 'Bearer '.$this->customerToken(),
        ]);

        $response->assertStatus(403);
    }

    /**
     * Task 36: Customer routes deny admin access (where applicable).
     */
    public function test_admin_cannot_place_order(): void
    {
        $item = GroceryItem::factory()->create(['stock' => 10]);

        $response = $this->postJson('/api/customer/orders', [
            'items' => [
                ['product_id' => $item->id, 'quantity' => 1],
            ],
        ], [
            'Authorization' => 'Bearer '.$this->adminToken(),
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_cannot_view_customer_order_history(): void
    {
        $response = $this->getJson('/api/customer/orders', [
            'Authorization' => 'Bearer '.$this->adminToken(),
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_cannot_view_customer_profile(): void
    {
        $response = $this->getJson('/api/customer/profile', [
            'Authorization' => 'Bearer '.$this->adminToken(),
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_cannot_update_customer_profile(): void
    {
        $response = $this->putJson('/api/customer/profile', [
            'name' => 'Hacked Name',
        ], [
            'Authorization' => 'Bearer '.$this->adminToken(),
        ]);

        $response->assertStatus(403);
    }

    /**
     * Task 36: User can only access own orders.
     */
    public function test_user_can_only_access_own_orders(): void
    {
        $customer1 = User::factory()->customer()->create();
        $customer2 = User::factory()->customer()->create();

        $order1 = Order::factory()->create(['user_id' => $customer1->id]);
        $order2 = Order::factory()->create(['user_id' => $customer2->id]);

        $token = $this->jwtGuard()->fromUser($customer1);

        // Can access own order
        $this->getJson('/api/customer/orders/'.$order1->id, [
            'Authorization' => 'Bearer '.$token,
        ])->assertStatus(200);

        // Cannot access other user's order
        $this->getJson('/api/customer/orders/'.$order2->id, [
            'Authorization' => 'Bearer '.$token,
        ])->assertStatus(404);
    }

    public function test_user_order_history_only_contains_own_orders(): void
    {
        $customer1 = User::factory()->customer()->create();
        $customer2 = User::factory()->customer()->create();

        Order::factory()->create(['user_id' => $customer1->id]);
        Order::factory()->create(['user_id' => $customer2->id]);
        Order::factory()->create(['user_id' => $customer2->id]);

        $token = $this->jwtGuard()->fromUser($customer1);

        $response = $this->getJson('/api/customer/orders', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.user_id', $customer1->id);
    }

    /**
     * Unauthenticated access to protected routes.
     */
    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        $this->getJson('/api/auth/me')->assertStatus(401);
        $this->getJson('/api/customer/orders')->assertStatus(401);
        $this->getJson('/api/customer/profile')->assertStatus(401);
        $this->getJson('/api/admin/grocery-items')->assertStatus(401);
        $this->getJson('/api/admin/orders')->assertStatus(401);
        $this->getJson('/api/admin/analytics/revenue')->assertStatus(401);
    }
}
