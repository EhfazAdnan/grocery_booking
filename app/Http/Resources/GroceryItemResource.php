<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroceryItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->formatDecimal($this->price),
            'stock' => $this->stock,
            'is_active' => $this->is_active,
            'stock_status' => $this->getStockStatus(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    private function formatDecimal($value): ?string
    {
        if ($value === null) {
            return null;
        }

        return bcadd((string) (float) $value, '0', 2);
    }

    private function getStockStatus(): string
    {
        $stock = (int) ($this->stock ?? 0);

        if ($stock > 10) {
            return 'in_stock';
        }

        if ($stock > 0) {
            return 'low_stock';
        }

        return 'out_of_stock';
    }
}
