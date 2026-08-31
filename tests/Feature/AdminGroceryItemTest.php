<?php

namespace Tests\Feature;

use App\Models\GroceryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\JWTGuard;

class AdminGroceryItemTest extends TestCase
{
    use RefreshDatabase;

    private function jwtGuard(): JWTGuard
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');

        return $guard;
    }

    protected function adminToken(): string
    {
        $admin = User::factory()->admin()->create();

        return $this->jwtGuard()->login($admin);
    }

    public function test_admin_can_list_grocery_items(): void
    {
        GroceryItem::factory()->count(3)->create();

        $response = $this->getJson('/api/admin/grocery-items', [
            'Authorization' => 'Bearer '.$this->adminToken(),
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'name',
                    'price',
                    'stock',
                    'is_active',
                ]],
            ]);
    }

    public function test_admin_can_create_grocery_item(): void
    {
        $response = $this->postJson('/api/admin/grocery-items', [
            'name' => 'Banana',
            'description' => 'Fresh daily banana',
            'price' => 95.50,
            'stock' => 12,
            'is_active' => true,
        ], [
            'Authorization' => 'Bearer '.$this->adminToken(),
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Banana')
            ->assertJsonPath('data.stock', 12);

        $this->assertDatabaseHas('grocery_items', ['name' => 'Banana']);
    }

    public function test_admin_can_update_grocery_item(): void
    {
        $item = GroceryItem::factory()->create([
            'name' => 'Orange',
            'price' => 80,
            'stock' => 5,
        ]);

        $response = $this->putJson('/api/admin/grocery-items/'.$item->id, [
            'name' => 'Orange Premium',
            'description' => 'Juicy orange',
            'price' => 90,
            'stock' => 10,
            'is_active' => true,
        ], [
            'Authorization' => 'Bearer '.$this->adminToken(),
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Orange Premium')
            ->assertJsonPath('data.price', '90.00');

        $this->assertDatabaseHas('grocery_items', ['id' => $item->id, 'name' => 'Orange Premium']);
    }

    public function test_admin_can_update_stock(): void
    {
        $item = GroceryItem::factory()->create([
            'stock' => 8,
        ]);

        $response = $this->patchJson('/api/admin/grocery-items/'.$item->id.'/stock', [
            'stock' => 15,
        ], [
            'Authorization' => 'Bearer '.$this->adminToken(),
        ]);

        $response->assertOk()
            ->assertJsonPath('data.stock', 15);
    }

    public function test_admin_can_delete_grocery_item(): void
    {
        $item = GroceryItem::factory()->create();

        $response = $this->deleteJson('/api/admin/grocery-items/'.$item->id, [], [
            'Authorization' => 'Bearer '.$this->adminToken(),
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'Grocery item deleted successfully']);

        $this->assertSoftDeleted('grocery_items', ['id' => $item->id]);
    }

    public function test_customer_is_forbidden_from_admin_routes(): void
    {
        $customer = User::factory()->customer()->create();
        $token = $this->jwtGuard()->login($customer);

        $response = $this->getJson('/api/admin/grocery-items', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(403);
    }
}
