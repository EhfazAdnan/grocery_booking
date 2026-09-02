<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'status' => $this->status?->value ?? $this->status,
            'total_amount' => $this->formatDecimal($this->total_amount),
            'status_changed_at' => $this->status_changed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'customer' => $this->whenLoaded('user', fn() => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
            ]),
            'user' => $this->whenLoaded('user', fn() => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
            ]),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
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
