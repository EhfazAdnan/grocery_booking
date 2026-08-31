<?php

namespace App\Services;

use App\Contracts\Repositories\OrderItemRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Models\GroceryItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected OrderItemRepositoryInterface $orderItemRepository
    ) {}

    public function placeOrder(User $user, array $payload): Order
    {
        $validator = Validator::make($payload, [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:grocery_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = $validator->validated();
        $items = $validated['items'];

        return DB::transaction(function () use ($user, $items) {
            $totalAmount = 0;
            $orderItems = [];

            foreach ($items as $entry) {
                $product = GroceryItem::query()->lockForUpdate()->findOrFail($entry['product_id']);

                if ($product->stock < $entry['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => ['Insufficient stock for product: '.$product->name],
                    ]);
                }

                $subtotal = $product->price * $entry['quantity'];
                $totalAmount += $subtotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'grocery_item_id' => $product->id,
                    'quantity' => $entry['quantity'],
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                ];
            }

            $order = $this->orderRepository->create([
                'user_id' => $user->id,
                'status' => 'pending',
                'total_amount' => $totalAmount,
            ]);

            foreach ($orderItems as $item) {
                $this->orderItemRepository->createForOrder($order, [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);

                $product = GroceryItem::query()->lockForUpdate()->findOrFail($item['product_id']);
                $product->decrement('stock', $item['quantity']);
            }

            $order->load('items');

            $order->update(['total_amount' => $totalAmount]);

            return $order->fresh();
        });
    }
}
