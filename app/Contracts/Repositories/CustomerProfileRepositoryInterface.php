<?php

namespace App\Contracts\Repositories;

use App\Models\User;

interface CustomerProfileRepositoryInterface
{
    public function update(User $user, array $data): User;
}
