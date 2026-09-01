<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    /**
     * Get a human-readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::CONFIRMED => 'Confirmed',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
        };
    }

    /**
     * Get all status values for validation.
     */
    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
