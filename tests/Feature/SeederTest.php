<?php

namespace Tests\Feature;

use App\Models\GroceryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Task 48: Verify seeders create correct data.
     */
    public function test_user_seeder_creates_admin_and_customers(): void
    {
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\UserSeeder']);

        // Verify admin user
        $this->assertDatabaseHas('users', [
            'email' => 'admin@grocery.com',
            'role' => 'admin',
        ]);

        // Verify 5 customer users
        $customers = [
            'alice@example.com',
            'bob@example.com',
            'carol@example.com',
            'david@example.com',
            'eva@example.com',
        ];

        foreach ($customers as $email) {
            $this->assertDatabaseHas('users', [
                'email' => $email,
                'role' => 'customer',
            ]);
        }

        // Verify total count: 1 admin + 5 customers = 6
        $this->assertEquals(6, User::count());
    }

    public function test_grocery_item_seeder_creates_15_products(): void
    {
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\GroceryItemSeeder']);

        // Verify 15 products created
        $this->assertEquals(15, GroceryItem::count());

        // Verify specific products
        $this->assertDatabaseHas('grocery_items', [
            'name' => 'Fresh Red Apples',
            'price' => '3.99',
            'stock' => 50,
        ]);

        $this->assertDatabaseHas('grocery_items', [
            'name' => 'Whole Milk',
            'price' => '4.29',
            'stock' => 30,
        ]);

        // Verify all products are active
        $this->assertEquals(15, GroceryItem::where('is_active', true)->count());
    }

    public function test_database_seeder_calls_all_seeders(): void
    {
        $this->artisan('db:seed');

        // Verify users seeded
        $this->assertEquals(6, User::count());

        // Verify grocery items seeded
        $this->assertEquals(15, GroceryItem::count());
    }

    public function test_seeded_admin_can_login(): void
    {
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\UserSeeder']);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@grocery.com',
            'password' => 'Password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['access_token']);
    }

    public function test_seeded_customer_can_login(): void
    {
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\UserSeeder']);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'alice@example.com',
            'password' => 'Password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['access_token']);
    }
}
