<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\User;
use App\Services\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CustomerController
{
    public function __construct(protected CustomerService $customerService) {}

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
}
