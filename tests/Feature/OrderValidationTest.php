<?php

namespace Tests\Feature;

use App\Models\GroceryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\JWTGuard;

class OrderValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function jwtGuard(): JWTGuard
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');

        return $guard;
    }

    protected function customerToken(): string
    {
        $customer = User::factory()->customer()->create();

        return $this->jwtGuard()->fromUser($customer);
    }

    /**
     * Task 37: Order validation errors.
     */
    public function test_order_rejects_negative_quantity(): void
    {
        $item = GroceryItem::factory()->create(['stock' => 10]);

        $response = $this->postJson('/api/customer/orders', [
            'items' => [
                ['product_id' => $item->id, 'quantity' => -2],
            ],
        ], [
            'Authorization' => 'Bearer '.$this->customerToken(),
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['items.0.quantity']]);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_order_rejects_zero_quantity(): void
    {
        $item = GroceryItem::factory()->create(['stock' => 10]);

        $response = $this->postJson('/api/customer/orders', [
            'items' => [
                ['product_id' => $item->id, 'quantity' => 0],
            ],
        ], [
            'Authorization' => 'Bearer '.$this->customerToken(),
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['items.0.quantity']]);
    }

    public function test_order_rejects_non_existent_product(): void
    {
        $response = $this->postJson('/api/customer/orders', [
            'items' => [
                ['product_id' => 99999, 'quantity' => 1],
            ],
        ], [
            'Authorization' => 'Bearer '.$this->customerToken(),
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['items.0.product_id']]);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_order_rejects_empty_items_array(): void
    {
        $response = $this->postJson('/api/customer/orders', [
            'items' => [],
        ], [
            'Authorization' => 'Bearer '.$this->customerToken(),
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['items']]);
    }

    public function test_order_rejects_missing_items_field(): void
    {
        $response = $this->postJson('/api/customer/orders', [], [
            'Authorization' => 'Bearer '.$this->customerToken(),
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['items']]);
    }

    public function test_order_rejects_non_integer_quantity(): void
    {
        $item = GroceryItem::factory()->create(['stock' => 10]);

        $response = $this->postJson('/api/customer/orders', [
            'items' => [
                ['product_id' => $item->id, 'quantity' => 'abc'],
            ],
        ], [
            'Authorization' => 'Bearer '.$this->customerToken(),
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['items.0.quantity']]);
    }

    public function test_order_rejects_inactive_product(): void
    {
        $item = GroceryItem::factory()->create([
            'stock' => 10,
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/customer/orders', [
            'items' => [
                ['product_id' => $item->id, 'quantity' => 1],
            ],
        ], [
            'Authorization' => 'Bearer '.$this->customerToken(),
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['items']]);

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('grocery_items', [
            'id' => $item->id,
            'stock' => 10,
        ]);
    }

    public function test_order_rejects_insufficient_stock(): void
    {
        $item = GroceryItem::factory()->create(['stock' => 3]);

        $response = $this->postJson('/api/customer/orders', [
            'items' => [
                ['product_id' => $item->id, 'quantity' => 5],
            ],
        ], [
            'Authorization' => 'Bearer '.$this->customerToken(),
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['items']]);

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('grocery_items', [
            'id' => $item->id,
            'stock' => 3,
        ]);
    }

    /**
     * Task 37: Order total calculation accuracy.
     */
    public function test_order_total_is_calculated_server_side(): void
    {
        $item1 = GroceryItem::factory()->create(['price' => 100, 'stock' => 10]);
        $item2 = GroceryItem::factory()->create(['price' => 75.50, 'stock' => 10]);

        $response = $this->postJson('/api/customer/orders', [
            'items' => [
                ['product_id' => $item1->id, 'quantity' => 2],
                ['product_id' => $item2->id, 'quantity' => 4],
            ],
        ], [
            'Authorization' => 'Bearer '.$this->customerToken(),
        ]);

        // Expected: (100 * 2) + (75.50 * 4) = 200 + 302 = 502
        $response->assertStatus(201)
            ->assertJsonPath('data.total_amount', '502.00')
            ->assertJsonPath('data.items.0.unit_price', '100.00')
            ->assertJsonPath('data.items.0.subtotal', '200.00')
            ->assertJsonPath('data.items.0.quantity', 2)
            ->assertJsonPath('data.items.1.unit_price', '75.50')
            ->assertJsonPath('data.items.1.subtotal', '302.00')
            ->assertJsonPath('data.items.1.quantity', 4);
    }

    public function test_order_total_is_not_affected_by_client_submitted_values(): void
    {
        $item = GroceryItem::factory()->create(['price' => 50, 'stock' => 10]);

        // Client tries to send price/total but server should ignore these
        $response = $this->postJson('/api/customer/orders', [
            'items' => [
                ['product_id' => $item->id, 'quantity' => 2],
            ],
            'total_amount' => 1, // malicious value
            'price' => 1,        // malicious value
        ], [
            'Authorization' => 'Bearer '.$this->customerToken(),
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.total_amount', '100.00')
            ->assertJsonPath('data.items.0.unit_price', '50.00');
    }

    public function test_single_item_order_calculates_correct_total(): void
    {
        $item = GroceryItem::factory()->create(['price' => 25, 'stock' => 10]);

        $response = $this->postJson('/api/customer/orders', [
            'items' => [
                ['product_id' => $item->id, 'quantity' => 3],
            ],
        ], [
            'Authorization' => 'Bearer '.$this->customerToken(),
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.total_amount', '75.00')
            ->assertJsonPath('data.items.0.subtotal', '75.00');
    }

    public function test_multiple_item_order_calculates_correct_total(): void
    {
        $item1 = GroceryItem::factory()->create(['price' => 10.50, 'stock' => 10]);
        $item2 = GroceryItem::factory()->create(['price' => 20, 'stock' => 10]);
        $item3 = GroceryItem::factory()->create(['price' => 15.25, 'stock' => 10]);

        $response = $this->postJson('/api/customer/orders', [
            'items' => [
                ['product_id' => $item1->id, 'quantity' => 1],
                ['product_id' => $item2->id, 'quantity' => 2],
                ['product_id' => $item3->id, 'quantity' => 4],
            ],
        ], [
            'Authorization' => 'Bearer '.$this->customerToken(),
        ]);

        // Expected: 10.50 + 40 + 61 = 111.50
        $response->assertStatus(201)
            ->assertJsonPath('data.total_amount', '111.50')
            ->assertJsonCount(3, 'data.items');
    }

    public function test_order_stock_is_deducted_correctly(): void
    {
        $item = GroceryItem::factory()->create(['stock' => 10, 'price' => 50]);

        $response = $this->postJson('/api/customer/orders', [
            'items' => [
                ['product_id' => $item->id, 'quantity' => 4],
            ],
        ], [
            'Authorization' => 'Bearer '.$this->customerToken(),
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('grocery_items', [
            'id' => $item->id,
            'stock' => 6,
        ]);
    }

    public function test_order_creates_order_and_items_records(): void
    {
        $customer = User::factory()->customer()->create();
        $token = $this->jwtGuard()->fromUser($customer);

        $item = GroceryItem::factory()->create(['stock' => 10, 'price' => 40]);

        $this->postJson('/api/customer/orders', [
            'items' => [
                ['product_id' => $item->id, 'quantity' => 2],
            ],
        ], [
            'Authorization' => 'Bearer '.$token,
        ])->assertStatus(201);

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);

        $this->assertDatabaseHas('orders', [
            'user_id' => $customer->id,
            'status' => 'pending',
            'total_amount' => '80.00',
        ]);

        $this->assertDatabaseHas('order_items', [
            'grocery_item_id' => $item->id,
            'quantity' => 2,
            'unit_price' => '40.00',
            'subtotal' => '80.00',
        ]);
    }
}
