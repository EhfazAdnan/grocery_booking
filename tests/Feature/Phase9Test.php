<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\GroceryItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth as JWT;

class Phase9Test extends TestCase
{
    use RefreshDatabase;

    protected function jwtGuard()
    {
        return auth('api');
    }

    /**
     * Task 23: Order Status Management
     */
    public function test_admin_can_change_order_status(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();

        $item = GroceryItem::factory()->create([
            'price' => 50,
            'stock' => 10,
        ]);

        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'status' => 'pending',
            'total_amount' => 50,
        ]);

        $token = auth('api')->fromUser($admin);

        // Change status to confirmed
        $response = $this->putJson('/api/admin/orders/'.$order->id.'/status', [
            'status' => 'confirmed',
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'confirmed',
        ]);

        $this->assertNotNull(Order::find($order->id)->status_changed_at);
    }

    public function test_admin_cannot_change_delivered_order_status(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();

        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'status' => 'delivered',
            'total_amount' => 50,
        ]);

        $token = auth('api')->fromUser($admin);

        $response = $this->putJson('/api/admin/orders/'.$order->id.'/status', [
            'status' => 'pending',
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors']);

        // Order status should not change
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'delivered',
        ]);
    }

    public function test_customer_cannot_change_order_status(): void
    {
        $customer = User::factory()->customer()->create();

        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'status' => 'pending',
            'total_amount' => 50,
        ]);

        $token = auth('api')->fromUser($customer);

        // Try to change status (should fail because endpoint requires admin role)
        $response = $this->putJson('/api/admin/orders/'.$order->id.'/status', [
            'status' => 'confirmed',
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(403);
    }

    /**
     * Task 24: Customer Order Details Endpoint
     */
    public function test_customer_can_view_own_order_details(): void
    {
        $customer = User::factory()->customer()->create();

        $item = GroceryItem::factory()->create([
            'name' => 'Test Product',
            'price' => 100,
            'stock' => 10,
        ]);

        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'status' => 'pending',
            'total_amount' => 100,
        ]);

        $order->items()->create([
            'grocery_item_id' => $item->id,
            'quantity' => 1,
            'unit_price' => 100,
            'subtotal' => 100,
        ]);

        $token = auth('api')->fromUser($customer);

        $response = $this->getJson('/api/customer/orders/'.$order->id, [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'status',
                    'total_amount',
                    'created_at',
                    'items' => [
                        '*' => [
                            'id',
                            'product_id',
                            'quantity',
                            'unit_price',
                            'subtotal',
                        ],
                    ],
                ],
            ])
            ->assertJson([
                'data' => [
                    'id' => $order->id,
                    'user_id' => $customer->id,
                ],
            ]);
    }

    public function test_customer_cannot_view_other_customer_order(): void
    {
        $customer1 = User::factory()->customer()->create();
        $customer2 = User::factory()->customer()->create();

        $order = Order::factory()->create([
            'user_id' => $customer2->id,
            'status' => 'pending',
            'total_amount' => 50,
        ]);

        $token = auth('api')->fromUser($customer1);

        $response = $this->getJson('/api/customer/orders/'.$order->id, [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(404);
    }

    /**
     * Task 25: Admin Analytics Endpoints
     */
    public function test_admin_can_get_revenue_analytics(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();

        // Create orders with different dates
        Order::factory()->create([
            'user_id' => $customer->id,
            'total_amount' => 100,
            'created_at' => now()->subDays(5),
        ]);

        Order::factory()->create([
            'user_id' => $customer->id,
            'total_amount' => 50,
            'created_at' => now(),
        ]);

        $token = auth('api')->fromUser($admin);

        $response = $this->getJson('/api/admin/analytics/revenue', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_revenue',
                    'order_count',
                    'average_order_value',
                    'date_range',
                ],
            ]);

        $data = $response->json()['data'];
        $this->assertEquals(150, $data['total_revenue']);
        $this->assertEquals(2, $data['order_count']);
    }

    public function test_admin_can_get_revenue_analytics_with_date_range(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();

        Order::factory()->create([
            'user_id' => $customer->id,
            'total_amount' => 100,
            'created_at' => now()->subDays(10),
        ]);

        Order::factory()->create([
            'user_id' => $customer->id,
            'total_amount' => 50,
            'created_at' => now(),
        ]);

        $token = auth('api')->fromUser($admin);

        $startDate = now()->subDays(2)->toDateString();
        $endDate = now()->toDateString();

        $response = $this->getJson("/api/admin/analytics/revenue?start_date={$startDate}&end_date={$endDate}", [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(200);
        $data = $response->json()['data'];

        // Should only include recent order (50)
        $this->assertEquals(50, $data['total_revenue']);
        $this->assertEquals(1, $data['order_count']);
    }

    public function test_admin_can_get_top_products(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();

        $product1 = GroceryItem::factory()->create(['name' => 'Product 1']);
        $product2 = GroceryItem::factory()->create(['name' => 'Product 2']);

        // Create orders with multiple items
        $order = Order::factory()->create(['user_id' => $customer->id, 'total_amount' => 300]);

        $order->items()->createMany([
            [
                'grocery_item_id' => $product1->id,
                'quantity' => 3,
                'unit_price' => 50,
                'subtotal' => 150,
            ],
            [
                'grocery_item_id' => $product2->id,
                'quantity' => 2,
                'unit_price' => 50,
                'subtotal' => 100,
            ],
            [
                'grocery_item_id' => $product1->id,
                'quantity' => 1,
                'unit_price' => 50,
                'subtotal' => 50,
            ],
        ]);

        $token = auth('api')->fromUser($admin);

        $response = $this->getJson('/api/admin/analytics/top-products?limit=10', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'product_id',
                        'product_name',
                        'order_count',
                        'total_quantity',
                        'total_revenue',
                    ],
                ],
            ]);

        $data = $response->json()['data'];

        // Product1 should be first (4 total items across 2 order items)
        $this->assertEquals($product1->id, $data[0]['product_id']);
        $this->assertEquals(4, $data[0]['total_quantity']);
    }

    public function test_admin_can_get_order_count_by_daily(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();

        Order::factory()->create([
            'user_id' => $customer->id,
            'total_amount' => 100,
            'created_at' => now(),
        ]);

        Order::factory()->create([
            'user_id' => $customer->id,
            'total_amount' => 50,
            'created_at' => now()->subDays(1),
        ]);

        $token = auth('api')->fromUser($admin);

        $response = $this->getJson('/api/admin/analytics/order-count?period=daily', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'period',
                        'order_count',
                        'revenue',
                    ],
                ],
            ]);

        $data = $response->json()['data'];
        $this->assertGreaterThanOrEqual(2, count($data));
    }

    public function test_admin_can_get_order_count_by_monthly(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();

        Order::factory()->create([
            'user_id' => $customer->id,
            'total_amount' => 100,
        ]);

        $token = auth('api')->fromUser($admin);

        $response = $this->getJson('/api/admin/analytics/order-count?period=monthly', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'period',
                        'order_count',
                        'revenue',
                    ],
                ],
            ]);
    }

    public function test_admin_cannot_use_invalid_period(): void
    {
        $admin = User::factory()->admin()->create();

        $token = auth('api')->fromUser($admin);

        $response = $this->getJson('/api/admin/analytics/order-count?period=invalid', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(422);
    }

    public function test_customer_cannot_access_analytics(): void
    {
        $customer = User::factory()->customer()->create();

        $token = auth('api')->fromUser($customer);

        $response = $this->getJson('/api/admin/analytics/revenue', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(403);
    }
}
