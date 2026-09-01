<?php

namespace App\Services;

use App\Models\GroceryItem;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function decrementStock(int $productId, int $quantity): void
    {
        $product = GroceryItem::query()->lockForUpdate()->findOrFail($productId);

        if ($product->stock < $quantity) {
            Log::warning('Insufficient stock', [
                'product_id' => $productId,
                'product_name' => $product->name,
                'requested_quantity' => $quantity,
                'available_stock' => $product->stock,
            ]);

            throw ValidationException::withMessages([
                'inventory' => ['Insufficient stock for product: '.$product->name],
            ]);
        }

        $product->decrement('stock', $quantity);

        Log::info('Stock decremented', [
            'product_id' => $productId,
            'product_name' => $product->name,
            'quantity_decremented' => $quantity,
            'new_stock' => $product->stock,
        ]);
    }
}
