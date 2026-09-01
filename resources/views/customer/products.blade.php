@extends('layouts.app')

@section('title', 'Browse Products')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Browse Groceries</h1>
    <p class="text-gray-600 mt-2">Explore our fresh selection of products</p>
</div>

<div id="toast" class="hidden fixed top-5 right-5 z-50 px-4 py-3 rounded-lg shadow-lg text-white"></div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <input type="text" id="searchInput" placeholder="Search products..."
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                   onkeyup="filterProducts()">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Price Range</label>
            <input type="range" id="priceRange" min="0" max="1000" step="10"
                   class="w-full" onchange="filterProducts()">
            <small class="text-gray-600">Max: $<span id="priceValue">1000</span></small>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Stock Status</label>
            <select id="stockFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                    onchange="filterProducts()">
                <option value="">All</option>
                <option value="in">In Stock</option>
                <option value="low">Low Stock</option>
                <option value="out">Out of Stock</option>
            </select>
        </div>
    </div>
</div>

<!-- Products Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="products-grid">
    <!-- Products loaded via JavaScript -->
</div>

<!-- Shopping Cart Modal -->
<div id="cartModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
        <h2 class="text-2xl font-bold mb-4">Shopping Cart</h2>

        <div id="cartItems" class="mb-4 max-h-96 overflow-y-auto">
            <!-- Cart items displayed here -->
        </div>

        <div class="border-t-2 pt-4 mb-6">
            <p class="flex justify-between text-lg font-bold">
                <span>Total:</span>
                <span>$<span id="cartTotal">0.00</span></span>
            </p>
        </div>

        <div class="flex justify-end space-x-3">
            <button type="button" onclick="closeCartModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                Continue Shopping
            </button>
            <button type="button" onclick="proceedToCheckout()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                Checkout
            </button>
        </div>
    </div>
</div>

