<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's user accounts.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@grocery.com',
            'password' => Hash::make('Password123'),
            'role' => 'admin',
        ]);

        // Create 5 customer users
        $customers = [
            ['name' => 'Alice Johnson', 'email' => 'alice@example.com'],
            ['name' => 'Bob Smith', 'email' => 'bob@example.com'],
            ['name' => 'Carol Williams', 'email' => 'carol@example.com'],
            ['name' => 'David Brown', 'email' => 'david@example.com'],
            ['name' => 'Eva Davis', 'email' => 'eva@example.com'],
        ];

        foreach ($customers as $customer) {
            User::create([
                'name' => $customer['name'],
                'email' => $customer['email'],
                'password' => Hash::make('Password123'),
                'role' => 'customer',
            ]);
        }
    }
}
