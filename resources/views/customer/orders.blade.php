@extends('layouts.app')

@section('title', 'My Orders')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">My Orders</h1>
</div>

<!-- Orders List -->
<div class="space-y-4" id="orders-list">
    <!-- Orders loaded via JavaScript -->
</div>

<!-- Order Details Modal -->
<div id="orderModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-2xl w-full mx-4">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold">Order Details</h2>
            <button onclick="closeOrderModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>

        <div id="orderDetails" class="mb-6 max-h-96 overflow-y-auto">
            <!-- Order details loaded here -->
        </div>

        <div class="flex justify-end">
            <button type="button" onclick="closeOrderModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                Close
            </button>
        </div>
    </div>
</div>

<script>
    const token = localStorage.getItem('token');

    document.addEventListener('DOMContentLoaded', loadOrders);

    async function loadOrders() {
        try {
            const response = await fetch('/api/customer/orders?per_page=50', {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            const data = await response.json();

            const container = document.getElementById('orders-list');
            container.innerHTML = '';

            if (!data.data || data.data.length === 0) {
                container.innerHTML = '<p class="text-gray-600 text-center py-8">No orders yet. <a href="/customer/products" class="text-green-600 hover:text-green-800">Start shopping!</a></p>';
                return;
            }

            data.data.forEach(order => {
                const date = new Date(order.created_at).toLocaleDateString();
                const card = `
                    <div class="bg-white rounded-lg shadow p-4 hover:shadow-lg transition">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Order #${order.id}</h3>
                                <p class="text-sm text-gray-600">${date}</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold ${getStatusColor(order.status)}">
                                ${capitalizeStatus(order.status)}
                            </span>
                        </div>
                        <div class="mt-3 flex justify-between items-end">
                            <div>
                                <p class="text-sm text-gray-600">${order.items ? order.items.length : 0} item(s)</p>
                            </div>
                            <div class="flex items-end space-x-3">
                                <p class="text-lg font-bold text-gray-900">$${parseFloat(order.total_amount).toFixed(2)}</p>
                                <button onclick="viewOrderDetails(${order.id})" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                                    View Details
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                container.innerHTML += card;
            });
        } catch (error) {
            console.error('Error loading orders:', error);
            document.getElementById('orders-list').innerHTML = '<p class="text-red-600">Error loading orders</p>';
        }
    }

    function getStatusColor(status) {
        const colors = {
            'pending': 'bg-yellow-100 text-yellow-800',
            'confirmed': 'bg-blue-100 text-blue-800',
            'delivered': 'bg-green-100 text-green-800',
            'cancelled': 'bg-red-100 text-red-800'
        };
        return colors[status] || 'bg-gray-100 text-gray-800';
    }

    function capitalizeStatus(status) {
        return status.charAt(0).toUpperCase() + status.slice(1);
    }

    async function viewOrderDetails(orderId) {
        try {
            const response = await fetch(`/api/customer/orders/${orderId}`, {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            const data = await response.json();
            const order = data.data;

            let itemsHtml = '<div class="mb-4"><h3 class="font-semibold mb-2">Order Items:</h3>';
            order.items.forEach(item => {
                itemsHtml += `
                    <div class="flex justify-between py-2 border-b">
                        <div>
                            <p class="font-medium">${item.product_name}</p>
                            <p class="text-sm text-gray-600">Qty: ${item.quantity}</p>
                        </div>
                        <p class="font-semibold">$${parseFloat(item.subtotal).toFixed(2)}</p>
                    </div>
                `;
            });
            itemsHtml += '</div>';

            const detailsHtml = `
                <div class="space-y-2">
                    <p><span class="font-medium">Order ID:</span> #${order.id}</p>
                    <p><span class="font-medium">Order Date:</span> ${new Date(order.created_at).toLocaleString()}</p>
                    <p><span class="font-medium">Status:</span> <span class="font-semibold ${getStatusColor(order.status).split(' ')[0] + ' ' + getStatusColor(order.status).split(' ')[1]}">${capitalizeStatus(order.status)}</span></p>
                    ${itemsHtml}
                    <div class="border-t-2 pt-4">
                        <p class="flex justify-between text-lg font-bold">
                            <span>Total:</span>
                            <span>$${parseFloat(order.total_amount).toFixed(2)}</span>
                        </p>
                    </div>
                </div>
            `;

            document.getElementById('orderDetails').innerHTML = detailsHtml;
            document.getElementById('orderModal').classList.remove('hidden');
        } catch (error) {
            console.error('Error loading order details:', error);
            alert('Error loading order details');
        }
    }

    function closeOrderModal() {
        document.getElementById('orderModal').classList.add('hidden');
    }
</script>
@endsection
