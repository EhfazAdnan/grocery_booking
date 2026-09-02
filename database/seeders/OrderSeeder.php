<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\GroceryItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Seed sample orders so admin has data to view.
     */
    public function run(): void
    {
        $customers = User::where('role', 'customer')->get();
        $products = GroceryItem::where('is_active', true)->take(5)->get();

        if ($customers->isEmpty() || $products->isEmpty()) {
            return;
        }

        // Create 3 sample orders
        for ($i = 0; $i < 3; $i++) {
            $customer = $customers->random();
            $product1 = $products->random();
            $product2 = $products->random();

            $quantity1 = rand(1, 3);
            $quantity2 = rand(1, 2);
            $subtotal1 = $product1->price * $quantity1;
            $subtotal2 = $product2->price * $quantity2;
            $totalAmount = $subtotal1 + $subtotal2;

            $order = Order::create([
                'user_id' => $customer->id,
                'status' => OrderStatus::PENDING,
                'total_amount' => $totalAmount,
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'grocery_item_id' => $product1->id,
                'quantity' => $quantity1,
                'unit_price' => $product1->price,
                'subtotal' => $subtotal1,
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'grocery_item_id' => $product2->id,
                'quantity' => $quantity2,
                'unit_price' => $product2->price,
                'subtotal' => $subtotal2,
            ]);
        }
    }
}
