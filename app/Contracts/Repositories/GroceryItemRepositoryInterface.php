<?php

namespace App\Contracts\Repositories;

use App\Models\GroceryItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface GroceryItemRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): GroceryItem;

    public function update(GroceryItem $groceryItem, array $data): GroceryItem;

    public function delete(GroceryItem $groceryItem): bool;
}
