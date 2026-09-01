<?php

namespace App\Services;

use App\Contracts\Repositories\GroceryItemRepositoryInterface;
use App\Models\GroceryItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class GroceryItemService
{
    public function __construct(
        protected GroceryItemRepositoryInterface $repository
    ) {}

    public function list(): LengthAwarePaginator
    {
        return $this->repository->paginate(15);
    }

    public function create(array $data): GroceryItem
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|gt:0|decimal:0,2',
            'stock' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $item = $this->repository->create($validator->validated());

        Log::info('Grocery item created', [
            'item_id' => $item->id,
            'name' => $item->name,
            'price' => $item->price,
            'stock' => $item->stock,
        ]);

        return $item;
    }

    public function update(GroceryItem $groceryItem, array $data): GroceryItem
    {
        $validator = Validator::make($data, [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'sometimes|required|numeric|gt:0|decimal:0,2',
            'stock' => 'sometimes|required|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $item = $this->repository->update($groceryItem, $validator->validated());

        Log::info('Grocery item updated', [
            'item_id' => $item->id,
            'name' => $item->name,
            'updated_fields' => array_keys($validator->validated()),
        ]);

        return $item;
    }

    public function updateStock(GroceryItem $groceryItem, array $data): GroceryItem
    {
        $validator = Validator::make($data, [
            'stock' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $item = $this->repository->update($groceryItem, ['stock' => $validator->validated()['stock']]);

        Log::info('Grocery item stock updated', [
            'item_id' => $item->id,
            'name' => $item->name,
            'new_stock' => $item->stock,
        ]);

        return $item;
    }

    public function delete(GroceryItem $groceryItem): bool
    {
        $itemId = $groceryItem->id;
        $itemName = $groceryItem->name;

        $result = $this->repository->delete($groceryItem);

        Log::info('Grocery item deleted', [
            'item_id' => $itemId,
            'name' => $itemName,
        ]);

        return $result;
    }
}
