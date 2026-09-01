<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Grocery Booking System')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/htmx.org"></script>
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

                        <!-- Role-based Navigation -->
                        @if (auth()->user()->isAdmin())
                            <div class="flex space-x-2">
                                <a href="/admin/products" class="text-gray-700 hover:text-green-600 transition">Products</a>
                                <a href="/admin/orders" class="text-gray-700 hover:text-green-600 transition">Orders</a>
                                <a href="/admin/analytics" class="text-gray-700 hover:text-green-600 transition">Analytics</a>
                            </div>
                        @else
                            <div class="flex space-x-2">
                                <a href="/customer/products" class="text-gray-700 hover:text-green-600 transition">Browse</a>
                                <a href="/customer/orders" class="text-gray-700 hover:text-green-600 transition">My Orders</a>
                            </div>
                        @endif

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

                    <!-- Language Switcher -->
                    <select onchange="window.location.href='/locale/'+this.value"
                            class="px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                        @foreach (['en', 'bn', 'es', 'fr'] as $lang)
                            <option value="{{ $lang }}" {{ app()->getLocale() === $lang ? 'selected' : '' }}>
                                {{ __('messages.language.'.$lang) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </nav>

    <!-- Alert Messages -->
    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 m-4" role="alert">
            <p class="font-bold">Error</p>
            <ul class="list-disc ml-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 m-4" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white text-center py-4 mt-12">
        <p>&copy; {{ date('Y') }} Grocery Booking System. All rights reserved.</p>
    </footer>
</body>
</html>
