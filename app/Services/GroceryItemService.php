<?php

namespace App\Services;

use App\Contracts\Repositories\GroceryItemRepositoryInterface;
use App\Models\GroceryItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

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
        $item = $this->repository->create($data);

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
        $item = $this->repository->update($groceryItem, $data);

        Log::info('Grocery item updated', [
            'item_id' => $item->id,
            'name' => $item->name,
            'updated_fields' => array_keys($data),
        ]);

        return $item;
    }

    public function updateStock(GroceryItem $groceryItem, array $data): GroceryItem
    {
        $item = $this->repository->update($groceryItem, ['stock' => $data['stock']]);

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