<script>
    let allProducts = [];
    let cart = JSON.parse(localStorage.getItem('cart')) || {};
    const token = localStorage.getItem('token');

    document.addEventListener('DOMContentLoaded', () => {
        loadProducts();
        updatePriceDisplay();
        updateCartCount();
    });

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

        setTimeout(() => {
            toast.classList.add('hidden');
        }, 2500);
    }

    async function loadProducts() {
        try {
            const response = await fetch('/api/customer/products?per_page=100');
            const data = await response.json();
            allProducts = data.data || [];
            renderProducts(allProducts);
            updateCartCount();
        } catch (error) {
            console.error('Error loading products:', error);
            showToast('Could not load products.', 'error');
        }
    }

    function renderProducts(products) {
        const grid = document.getElementById('products-grid');
        grid.innerHTML = '';

        products.forEach(product => {
            const status = getStockStatus(product.stock);
            const inCart = cart[product.id] || 0;

            const card = `
                <div class="bg-white rounded-lg shadow hover:shadow-lg transition overflow-hidden">
                    <div class="bg-green-100 h-32 flex items-center justify-center">
                        <span class="text-4xl">🥕</span>
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900">${product.name}</h3>
                        <p class="text-sm text-gray-600 mt-1">${product.description || 'Fresh product'}</p>
                        <div class="flex justify-between items-center mt-4">
                            <span class="text-2xl font-bold text-green-600">$${parseFloat(product.price).toFixed(2)}</span>
                            <span class="text-xs font-semibold ${getStatusColorClass(status)}">${status}</span>
                        </div>
                        <p class="text-sm text-gray-600 mt-2">Stock: ${product.stock}</p>
                        ${product.stock > 0 ? `
                            <div class="flex gap-2 mt-4">
                                <input type="number" id="qty-${product.id}" value="1" min="1" max="${product.stock}"
                                       class="flex-1 px-2 py-1 border border-gray-300 rounded text-sm">
                                <button onclick="addToCart(${product.id})"
                                        class="flex-1 bg-green-600 text-white py-1 rounded hover:bg-green-700 transition">
                                    Add ${inCart > 0 ? `(${inCart})` : ''}
                                </button>
                            </div>
                        ` : `
                            <button disabled class="w-full mt-4 bg-gray-400 text-white py-2 rounded cursor-not-allowed">
                                Out of Stock
                            </button>
                        `}
                    </div>
                </div>
            `;
            grid.innerHTML += card;
        });
    }

    function getStockStatus(stock) {
        if (stock === 0) return 'Out of Stock';
        if (stock < 5) return 'Low Stock';
        return 'In Stock';
    }

    function getStatusColorClass(status) {
        switch(status) {
            case 'In Stock': return 'bg-green-100 text-green-800';
            case 'Low Stock': return 'bg-yellow-100 text-yellow-800';
            case 'Out of Stock': return 'bg-red-100 text-red-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    }

    function filterProducts() {
        const search = document.getElementById('searchInput').value.toLowerCase();
        const maxPrice = document.getElementById('priceRange').value;
        const stockFilter = document.getElementById('stockFilter').value;

        const filtered = allProducts.filter(product => {
            const matchesSearch = product.name.toLowerCase().includes(search) ||
                                 (product.description || '').toLowerCase().includes(search);
            const matchesPrice = product.price <= maxPrice;
            const matchesStock = !stockFilter ||
                                (stockFilter === 'in' && product.stock > 5) ||
                                (stockFilter === 'low' && product.stock > 0 && product.stock <= 5) ||
                                (stockFilter === 'out' && product.stock === 0);

            return matchesSearch && matchesPrice && matchesStock;
        });

        renderProducts(filtered);
    }

    function updatePriceDisplay() {
        const range = document.getElementById('priceRange');
        document.getElementById('priceValue').innerText = range.value;
    }

    document.getElementById('priceRange').addEventListener('input', updatePriceDisplay);

    function addToCart(productId) {
        const qty = parseInt(document.getElementById(`qty-${productId}`).value) || 1;
        const product = allProducts.find(p => p.id === productId);

        if (product && qty > 0) {
            cart[productId] = (cart[productId] || 0) + qty;
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount();
            showToast(`Added ${qty} x ${product.name} to cart!`, 'success');
        }
    }

    function openCartModal() {
        if (Object.keys(cart).length === 0) {
            alert('Your cart is empty');
            return;
        }

        let total = 0;
        let itemsHtml = '';

        Object.entries(cart).forEach(([productId, qty]) => {
            const product = allProducts.find(p => p.id == productId);
            if (product) {
                const subtotal = product.price * qty;
                total += subtotal;
                itemsHtml += `
                    <div class="flex justify-between py-2 border-b">
                        <div>
                            <p class="font-medium">${product.name}</p>
                            <p class="text-sm text-gray-600">Qty: ${qty} x $${parseFloat(product.price).toFixed(2)}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold">$${parseFloat(subtotal).toFixed(2)}</p>
                            <button onclick="removeFromCart(${productId})" class="text-red-600 hover:text-red-800 text-sm">Remove</button>
                        </div>
                    </div>
                `;
            }
        });

        document.getElementById('cartItems').innerHTML = itemsHtml;
        document.getElementById('cartTotal').innerText = parseFloat(total).toFixed(2);
        document.getElementById('cartModal').classList.remove('hidden');
    }

    function closeCartModal() {
        document.getElementById('cartModal').classList.add('hidden');
    }

    function removeFromCart(productId) {
        delete cart[productId];
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartCount();
        openCartModal();
        showToast('Item removed from cart.', 'info');
    }

    function proceedToCheckout() {
        if (!token) {
            showToast('Please login first', 'error');
            window.location.href = '/login';
            return;
        }
        window.location.href = '/customer/checkout';
    }
</script>

<!-- Add cart button to nav (would need layout modification for real implementation) -->
<div class="fixed bottom-6 right-6">
    <button onclick="openCartModal()" class="bg-green-600 text-white rounded-full p-4 shadow-lg hover:bg-green-700 transition text-2xl">
        🛒 <span id="cartCount" class="absolute -top-2 -right-2 bg-red-600 text-white text-xs rounded-full h-6 w-6 flex items-center justify-center">0</span>
    </button>
</div>

<script>
    // Update cart count display
    function updateCartCount() {
        const count = Object.values(cart).reduce((sum, qty) => sum + qty, 0);
        document.getElementById('cartCount').innerText = count;
    }
    updateCartCount();
</script>

@endsection
