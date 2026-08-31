<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\GroceryItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GroceryItemController
{
    public function index(): JsonResponse
    {
        $items = GroceryItem::query()->orderByDesc('created_at')->paginate(15);

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
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item = GroceryItem::create($validator->validated());

        return response()->json(['data' => $item], 201);
    }

    public function show(GroceryItem $groceryItem): JsonResponse
    {
        return response()->json(['data' => $groceryItem], 200);
    }

    public function update(Request $request, GroceryItem $groceryItem): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $groceryItem->update($validator->validated());

        return response()->json(['data' => $groceryItem->fresh()], 200);
    }

    public function updateStock(Request $request, GroceryItem $groceryItem): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'stock' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $groceryItem->update(['stock' => $validator->validated()['stock']]);

        return response()->json(['data' => $groceryItem->fresh()], 200);
    }

    public function destroy(GroceryItem $groceryItem): JsonResponse
    {
        $groceryItem->delete();

        return response()->json(['message' => 'Grocery item deleted successfully'], 200);
    }
}
