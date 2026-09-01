<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class OrderStatusService
{
    /**
     * Change the status of an order and log the change.
     *
     * @param Order $order
     * @param OrderStatus $newStatus
     * @return Order
     *
     * @throws ValidationException if status transition is invalid
     */
    public function changeStatus(Order $order, OrderStatus $newStatus): Order
    {
        // Validate status transition if needed
        $this->validateStatusTransition($order->status, $newStatus);

        $oldStatus = $order->status;

        // Update order status and log timestamp
        $order->update([
            'status' => $newStatus,
            'status_changed_at' => now(),
        ]);

        // Log the status change
        Log::info('Order status changed', [
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'old_status' => $oldStatus->value,
            'new_status' => $newStatus->value,
            'changed_at' => now()->toDateTimeString(),
        ]);

        return $order->fresh();
    }

    /**
     * Validate that a status transition is allowed.
     * (Can be extended with more complex rules)
     *
     * @param OrderStatus $currentStatus
     * @param OrderStatus $newStatus
     * @return void
     *
     * @throws ValidationException
     */
    private function validateStatusTransition(OrderStatus $currentStatus, OrderStatus $newStatus): void
    {
        // Prevent changing status of already delivered orders (except to cancelled)
        if ($currentStatus === OrderStatus::DELIVERED && $newStatus !== OrderStatus::CANCELLED) {
            throw ValidationException::withMessages([
                'status' => ['Cannot change status of a delivered order.'],
            ]);
        }

        // Prevent changing status of cancelled orders
        if ($currentStatus === OrderStatus::CANCELLED) {
            throw ValidationException::withMessages([
                'status' => ['Cannot change status of a cancelled order.'],
            ]);
        }

        // Prevent transitioning to the same status
        if ($currentStatus === $newStatus) {
            throw ValidationException::withMessages([
                'status' => ['Order is already in this status.'],
            ]);
        }
    }

    /**
     * Get all available status transitions.
     *
     * @return array
     */
    public function getAvailableStatuses(): array
    {
        return OrderStatus::values();
    }
}
