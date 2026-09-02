<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Requests\StoreGroceryItemRequest;
use App\Http\Requests\UpdateGroceryItemRequest;
use App\Http\Requests\UpdateStockRequest;
use App\Http\Resources\GroceryItemResource;
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
            'data' => GroceryItemResource::collection(collect($items->items())),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'total' => $items->total(),
            ],
        ], 200);
    }

    public function store(StoreGroceryItemRequest $request): JsonResponse
    {
        $item = $this->groceryItemService->create($request->validated());

        return response()->json(['data' => new GroceryItemResource($item)], 201);
    }

    public function show(GroceryItem $groceryItem): JsonResponse
    {
        return response()->json(['data' => new GroceryItemResource($groceryItem)], 200);
    }

    public function update(UpdateGroceryItemRequest $request, GroceryItem $groceryItem): JsonResponse
    {
        $item = $this->groceryItemService->update($groceryItem, $request->validated());

        return response()->json(['data' => new GroceryItemResource($item)], 200);
    }

    public function updateStock(UpdateStockRequest $request, GroceryItem $groceryItem): JsonResponse
    {
        $item = $this->groceryItemService->updateStock($groceryItem, $request->validated());

        return response()->json(['data' => new GroceryItemResource($item)], 200);
    }

    public function destroy(GroceryItem $groceryItem): JsonResponse
    {
        $this->groceryItemService->delete($groceryItem);

        return response()->json(['message' => 'Grocery item deleted successfully'], 200);
    }
}
