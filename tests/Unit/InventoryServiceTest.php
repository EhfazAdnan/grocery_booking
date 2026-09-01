<?php

namespace Tests\Unit;

use App\Models\GroceryItem;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected InventoryService $inventoryService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->inventoryService = app(InventoryService::class);
    }

    /**
     * Task 39: InventoryService.decrementStock() logic.
     */
    public function test_decrement_stock_reduces_quantity(): void
    {
        $item = GroceryItem::factory()->create(['stock' => 10]);

        $this->inventoryService->decrementStock($item->id, 3);

        $this->assertDatabaseHas('grocery_items', [
            'id' => $item->id,
            'stock' => 7,
        ]);
    }

    public function test_decrement_stock_to_zero_is_allowed(): void
    {
        $item = GroceryItem::factory()->create(['stock' => 5]);

        $this->inventoryService->decrementStock($item->id, 5);

        $this->assertDatabaseHas('grocery_items', [
            'id' => $item->id,
            'stock' => 0,
        ]);
    }

    public function test_decrement_stock_throws_on_insufficient_stock(): void
    {
        $item = GroceryItem::factory()->create(['stock' => 2]);

        $this->expectException(ValidationException::class);

        $this->inventoryService->decrementStock($item->id, 3);
    }

    public function test_decrement_stock_does_not_make_stock_negative(): void
    {
        $item = GroceryItem::factory()->create(['stock' => 1]);

        try {
            $this->inventoryService->decrementStock($item->id, 5);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            // Expected
        }

        // Stock should remain unchanged
        $this->assertDatabaseHas('grocery_items', [
            'id' => $item->id,
            'stock' => 1,
        ]);
    }

    public function test_decrement_stock_throws_on_non_existent_product(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->inventoryService->decrementStock(99999, 1);
    }

    public function test_multiple_decrements_accumulate(): void
    {
        $item = GroceryItem::factory()->create(['stock' => 10]);

        $this->inventoryService->decrementStock($item->id, 2);
        $this->inventoryService->decrementStock($item->id, 3);

        $this->assertDatabaseHas('grocery_items', [
            'id' => $item->id,
            'stock' => 5,
        ]);
    }
}
