@extends('layouts.app')

@section('title', 'Admin - Product Management')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Product Management</h1>
    <button onclick="openCreateModal()" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
        + Add Product
    </button>
</div>

<!-- Products Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-100 border-b">
            <tr>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Name</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Price</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Stock</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y" id="products-list">
            <!-- Products loaded via JavaScript -->
        </tbody>
    </table>
</div>

<!-- Create/Edit Modal -->
<div id="productModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
        <h2 id="modalTitle" class="text-2xl font-bold mb-4">Create Product</h2>
        <form id="productForm" onsubmit="handleSaveProduct(event)">
            @csrf
            <input type="hidden" id="productId">

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" id="productName" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                <input type="number" id="productPrice" step="0.01" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Stock</label>
                <input type="number" id="productStock" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea id="productDescription" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Save Product
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const token = localStorage.getItem('token');
    const apiUrl = '/api/admin/grocery-items';

    // Load products on page load
    document.addEventListener('DOMContentLoaded', loadProducts);

    async function loadProducts() {
        try {
            const response = await fetch(apiUrl, {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            const data = await response.json();

            const tbody = document.getElementById('products-list');
            tbody.innerHTML = '';

            data.data.forEach(product => {
                const row = `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900">${product.name}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">$${parseFloat(product.price).toFixed(2)}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">${product.stock}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold ${product.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                                ${product.is_active ? 'Active' : 'Inactive'}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm space-x-2">
                            <button onclick="editProduct(${product.id})" class="text-blue-600 hover:text-blue-800">Edit</button>
                            <button onclick="deleteProduct(${product.id})" class="text-red-600 hover:text-red-800">Delete</button>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        } catch (error) {
            console.error('Error loading products:', error);
        }
    }

    function openCreateModal() {
        document.getElementById('productId').value = '';
        document.getElementById('productForm').reset();
        document.getElementById('modalTitle').innerText = 'Create Product';
        document.getElementById('productModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('productModal').classList.add('hidden');
    }

    async function editProduct(id) {
        try {
            const response = await fetch(`${apiUrl}/${id}`, {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            const data = await response.json();
            const product = data.data;

            document.getElementById('productId').value = id;
            document.getElementById('productName').value = product.name;
            document.getElementById('productPrice').value = product.price;
            document.getElementById('productStock').value = product.stock;
            document.getElementById('productDescription').value = product.description || '';
            document.getElementById('modalTitle').innerText = 'Edit Product';
            document.getElementById('productModal').classList.remove('hidden');
        } catch (error) {
            console.error('Error loading product:', error);
        }
    }

    async function handleSaveProduct(event) {
        event.preventDefault();

        const id = document.getElementById('productId').value;
        const method = id ? 'PUT' : 'POST';
        const url = id ? `${apiUrl}/${id}` : apiUrl;

        const payload = {
            name: document.getElementById('productName').value,
            price: document.getElementById('productPrice').value,
            stock: document.getElementById('productStock').value,
            description: document.getElementById('productDescription').value,
            is_active: true
        };

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify(payload)
            });

            if (response.ok) {
                closeModal();
                loadProducts();
            } else {
                const error = await response.json();
                alert('Error: ' + JSON.stringify(error));
            }
        } catch (error) {
            console.error('Error saving product:', error);
        }
    }

    async function deleteProduct(id) {
        if (confirm('Are you sure you want to delete this product?')) {
            try {
                const response = await fetch(`${apiUrl}/${id}`, {
                    method: 'DELETE',
                    headers: { 'Authorization': `Bearer ${token}` }
                });

                if (response.ok) {
                    loadProducts();
                } else {
                    alert('Error deleting product');
                }
            } catch (error) {
                console.error('Error deleting product:', error);
            }
        }
    }
</script>
@endsection
