<?php

namespace App\Services;

use App\Contracts\Repositories\CustomerProfileRepositoryInterface;
use App\Contracts\Repositories\GroceryItemRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Models\GroceryItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class CustomerService
{
    public function __construct(
        protected GroceryItemRepositoryInterface $groceryItemRepository,
        protected OrderRepositoryInterface $orderRepository,
        protected CustomerProfileRepositoryInterface $customerProfileRepository
    ) {}

    public function browseProducts(array $filters = []): mixed
    {
        $cacheKey = 'products.'.md5(json_encode($filters));

        return Cache::remember($cacheKey, 60, function () use ($filters) {
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
        });
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
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->customerProfileRepository->update($user, $data);
    }
}
