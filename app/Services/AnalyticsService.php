<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Get revenue analytics for a date range.
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getRevenue(?string $startDate = null, ?string $endDate = null): array
    {
        $query = Order::query();

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $total = $query->sum('total_amount');
        $count = $query->count();
        $average = $count > 0 ? $total / $count : 0;

        return [
            'total_revenue' => (float) $total,
            'order_count' => $count,
            'average_order_value' => (float) number_format($average, 2),
            'date_range' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
        ];
    }

    /**
     * Get top products by order count.
     *
     * @param int $limit
     * @param string|null $startDate
     * @param string|null $endDate
     * @return Collection
     */
    public function getTopProducts(int $limit = 10, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = OrderItem::query()
            ->selectRaw('grocery_items.id, grocery_items.name, COUNT(order_items.id) as order_count, SUM(order_items.quantity) as total_quantity, SUM(order_items.subtotal) as total_revenue')
            ->join('grocery_items', 'order_items.grocery_item_id', '=', 'grocery_items.id')
            ->groupBy('grocery_items.id', 'grocery_items.name')
            ->orderByDesc('order_count');

        if ($startDate) {
            $query->whereDate('order_items.created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('order_items.created_at', '<=', $endDate);
        }

        return $query->limit($limit)->get()->map(fn($item) => [
            'product_id' => $item->id,
            'product_name' => $item->name,
            'order_count' => $item->order_count,
            'total_quantity' => $item->total_quantity,
            'total_revenue' => (float) $item->total_revenue,
        ]);
    }

    /**
     * Get order count analytics by time period.
     *
     * @param string $period - 'daily', 'weekly', or 'monthly'
     * @param string|null $startDate
     * @param string|null $endDate
     * @return Collection
     */
    public function getOrderCount(string $period = 'daily', ?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = Order::query();

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        // Use database-agnostic approach
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // SQLite date functions
            $selectFormat = match ($period) {
                'weekly' => "strftime('%Y-W%W', created_at) as period",
                'monthly' => "strftime('%Y-%m', created_at) as period",
                default => "date(created_at) as period",
            };

            $groupFormat = match ($period) {
                'weekly' => "strftime('%Y-W%W', created_at)",
                'monthly' => "strftime('%Y-%m', created_at)",
                default => "date(created_at)",
            };
        } else {
            // MySQL date functions
            $selectFormat = match ($period) {
                'weekly' => "DATE_FORMAT(created_at, '%Y-W%V') as period",
                'monthly' => "DATE_FORMAT(created_at, '%Y-%m') as period",
                default => "DATE_FORMAT(created_at, '%Y-%m-%d') as period",
            };

            $groupFormat = match ($period) {
                'weekly' => "DATE_FORMAT(created_at, '%Y-W%V')",
                'monthly' => "DATE_FORMAT(created_at, '%Y-%m')",
                default => "DATE_FORMAT(created_at, '%Y-%m-%d')",
            };
        }

        return $query
            ->selectRaw("$selectFormat, COUNT(*) as count, SUM(total_amount) as revenue")
            ->groupByRaw($groupFormat)
            ->orderBy('period')
            ->get()
            ->map(fn($item) => [
                'period' => $item->period,
                'order_count' => $item->count,
                'revenue' => (float) $item->revenue,
            ]);
    }
}
