@extends('layouts.app')

@section('title', __('Checkout'))

@section('content')
<div id="toast" class="hidden fixed top-5 right-5 z-50 px-4 py-3 rounded-lg shadow-lg text-white"></div>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">{{ __('Checkout') }}</h1>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Cart Summary -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-2xl font-bold mb-4">{{ __('Order Summary') }}</h2>

            <div id="cartSummary" class="mb-6">
                <!-- Cart items displayed here -->
            </div>

            <div class="border-t-2 pt-4">
                <p class="flex justify-between text-lg font-bold">
                    <span>{{ __('Subtotal:') }}</span>
                    <span>$<span id="subtotal">0.00</span></span>
                </p>
                <p class="flex justify-between text-gray-600 mt-2">
                    <span>{{ __('Tax (10%):') }}</span>
                    <span>$<span id="tax">0.00</span></span>
                </p>
                <p class="flex justify-between text-2xl font-bold mt-4 pt-4 border-t">
                    <span>{{ __('Total:') }}</span>
                    <span>$<span id="total">0.00</span></span>
                </p>
            </div>
        </div>
    </div>

    <!-- Checkout Form -->
    <div class="bg-white rounded-lg shadow p-6 h-fit">
        <h2 class="text-2xl font-bold mb-4">{{ __('Complete Order') }}</h2>

        <form id="checkoutForm" onsubmit="handleCheckout(event)">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Delivery Address') }}</label>
                <textarea id="address" rows="3" required
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                          placeholder="{{ __('Enter your delivery address') }}"></textarea>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Phone Number') }}</label>
                <input type="tel" id="phone" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                       placeholder="(555) 123-4567">
            </div>

            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" id="termsAccepted" required
                           class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                    <span class="ml-2 text-sm text-gray-700">{{ __('I agree to the terms and conditions') }}</span>
                </label>
            </div>

            <button type="submit" id="submitBtn"
                    class="w-full px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition disabled:opacity-50"
                    disabled>
                {{ __('Place Order') }}
            </button>
        </form>

        <button type="button" onclick="continueShopping()"
                class="w-full mt-3 px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition">
            {{ __('Continue Shopping') }}
        </button>
    </div>
</div>

<script>
    const token = localStorage.getItem('token');
    let cart = JSON.parse(localStorage.getItem('cart')) || {};
    let allProducts = [];

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

    document.addEventListener('DOMContentLoaded', () => {
        if (!token) {
            showToast(t('Please login first'), 'error');
            window.location.href = '/login';
            return;
        }

        if (Object.keys(cart).length === 0) {
            showToast(t('Your cart is empty'), 'error');
            window.location.href = '/customer/products';
            return;
        }

        loadProducts();
        updateTermsButton();
    });

    async function loadProducts() {
        try {
            const response = await fetch('/api/customer/products?per_page=100');
            const data = await response.json();
            allProducts = data.data || [];
            renderCartSummary();
        } catch (error) {
            console.error('Error loading products:', error);
        }
    }

    function renderCartSummary() {
        let subtotal = 0;
        let itemsHtml = '';

        Object.entries(cart).forEach(([productId, qty]) => {
            const product = allProducts.find(p => p.id == productId);
            if (product) {
                const subtotalItem = product.price * qty;
                subtotal += subtotalItem;
                itemsHtml += `
                    <div class="flex justify-between py-3 border-b">
                        <div>
                            <p class="font-medium">${product.name}</p>
                            <p class="text-sm text-gray-600">${t('Qty: :qty', { qty: qty })}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold">$${parseFloat(subtotalItem).toFixed(2)}</p>
                            <button type="button" onclick="removeFromCart(${productId})"
                                    class="text-red-600 hover:text-red-800 text-sm">${t('Remove')}</button>
                        </div>
                    </div>
                `;
            }
        });

        document.getElementById('cartSummary').innerHTML = itemsHtml;

        const tax = subtotal * 0.10;
        const total = subtotal + tax;

        document.getElementById('subtotal').innerText = parseFloat(subtotal).toFixed(2);
        document.getElementById('tax').innerText = parseFloat(tax).toFixed(2);
        document.getElementById('total').innerText = parseFloat(total).toFixed(2);
    }

    function removeFromCart(productId) {
        delete cart[productId];
        localStorage.setItem('cart', JSON.stringify(cart));

        if (Object.keys(cart).length === 0) {
            alert('Your cart is empty');
            window.location.href = '/customer/products';
        } else {
            renderCartSummary();
        }
    }

    function updateTermsButton() {
        const termsCheckbox = document.getElementById('termsAccepted');
        const submitBtn = document.getElementById('submitBtn');

        termsCheckbox.addEventListener('change', () => {
            submitBtn.disabled = !termsCheckbox.checked;
        });
    }

    async function placeOrder(event) {
        return handleCheckout(event);
    }

    async function handleCheckout(event) {
        event.preventDefault();

        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.textContent = t('Placing order...');

        const items = Object.entries(cart).map(([productId, quantity]) => ({
            product_id: parseInt(productId),
            quantity: quantity
        }));

        try {
            const response = await fetch('/api/customer/orders', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({ items })
            });

            if (response.status === 201) {
                const data = await response.json();
                const orderId = data.data.id;

                localStorage.removeItem('cart');
                showToast(t('Order placed successfully!'), 'success');
                window.location.href = `/customer/order-confirmation?id=${orderId}`;
            } else {
                const error = await response.json();
                showToast(t('Error: :details', { details: JSON.stringify(error.errors || error.message) }), 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = t('Place Order');
            }
        } catch (error) {
            console.error('Error placing order:', error);
            showToast(t('Error placing order. Please try again.'), 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = t('Place Order');
        }
    }

    function continueShopping() {
        window.location.href = '/customer/products';
    }
</script>
@endsection
