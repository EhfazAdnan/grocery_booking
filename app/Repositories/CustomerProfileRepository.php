<?php

namespace App\Repositories;

use App\Contracts\Repositories\CustomerProfileRepositoryInterface;
use App\Models\User;

class CustomerProfileRepository implements CustomerProfileRepositoryInterface
{
    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user->fresh();
    }
}
