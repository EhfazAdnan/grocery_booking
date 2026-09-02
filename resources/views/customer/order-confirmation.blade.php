@extends('layouts.app')

@section('title', __('Order Confirmation'))

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-8 text-center mb-6">
        <div class="mb-6">
            <span class="text-6xl">✅</span>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ __('Order Confirmed!') }}</h1>
        <p class="text-gray-600 text-lg mb-4">{{ __('Thank you for your purchase.') }}</p>

        <div id="orderInfo" class="bg-gray-50 rounded-lg p-6 mb-6 text-left">
            <!-- Order info loaded here -->
        </div>

        <div class="space-y-2">
            <p class="text-gray-700">
                <span class="font-semibold">{{ __('Order Status:') }}</span>
                <span class="text-yellow-600 font-semibold">{{ __('Pending') }}</span>
            </p>
            <p class="text-gray-600 text-sm">{{ __('You will receive an email confirmation shortly. Our team will process your order and contact you with delivery details.') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="/customer/orders" class="block px-6 py-3 bg-blue-600 text-white text-center rounded-lg hover:bg-blue-700 transition">
            {{ __('View All Orders') }}
        </a>
        <a href="/customer/products" class="block px-6 py-3 bg-green-600 text-white text-center rounded-lg hover:bg-green-700 transition">
            {{ __('Continue Shopping') }}
        </a>
    </div>
</div>

<script>
    const token = localStorage.getItem('token');
    const urlParams = new URLSearchParams(window.location.search);
    const orderId = urlParams.get('id');

    document.addEventListener('DOMContentLoaded', () => {
        if (orderId) {
            loadOrderDetails(orderId);
        }
    });

    async function loadOrderDetails(id) {
        try {
            const response = await fetch(`/api/customer/orders/${id}`, {
                headers: { 'Authorization': `Bearer ${token}` }
            });

            if (response.ok) {
                const data = await response.json();
                const order = data.data;

                let itemsHtml = '<div class="mb-4"><h3 class="font-semibold mb-2">' + t('Order Items:') + '</h3>';
                order.items.forEach(item => {
                    itemsHtml += `
                        <div class="flex justify-between py-2 border-b">
                            <div>
                                <p class="font-medium">${item.product_name}</p>
                                <p class="text-sm text-gray-600">${t('Qty: :qty', { qty: item.quantity })}</p>
                            </div>
                            <p class="font-semibold">$${parseFloat(item.subtotal).toFixed(2)}</p>
                        </div>
                    `;
                });
                itemsHtml += '</div>';

                const infoHtml = `
                    <div>
                        <p class="mb-3"><span class="font-semibold">${t('Order ID:')}</span> #${order.id}</p>
                        <p class="mb-3"><span class="font-semibold">${t('Order Date:')}</span> ${new Date(order.created_at).toLocaleString()}</p>
                        ${itemsHtml}
                        <div class="border-t-2 pt-4 mt-4">
                            <p class="flex justify-between text-lg font-bold">
                                <span>${t('Total:')}</span>
                                <span>$${parseFloat(order.total_amount).toFixed(2)}</span>
                            </p>
                        </div>
                    </div>
                `;

                document.getElementById('orderInfo').innerHTML = infoHtml;
            }
        } catch (error) {
            console.error('Error loading order:', error);
        }
    }
</script>
@endsection
