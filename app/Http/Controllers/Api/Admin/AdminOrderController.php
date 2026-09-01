<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\AnalyticsService;
use App\Services\OrderStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdminOrderController
{
    public function __construct(
        protected OrderStatusService $statusService,
        protected AnalyticsService $analyticsService
    ) {}

    /**
     * Change order status.
     * PUT /admin/orders/{id}/status
     */
    public function changeStatus(Request $request, Order $order): JsonResponse
    {
        try {
            $validated = $request->validate([
                'status' => 'required|string|in:' . implode(',', OrderStatus::values()),
            ]);

            $newStatus = OrderStatus::from($validated['status']);
            $updatedOrder = $this->statusService->changeStatus($order, $newStatus);

            return response()->json(['data' => $updatedOrder], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * Get revenue analytics.
     * GET /admin/analytics/revenue?start_date=2026-01-01&end_date=2026-12-31
     */
    public function revenue(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $revenue = $this->analyticsService->getRevenue($startDate, $endDate);

        return response()->json(['data' => $revenue], 200);
    }

    /**
     * Get top products by order count.
     * GET /admin/analytics/top-products?limit=10&start_date=2026-01-01&end_date=2026-12-31
     */
    public function topProducts(Request $request): JsonResponse
    {
        $limit = (int) $request->query('limit', 10);
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $products = $this->analyticsService->getTopProducts($limit, $startDate, $endDate);

        return response()->json(['data' => $products], 200);
    }

    /**
     * Get order count analytics.
     * GET /admin/analytics/order-count?period=daily&start_date=2026-01-01&end_date=2026-12-31
     */
    public function orderCount(Request $request): JsonResponse
    {
        $period = $request->query('period', 'daily');

        // Validate period
        if (!in_array($period, ['daily', 'weekly', 'monthly'])) {
            return response()->json(['errors' => ['period' => ['Period must be daily, weekly, or monthly.']]], 422);
        }

        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $counts = $this->analyticsService->getOrderCount($period, $startDate, $endDate);

        return response()->json(['data' => $counts], 200);
    }
}
