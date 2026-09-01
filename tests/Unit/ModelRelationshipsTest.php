<?php

namespace Tests\Unit;

use App\Models\GroceryItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Task 39: User model relationships.
     */
    public function test_user_has_many_orders(): void
    {
        $user = User::factory()->customer()->create();

        Order::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->orders);
        $this->assertInstanceOf(Order::class, $user->orders->first());
    }

    public function test_user_with_no_orders_returns_empty_collection(): void
    {
        $user = User::factory()->customer()->create();

        $this->assertCount(0, $user->orders);
    }

    public function test_user_admin_check(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($customer->isAdmin());
    }

    /**
     * Task 39: Order/OrderItem relationships.
     */
    public function test_order_belongs_to_user(): void
    {
        $user = User::factory()->customer()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $order->user);
        $this->assertEquals($user->id, $order->user->id);
    }

    public function test_order_has_many_items(): void
    {
        $user = User::factory()->customer()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $item = GroceryItem::factory()->create();

        OrderItem::create([
            'order_id' => $order->id,
            'grocery_item_id' => $item->id,
            'quantity' => 2,
            'unit_price' => 50,
            'subtotal' => 100,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'grocery_item_id' => $item->id,
            'quantity' => 3,
            'unit_price' => 50,
            'subtotal' => 150,
        ]);

        $this->assertCount(2, $order->items);
        $this->assertInstanceOf(OrderItem::class, $order->items()->first());
    }

    public function test_order_item_belongs_to_order(): void
    {
        $user = User::factory()->customer()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $item = GroceryItem::factory()->create();

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'grocery_item_id' => $item->id,
            'quantity' => 1,
            'unit_price' => 50,
            'subtotal' => 50,
        ]);

        $this->assertInstanceOf(Order::class, $orderItem->order);
        $this->assertEquals($order->id, $orderItem->order->id);
    }

    public function test_order_item_belongs_to_grocery_item(): void
    {
        $user = User::factory()->customer()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $item = GroceryItem::factory()->create();

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'grocery_item_id' => $item->id,
            'quantity' => 1,
            'unit_price' => 50,
            'subtotal' => 50,
        ]);

        $this->assertInstanceOf(GroceryItem::class, $orderItem->groceryItem);
        $this->assertEquals($item->id, $orderItem->groceryItem->id);
    }

    public function test_grocery_item_has_many_order_items(): void
    {
        $item = GroceryItem::factory()->create();
        $user = User::factory()->customer()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        OrderItem::create([
            'order_id' => $order->id,
            'grocery_item_id' => $item->id,
            'quantity' => 1,
            'unit_price' => 50,
            'subtotal' => 50,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'grocery_item_id' => $item->id,
            'quantity' => 2,
            'unit_price' => 50,
            'subtotal' => 100,
        ]);

        $this->assertCount(2, $item->orderItems);
        $this->assertInstanceOf(OrderItem::class, $item->orderItems->first());
    }
}
