<?php

namespace Tests\Feature;

use App\Models\GroceryItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\JWTGuard;

class CustomerFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private function jwtGuard(): JWTGuard
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');

        return $guard;
    }

    public function test_customer_can_browse_products(): void
    {
        GroceryItem::factory()->create([
            'name' => 'Apple',
            'price' => 50,
            'stock' => 10,
            'is_active' => true,
        ]);

        GroceryItem::factory()->create([
            'name' => 'Banana',
            'price' => 80,
            'stock' => 0,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/customer/products?search=app');

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'Apple')
            ->assertJsonCount(1, 'data');
    }

    public function test_customer_can_view_their_order_history(): void
    {
        $customer = User::factory()->customer()->create();
        $otherCustomer = User::factory()->customer()->create();

        $ownOrder = Order::create([
            'user_id' => $customer->id,
            'status' => 'pending',
            'total_amount' => 150,
        ]);

        Order::create([
            'user_id' => $otherCustomer->id,
            'status' => 'pending',
            'total_amount' => 200,
        ]);

        $groceryItem = GroceryItem::factory()->create(['price' => 75]);

        OrderItem::create([
            'order_id' => $ownOrder->id,
            'grocery_item_id' => $groceryItem->id,
            'quantity' => 2,
            'unit_price' => 75,
            'subtotal' => 150,
        ]);

        $token = $this->jwtGuard()->login($customer);

        $response = $this->getJson('/api/customer/orders', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownOrder->id)
            ->assertJsonPath('data.0.total_amount', '150.00');
    }

    public function test_customer_can_view_and_update_profile(): void
    {
        $customer = User::factory()->customer()->create([
            'name' => 'Alice Customer',
            'email' => 'alice@example.com',
        ]);

        $token = $this->jwtGuard()->login($customer);

        $response = $this->getJson('/api/customer/profile', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Alice Customer')
            ->assertJsonPath('data.email', 'alice@example.com');

        $update = $this->putJson('/api/customer/profile', [
            'name' => 'Alice Updated',
            'email' => 'alice.updated@example.com',
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $update->assertOk()
            ->assertJsonPath('data.name', 'Alice Updated')
            ->assertJsonPath('data.email', 'alice.updated@example.com');

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'name' => 'Alice Updated',
            'email' => 'alice.updated@example.com',
        ]);
    }

    public function test_customer_can_place_order_with_valid_items(): void
    {
        $customer = User::factory()->customer()->create();
        $item = GroceryItem::factory()->create([
            'name' => 'Orange',
            'price' => 25,
            'stock' => 5,
            'is_active' => true,
        ]);

        $token = $this->jwtGuard()->login($customer);

        $response = $this->postJson('/api/customer/orders', [
            'items' => [
                ['product_id' => $item->id, 'quantity' => 2],
            ],
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.total_amount', '50.00')
            ->assertJsonPath('data.items.0.product_id', $item->id)
            ->assertJsonPath('data.items.0.quantity', 2);

        $this->assertDatabaseHas('orders', [
            'user_id' => $customer->id,
            'total_amount' => '50.00',
        ]);

        $this->assertDatabaseHas('order_items', [
            'grocery_item_id' => $item->id,
            'quantity' => 2,
            'subtotal' => '50.00',
        ]);

        $this->assertDatabaseHas('grocery_items', [
            'id' => $item->id,
            'stock' => 3,
        ]);
    }

    public function test_customer_cannot_place_order_when_stock_is_insufficient(): void
    {
        $customer = User::factory()->customer()->create();
        $item = GroceryItem::factory()->create([
            'name' => 'Mango',
            'price' => 30,
            'stock' => 1,
            'is_active' => true,
        ]);

        $token = $this->jwtGuard()->login($customer);

        $response = $this->postJson('/api/customer/orders', [
            'items' => [
                ['product_id' => $item->id, 'quantity' => 2],
            ],
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['success', 'message'])
            ->assertJson(['success' => false]);
    }
}
