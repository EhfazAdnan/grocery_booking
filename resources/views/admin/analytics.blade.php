@extends('layouts.app')

@section('title', 'Admin - Analytics')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Analytics Dashboard</h1>
</div>

<!-- Analytics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Revenue Card -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">Total Revenue</h3>
        <p class="text-3xl font-bold text-gray-900">$<span id="totalRevenue">0.00</span></p>
        <p class="text-xs text-gray-600 mt-2">All time</p>
    </div>

    <!-- Orders Card -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">Total Orders</h3>
        <p class="text-3xl font-bold text-gray-900" id="totalOrders">0</p>
        <p class="text-xs text-gray-600 mt-2">Completed orders</p>
    </div>

    <!-- Avg Order Value -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">Avg Order Value</h3>
        <p class="text-3xl font-bold text-gray-900">$<span id="avgOrderValue">0.00</span></p>
        <p class="text-xs text-gray-600 mt-2">Average per order</p>
    </div>

    <!-- Top Product -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">Top Product</h3>
        <p class="text-lg font-bold text-gray-900" id="topProduct">—</p>
        <p class="text-xs text-gray-600 mt-2">Most ordered</p>
    </div>
</div>

<!-- Revenue by Period -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Daily Orders -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Orders by Day</h3>
            <select id="dailyFilter" class="px-3 py-1 border border-gray-300 rounded text-sm" onchange="loadAnalytics()">
                <option value="7">Last 7 days</option>
                <option value="30">Last 30 days</option>
            </select>
        </div>
        <div id="dailyChart" class="h-48 flex items-end justify-around">
            <!-- Chart data -->
        </div>
    </div>

    <!-- Top Products -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Top 5 Products</h3>
        <div id="topProductsList" class="space-y-3">
            <!-- Products list -->
        </div>
    </div>
</div>

<script>
    const token = localStorage.getItem('token');

    document.addEventListener('DOMContentLoaded', loadAnalytics);

    async function loadAnalytics() {
        try {
            // Load Revenue
            const revenueResponse = await fetch('/api/admin/analytics/revenue', {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            const revenueData = await revenueResponse.json();
            const revenue = revenueData.data;

            document.getElementById('totalRevenue').innerText = parseFloat(revenue.total_revenue).toFixed(2);
            document.getElementById('totalOrders').innerText = revenue.order_count;
            document.getElementById('avgOrderValue').innerText = parseFloat(revenue.average_order_value).toFixed(2);

            // Load Top Products
            const productsResponse = await fetch('/api/admin/analytics/top-products?limit=5', {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            const productsData = await productsResponse.json();
            const products = productsData.data;

            if (products.length > 0) {
                document.getElementById('topProduct').innerText = products[0].product_name;

                let productsHtml = '';
                products.forEach((product, index) => {
                    productsHtml += `
                        <div class="flex justify-between items-center py-2 border-b">
                            <div>
                                <p class="font-medium text-gray-900">${index + 1}. ${product.product_name}</p>
                                <p class="text-xs text-gray-600">${product.order_count} orders</p>
                            </div>
                            <p class="font-semibold text-gray-900">$${parseFloat(product.total_revenue).toFixed(2)}</p>
                        </div>
                    `;
                });
                document.getElementById('topProductsList').innerHTML = productsHtml;
            }

            // Load Order Count
            const period = document.getElementById('dailyFilter')?.value || '7';
            const countResponse = await fetch(`/api/admin/analytics/order-count?period=daily`, {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            const countData = await countResponse.json();
            const counts = countData.data;

            // Simple bar chart representation
            let chartHtml = '';
            const maxCount = Math.max(...counts.map(c => c.order_count), 1);

            counts.slice(-7).forEach(item => {
                const height = (item.order_count / maxCount) * 100;
                chartHtml += `
                    <div class="flex flex-col items-center">
                        <div class="bg-green-500 rounded-t" style="width: 30px; height: ${height}%;"></div>
                        <p class="text-xs mt-1 text-gray-600">${item.order_count}</p>
                    </div>
                `;
            });
            document.getElementById('dailyChart').innerHTML = chartHtml;

        } catch (error) {
            console.error('Error loading analytics:', error);
        }
    }
</script>
@endsection
