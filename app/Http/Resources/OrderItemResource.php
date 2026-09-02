<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'product_id' => $this->grocery_item_id,
            'grocery_item_id' => $this->grocery_item_id,
            'quantity' => $this->quantity,
            'unit_price' => $this->formatDecimal($this->unit_price),
            'subtotal' => $this->formatDecimal($this->subtotal),
            'product_name' => $this->whenLoaded('groceryItem', fn() => $this->groceryItem?->name),
        ];
    }

    private function formatDecimal($value): ?string
    {
        if ($value === null) {
            return null;
        }

        return bcadd((string) (float) $value, '0', 2);
    }
}
