<?php

namespace App\Services;

use App\Contracts\Repositories\GroceryItemRepositoryInterface;
use App\Models\GroceryItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
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
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this->repository->create($validator->validated());
    }

    public function update(GroceryItem $groceryItem, array $data): GroceryItem
    {
        $validator = Validator::make($data, [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this->repository->update($groceryItem, $validator->validated());
    }

    public function updateStock(GroceryItem $groceryItem, array $data): GroceryItem
    {
        $validator = Validator::make($data, [
            'stock' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this->repository->update($groceryItem, ['stock' => $validator->validated()['stock']]);
    }

    public function delete(GroceryItem $groceryItem): bool
    {
        return $this->repository->delete($groceryItem);
    }
}
