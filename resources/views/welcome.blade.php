<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Grocery Booking System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="flex items-center">
                        <span class="text-2xl font-bold text-green-600">🛒 Grocery</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    @auth
                        <span class="text-gray-700">
                            {{ auth()->user()->email }}
                            @if (auth()->user()->isAdmin())
                                <span class="ml-2 px-2 py-1 text-xs font-semibold text-white bg-red-600 rounded">ADMIN</span>
                            @endif
                        </span>
                        <form method="POST" action="/logout" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">
                                Logout
                            </button>
                        </form>
                    @else
                        <a href="/login" class="text-gray-700 hover:text-green-600 transition">Login</a>
                        <a href="/register" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">Register</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-green-600 to-green-700 text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-6">
                Fresh Groceries Delivered to Your Door
            </h1>
            <p class="text-xl md:text-2xl mb-8 max-w-3xl mx-auto">
                Browse our selection of fresh fruits, vegetables, and pantry essentials.
                Order online and get your groceries delivered quickly and conveniently.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                @auth
                    @if (auth()->user()->isAdmin())
                        <a href="/admin/products" class="px-8 py-3 bg-white text-green-600 font-semibold rounded-lg hover:bg-gray-100 transition">
                            Go to Admin Dashboard
                        </a>
                    @else
                        <a href="/customer/products" class="px-8 py-3 bg-white text-green-600 font-semibold rounded-lg hover:bg-gray-100 transition">
                            Start Shopping
                        </a>
                    @endif
                @else
                    <a href="/register" class="px-8 py-3 bg-white text-green-600 font-semibold rounded-lg hover:bg-gray-100 transition">
                        Get Started
                    </a>
                    <a href="/login" class="px-8 py-3 border-2 border-white text-white font-semibold rounded-lg hover:bg-white hover:text-green-600 transition">
                        Login
                    </a>
                @endauth
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center text-gray-900 mb-12">Why Choose Us?</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                <div class="bg-white rounded-lg p-8 shadow">
                    <div class="text-4xl mb-4">🚚</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Fast Delivery</h3>
                    <p class="text-gray-600">Get your groceries delivered fresh to your doorstep within 24 hours.</p>
                </div>
                <div class="bg-white rounded-lg p-8 shadow">
                    <div class="text-4xl mb-4">🧊</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Fresh Quality</h3>
                    <p class="text-gray-600">We source only the freshest produce from local farms and suppliers.</p>
                </div>
                <div class="bg-white rounded-lg p-8 shadow">
                    <div class="text-4xl mb-4">💰</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Best Prices</h3>
                    <p class="text-gray-600">Competitive prices on all your daily grocery essentials.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white text-center py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-2xl font-bold mb-2">🛒 Grocery</p>
            <p class="text-gray-400 mb-4">Fresh groceries delivered to your door</p>
            <p class="text-sm text-gray-500">&copy; {{ date('Y') }} Grocery Booking System. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
