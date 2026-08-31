<?php

namespace App\Contracts\Repositories;

use App\Models\Order;
use App\Models\OrderItem;

interface OrderItemRepositoryInterface
{
    public function createForOrder(Order $order, array $itemData): OrderItem;
}
