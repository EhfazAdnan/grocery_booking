<?php

namespace App\Services;

use App\Contracts\Repositories\CustomerProfileRepositoryInterface;
use App\Contracts\Repositories\GroceryItemRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Models\GroceryItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CustomerService
{
    public function __construct(
        protected GroceryItemRepositoryInterface $groceryItemRepository,
        protected OrderRepositoryInterface $orderRepository,
        protected CustomerProfileRepositoryInterface $customerProfileRepository
    ) {}

    public function browseProducts(array $filters = []): mixed
    {
        $query = GroceryItem::query()->where('is_active', true);

        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%'.$filters['search'].'%');
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (! empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        return $query->orderByDesc('created_at')->paginate(15);
    }

    public function getOrderHistory(User $user): mixed
    {
        return Order::query()
            ->where('user_id', $user->id)
            ->with('items.groceryItem')
            ->orderByDesc('created_at')
            ->paginate(15);
    }

    public function profile(User $user): User
    {
        return $user->fresh();
    }

    public function updateProfile(User $user, array $data): User
    {
        $validator = Validator::make($data, [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255|unique:users,email,'.$user->id,
            'password' => 'sometimes|required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $payload = $validator->validated();

        if (isset($payload['password'])) {
            $payload['password'] = Hash::make($payload['password']);
        }

        return $this->customerProfileRepository->update($user, $payload);
    }
}
