<?php

namespace Tests\Feature;

use App\Models\GroceryItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tymon\JWTAuth\JWTGuard;

class ConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    private function jwtGuard(): JWTGuard
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');

        return $guard;
    }

    public function test_concurrent_orders_prevent_overselling(): void
    {
        $lowStockItem = GroceryItem::factory()->create([
            'name' => 'Limited Item',
            'price' => 100,
            'stock' => 2,
            'is_active' => true,
        ]);

        $customer1 = User::factory()->customer()->create();
        $customer2 = User::factory()->customer()->create();

        $token1 = $this->jwtGuard()->login($customer1);
        $token2 = $this->jwtGuard()->login($customer2);

        // First order: customer 1 orders 2 units (should succeed)
        $response1 = $this->postJson('/api/customer/orders', [
            'items' => [
                ['product_id' => $lowStockItem->id, 'quantity' => 2],
            ],
        ], [
            'Authorization' => 'Bearer '.$token1,
        ]);

        $response1->assertStatus(201);

        // Verify stock was decremented to 0
        $this->assertDatabaseHas('grocery_items', [
            'id' => $lowStockItem->id,
            'stock' => 0,
        ]);

        // Verify ANY order was created (first order should succeed)
        $this->assertGreaterThan(0, Order::count(), 'No orders found in database');

        // Second order: customer 2 tries to order 1 unit (should fail - no stock)
        $response2 = $this->postJson('/api/customer/orders', [
            'items' => [
                ['product_id' => $lowStockItem->id, 'quantity' => 1],
            ],
        ], [
            'Authorization' => 'Bearer '.$token2,
        ]);

        $response2->assertStatus(422)
            ->assertJsonStructure(['success', 'message'])
            ->assertJson(['success' => false]);

        // Verify only one order total (customer1's succeeded, customer2's failed)
        $this->assertEquals(1, Order::count(), 'Expected exactly 1 order in database');

        // Verify stock is still 0 (no negative stock, no double-decrement)
        $this->assertDatabaseHas('grocery_items', [
            'id' => $lowStockItem->id,
            'stock' => 0,
        ]);
    }

    public function test_transaction_rollback_on_validation_failure(): void
    {
        $item1 = GroceryItem::factory()->create([
            'name' => 'Item 1',
            'price' => 50,
            'stock' => 5,
            'is_active' => true,
        ]);

        $customer = User::factory()->customer()->create();
        $token = $this->jwtGuard()->login($customer);

        // Attempt order with non-existent product (should fail validation)
        $response = $this->postJson('/api/customer/orders', [
            'items' => [
                ['product_id' => $item1->id, 'quantity' => 2],
                ['product_id' => 99999, 'quantity' => 1], // non-existent product
            ],
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(422);

        // Verify no order was created (transaction was rolled back)
        $this->assertEquals(0, Order::count());

        // Verify stock was not decremented for item1
        $this->assertDatabaseHas('grocery_items', [
            'id' => $item1->id,
            'stock' => 5,
        ]);
    }

    public function test_inventory_is_atomic_no_partial_orders(): void
    {
        $item1 = GroceryItem::factory()->create([
            'name' => 'Item 1',
            'price' => 100,
            'stock' => 3,
            'is_active' => true,
        ]);

        $item2 = GroceryItem::factory()->create([
            'name' => 'Item 2',
            'price' => 200,
            'stock' => 1,
            'is_active' => true,
        ]);

        $customer = User::factory()->customer()->create();
        $token = $this->jwtGuard()->login($customer);

        // Attempt multi-item order where second item has insufficient stock
        $response = $this->postJson('/api/customer/orders', [
            'items' => [
                ['product_id' => $item1->id, 'quantity' => 2],
                ['product_id' => $item2->id, 'quantity' => 5], // insufficient
            ],
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(422);

        // Verify no order was created
        $this->assertEquals(0, Order::count());

        // Verify BOTH items retained their original stock (transaction rollback)
        $this->assertDatabaseHas('grocery_items', [
            'id' => $item1->id,
            'stock' => 3,
        ]);

        $this->assertDatabaseHas('grocery_items', [
            'id' => $item2->id,
            'stock' => 1,
        ]);
    }
}
