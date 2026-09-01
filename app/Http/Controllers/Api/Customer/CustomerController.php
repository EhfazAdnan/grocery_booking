<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\Order;
use App\Models\User;
use App\Services\CustomerService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CustomerController
{
    public function __construct(
        protected CustomerService $customerService,
        protected OrderService $orderService,
    ) {}

    public function products(Request $request): JsonResponse
    {
        $items = $this->customerService->browseProducts($request->all());

        return response()->json([
            'data' => $items->items(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'total' => $items->total(),
            ],
        ], 200);
    }

    public function orders(Request $request): JsonResponse
    {
        $user = $request->user();

        $orders = $this->customerService->getOrderHistory($user);

        return response()->json([
            'data' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
            ],
        ], 200);
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json(['data' => $this->customerService->profile($user)], 200);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            $updatedUser = $this->customerService->updateProfile($user, $request->all());

            return response()->json(['data' => $updatedUser], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function placeOrder(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            $order = $this->orderService->placeOrder($user, $request->all());

            $items = $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'order_id' => $item->order_id,
                    'product_id' => $item->grocery_item_id,
                    'grocery_item_id' => $item->grocery_item_id,
                    'quantity' => $item->quantity,
                    'unit_price' => (string) $item->unit_price,
                    'subtotal' => (string) $item->subtotal,
                ];
            })->values();

            return response()->json([
                'data' => [
                    'id' => $order->id,
                    'user_id' => $order->user_id,
                    'status' => $order->status,
                    'total_amount' => (string) $order->total_amount,
                    'items' => $items,
                ],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * Get customer's specific order details.
     * GET /customer/orders/{id}
     */
    public function orderDetail(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        // Verify the order belongs to the current user
        if ($order->user_id !== $user->id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $items = $order->items->map(function ($item) {
            return [
                'id' => $item->id,
                'order_id' => $item->order_id,
                'product_id' => $item->grocery_item_id,
                'product_name' => $item->groceryItem->name,
                'quantity' => $item->quantity,
                'unit_price' => (string) $item->unit_price,
                'subtotal' => (string) $item->subtotal,
            ];
        })->values();

        return response()->json([
            'data' => [
                'id' => $order->id,
                'user_id' => $order->user_id,
                'status' => $order->status,
                'total_amount' => (string) $order->total_amount,
                'status_changed_at' => $order->status_changed_at,
                'created_at' => $order->created_at,
                'items' => $items,
            ],
        ], 200);
    }
}
