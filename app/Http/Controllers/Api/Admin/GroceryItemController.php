<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\GroceryItem;
use App\Services\GroceryItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class GroceryItemController
{
    public function __construct(protected GroceryItemService $groceryItemService) {}

    public function index(): JsonResponse
    {
        $items = $this->groceryItemService->list();

        return response()->json([
            'data' => $items->items(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'total' => $items->total(),
            ],
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $item = $this->groceryItemService->create($request->all());

            return response()->json(['data' => $item], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function show(GroceryItem $groceryItem): JsonResponse
    {
        return response()->json(['data' => $groceryItem], 200);
    }

    public function update(Request $request, GroceryItem $groceryItem): JsonResponse
    {
        try {
            $item = $this->groceryItemService->update($groceryItem, $request->all());

            return response()->json(['data' => $item], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function updateStock(Request $request, GroceryItem $groceryItem): JsonResponse
    {
        try {
            $item = $this->groceryItemService->updateStock($groceryItem, $request->all());

            return response()->json(['data' => $item], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function destroy(GroceryItem $groceryItem): JsonResponse
    {
        $this->groceryItemService->delete($groceryItem);

        return response()->json(['message' => 'Grocery item deleted successfully'], 200);
    }
}
