<?php

namespace Tests\Unit;

use App\Contracts\Repositories\OrderItemRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Models\GroceryItem;
use App\Models\Order;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderService = app(OrderService::class);
    }

    /**
     * Task 39: OrderService.placeOrder() logic.
     */
    public function test_place_order_creates_order_with_correct_total(): void
    {
        $user = User::factory()->customer()->create();
        $item1 = GroceryItem::factory()->create(['price' => 100, 'stock' => 10]);
        $item2 = GroceryItem::factory()->create(['price' => 50.50, 'stock' => 10]);

        $order = $this->orderService->placeOrder($user, [
            'items' => [
                ['product_id' => $item1->id, 'quantity' => 2],
                ['product_id' => $item2->id, 'quantity' => 3],
            ],
        ]);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals($user->id, $order->user_id);
        $this->assertEquals('pending', $order->status->value);
        // Expected: (100 * 2) + (50.50 * 3) = 200 + 151.50 = 351.50
        $this->assertEquals('351.50', $order->total_amount);
        $this->assertCount(2, $order->items);
    }

    public function test_place_order_deducts_stock(): void
    {
        $user = User::factory()->customer()->create();
        $item = GroceryItem::factory()->create(['price' => 50, 'stock' => 10]);

        $this->orderService->placeOrder($user, [
            'items' => [
                ['product_id' => $item->id, 'quantity' => 4],
            ],
        ]);

        $this->assertDatabaseHas('grocery_items', [
            'id' => $item->id,
            'stock' => 6,
        ]);
    }

    public function test_place_order_throws_validation_exception_on_insufficient_stock(): void
    {
        $user = User::factory()->customer()->create();
        $item = GroceryItem::factory()->create(['price' => 50, 'stock' => 2]);

        $this->expectException(ValidationException::class);

        $this->orderService->placeOrder($user, [
            'items' => [
                ['product_id' => $item->id, 'quantity' => 5],
            ],
        ]);
    }

    public function test_place_order_throws_validation_exception_on_inactive_product(): void
    {
        $user = User::factory()->customer()->create();
        $item = GroceryItem::factory()->create([
            'price' => 50,
            'stock' => 10,
            'is_active' => false,
        ]);

        $this->expectException(ValidationException::class);

        $this->orderService->placeOrder($user, [
            'items' => [
                ['product_id' => $item->id, 'quantity' => 1],
            ],
        ]);
    }

    public function test_place_order_throws_validation_exception_on_invalid_payload(): void
    {
        $user = User::factory()->customer()->create();

        $this->expectException(ValidationException::class);

        $this->orderService->placeOrder($user, [
            'items' => [
                ['product_id' => 99999, 'quantity' => 1],
            ],
        ]);
    }

    public function test_place_order_rolls_back_on_multi_item_failure(): void
    {
        $user = User::factory()->customer()->create();
        $item1 = GroceryItem::factory()->create(['price' => 50, 'stock' => 5]);
        $item2 = GroceryItem::factory()->create(['price' => 30, 'stock' => 1]);

        try {
            $this->orderService->placeOrder($user, [
                'items' => [
                    ['product_id' => $item1->id, 'quantity' => 2],
                    ['product_id' => $item2->id, 'quantity' => 5], // insufficient
                ],
            ]);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            // Expected
        }

        // No order should be created
        $this->assertDatabaseCount('orders', 0);

        // Both items should retain original stock (transaction rollback)
        $this->assertDatabaseHas('grocery_items', [
            'id' => $item1->id,
            'stock' => 5,
        ]);
        $this->assertDatabaseHas('grocery_items', [
            'id' => $item2->id,
            'stock' => 1,
        ]);
    }

    public function test_place_order_creates_order_items_with_unit_prices(): void
    {
        $user = User::factory()->customer()->create();
        $item = GroceryItem::factory()->create(['price' => 75, 'stock' => 10]);

        $order = $this->orderService->placeOrder($user, [
            'items' => [
                ['product_id' => $item->id, 'quantity' => 3],
            ],
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'grocery_item_id' => $item->id,
            'quantity' => 3,
            'unit_price' => '75.00',
            'subtotal' => '225.00',
        ]);
    }
}
