<?php

namespace Tests\Feature;

use App\Models\GroceryItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_grocery_orders_and_items_are_connected(): void
    {
        $user = User::factory()->create();

        $groceryItem = GroceryItem::create([
            'name' => 'Apple',
            'description' => 'Fresh red apple',
            'price' => 120.50,
            'stock' => 25,
            'is_active' => true,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'total_amount' => 241.00,
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'grocery_item_id' => $groceryItem->id,
            'quantity' => 2,
            'unit_price' => 120.50,
            'subtotal' => 241.00,
        ]);

        $this->assertSame(1, $user->orders()->count());
        $this->assertSame(1, $order->items()->count());
        $this->assertSame($groceryItem->id, $orderItem->groceryItem->id);
        $this->assertSame(1, $groceryItem->orderItems()->count());
        $this->assertSame($order->id, $orderItem->order->id);
    }
}
