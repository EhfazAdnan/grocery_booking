@extends('layouts.app')

@section('title', __('Admin - Orders Management'))

@section('content')
<div id="toast" class="hidden fixed top-5 right-5 z-50 px-4 py-3 rounded-lg shadow-lg text-white"></div>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">{{ __('Orders Management') }}</h1>
</div>

<!-- Orders Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-100 border-b">
            <tr>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">{{ __('Order ID') }}</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">{{ __('Customer') }}</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">{{ __('Date') }}</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">{{ __('Total') }}</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">{{ __('Status') }}</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y" id="orders-list">
            <!-- Orders loaded via JavaScript -->
        </tbody>
    </table>
</div>

<!-- Order Details Modal -->
<div id="orderModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-2xl w-full mx-4">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold">{{ __('Order Details') }}</h2>
            <button onclick="closeOrderModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>

        <div id="orderDetails" class="mb-6 max-h-96 overflow-y-auto">
            <!-- Order details loaded here -->
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Order Status') }}</label>
            <select id="orderStatus" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                <option value="pending">{{ __('Pending') }}</option>
                <option value="confirmed">{{ __('Confirmed') }}</option>
                <option value="delivered">{{ __('Delivered') }}</option>
                <option value="cancelled">{{ __('Cancelled') }}</option>
            </select>
        </div>

        <div class="flex justify-end space-x-3">
            <button type="button" onclick="closeOrderModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                {{ __('Close') }}
            </button>
            <button type="button" onclick="updateOrderStatus()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                {{ __('Update Status') }}
            </button>
        </div>
    </div>
</div>

<script>
    const token = localStorage.getItem('token');
    let currentOrderId = null;

    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        if (!toast) return;

        const colors = {
            success: 'bg-green-600',
            error: 'bg-red-600',
            info: 'bg-blue-600'
        };

        toast.textContent = message;
        toast.className = `fixed top-5 right-5 z-50 px-4 py-3 rounded-lg shadow-lg text-white ${colors[type] || colors.success}`;
        toast.classList.remove('hidden');

        setTimeout(() => toast.classList.add('hidden'), 2500);
    }

    document.addEventListener('DOMContentLoaded', loadOrders);

    async function loadOrders() {
        try {
            const response = await fetch('/api/admin/orders', {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            const data = await response.json();

            const tbody = document.getElementById('orders-list');
            tbody.innerHTML = '';

            if (data.data && Array.isArray(data.data)) {
                data.data.forEach(order => {
                    const date = new Date(order.created_at).toLocaleDateString();
                    const row = `
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900">#${order.id}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">${order.user.email}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">${date}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 font-semibold">$${parseFloat(order.total_amount).toFixed(2)}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold ${getStatusColor(order.status)}">
                                    ${t(capitalizeStatus(order.status))}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <button onclick="viewOrderDetails(${order.id})" class="text-blue-600 hover:text-blue-800">${t('View')}</button>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            }
        } catch (error) {
            console.error('Error loading orders:', error);
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
            const response = await fetch(`/api/admin/orders/${orderId}`, {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            const data = await response.json();
            const order = data.data;

            currentOrderId = orderId;
            document.getElementById('orderStatus').value = order.status;

            let itemsHtml = '<div class="mb-4"><h3 class="font-semibold mb-2">' + t('Order Items:') + '</h3>';
            order.items.forEach(item => {
                itemsHtml += `
                    <div class="flex justify-between py-2 border-b">
                        <div>
                            <p class="font-medium">${item.product_name || t('Product')}</p>
                            <p class="text-sm text-gray-600">${t('Qty: :qty', { qty: item.quantity })}</p>
                        </div>
                        <p class="font-semibold">$${parseFloat(item.subtotal).toFixed(2)}</p>
                    </div>
                `;
            });
            itemsHtml += '</div>';

            const detailsHtml = `
                <div class="space-y-2">
                    <p><span class="font-medium">${t('Order ID:')}</span> #${order.id}</p>
                    <p><span class="font-medium">${t('Customer Email:')}</span> ${order.user.email}</p>
                    <p><span class="font-medium">${t('Date:')}</span> ${new Date(order.created_at).toLocaleString()}</p>
                    ${itemsHtml}
                    <div class="border-t-2 pt-4">
                        <p class="flex justify-between text-lg font-bold">
                            <span>${t('Total:')}</span>
                            <span>$${parseFloat(order.total_amount).toFixed(2)}</span>
                        </p>
                    </div>
                </div>
            `;

            document.getElementById('orderDetails').innerHTML = detailsHtml;
            document.getElementById('orderModal').classList.remove('hidden');
        } catch (error) {
            console.error('Error loading order details:', error);
            alert(t('Error loading order details'));
        }
    }

    async function updateOrderStatus() {
        const status = document.getElementById('orderStatus').value;

        try {
            const response = await fetch(`/api/admin/orders/${currentOrderId}/status`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({ status: status })
            });

            if (response.ok) {
                closeOrderModal();
                loadOrders();
                showToast(t('Order status updated successfully.'), 'success');
            } else {
                const error = await response.json();
                showToast(t('Error: :details', { details: JSON.stringify(error.errors || error.message) }), 'error');
            }
        } catch (error) {
            console.error('Error updating status:', error);
            showToast(t('Could not update order status.'), 'error');
        }
    }

    function closeOrderModal() {
        document.getElementById('orderModal').classList.add('hidden');
        currentOrderId = null;
    }
</script>
@endsection
