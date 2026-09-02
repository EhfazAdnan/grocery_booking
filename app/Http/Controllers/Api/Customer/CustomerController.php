<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Requests\PlaceOrderRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\GroceryItemResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\UserResource;
use App\Models\Order;
use App\Services\CustomerService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
            'data' => GroceryItemResource::collection(collect($items->items())),
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
            'data' => OrderResource::collection(collect($orders->items())),
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

        return response()->json(['data' => new UserResource($this->customerService->profile($user))], 200);
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        $updatedUser = $this->customerService->updateProfile($user, $request->validated());

        return response()->json(['data' => new UserResource($updatedUser)], 200);
    }

    public function placeOrder(PlaceOrderRequest $request): JsonResponse
    {
        $user = $request->user();

        $order = $this->orderService->placeOrder($user, $request->validated());

        return response()->json([
            'data' => new OrderResource($order->load('items')),
        ], 201);
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

        return response()->json([
            'data' => new OrderResource($order->load('items.groceryItem')),
        ], 200);
    }
}
