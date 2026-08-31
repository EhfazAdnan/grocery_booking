<?php

namespace App\Repositories;

use App\Contracts\Repositories\GroceryItemRepositoryInterface;
use App\Models\GroceryItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GroceryItemRepository implements GroceryItemRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return GroceryItem::query()->orderByDesc('created_at')->paginate($perPage);
    }

    public function create(array $data): GroceryItem
    {
        return GroceryItem::create($data);
    }

    public function update(GroceryItem $groceryItem, array $data): GroceryItem
    {
        $groceryItem->update($data);

        return $groceryItem->fresh();
    }

    public function delete(GroceryItem $groceryItem): bool
    {
        return (bool) $groceryItem->delete();
    }
}
