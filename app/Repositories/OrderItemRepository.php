<?php

namespace App\Repositories;

use App\Contracts\Repositories\OrderItemRepositoryInterface;
use App\Models\Order;
use App\Models\OrderItem;

class OrderItemRepository implements OrderItemRepositoryInterface
{
    public function createForOrder(Order $order, array $itemData): OrderItem
    {
        return OrderItem::create([
            'order_id' => $order->id,
            'grocery_item_id' => $itemData['product_id'],
            'quantity' => $itemData['quantity'],
            'unit_price' => $itemData['unit_price'],
            'subtotal' => $itemData['subtotal'],
        ]);
    }
}
