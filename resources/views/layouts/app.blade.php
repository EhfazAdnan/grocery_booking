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
                    <!-- Guest links (shown when not logged in) -->
                    <div id="guestLinks" class="flex items-center space-x-4 {{ auth()->check() ? 'hidden' : '' }}">
                        <a href="/login" class="text-gray-700 hover:text-green-600 transition">{{ __('messages.nav.login') }}</a>
                        <a href="/register" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">{{ __('messages.nav.register') }}</a>
                    </div>

                    <!-- Logged-in user dropdown (next to language switcher) -->
                    <div id="userMenu" class="relative {{ auth()->check() ? '' : 'hidden' }}">
                        <button id="userMenuButton" type="button"
                                class="flex items-center space-x-2 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 rounded-lg transition">
                            <span id="userName">{{ auth()->check() ? (auth()->user()->name ?: auth()->user()->email) : '' }}</span>
                            <span id="userRoleBadge" class="px-2 py-0.5 text-xs font-semibold text-white bg-red-600 rounded {{ auth()->check() && auth()->user()->isAdmin() ? '' : 'hidden' }}">ADMIN</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div id="userDropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <p id="userMenuName" class="text-sm font-semibold text-gray-800">{{ auth()->check() ? (auth()->user()->name ?: auth()->user()->email) : '' }}</p>
                                <p id="userMenuEmail" class="text-xs text-gray-500">{{ auth()->check() ? auth()->user()->email : '' }}</p>
                            </div>
                            <div id="userNavLinks" class="py-1">
                                {{-- Filled client-side from localStorage; server-side fallback below --}}
                                @if (auth()->check() && auth()->user()->isAdmin())
                                    <a href="/admin/products" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('messages.nav.products') }}</a>
                                    <a href="/admin/orders" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('messages.nav.orders') }}</a>
                                    <a href="/admin/analytics" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('messages.nav.analytics') }}</a>
                                @elseif (auth()->check())
                                    <a href="/customer/products" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('messages.nav.browse') }}</a>
                                    <a href="/customer/orders" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('messages.nav.my_orders') }}</a>
                                @endif
                            </div>
                            <div class="border-t border-gray-100 py-1">
                                <button id="logoutButton" type="button"
                                        class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    {{ __('messages.nav.logout') }}
                                </button>
                            </div>
                        </div>
                    </div>

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
<script>
    // Translations for the nav/role links, injected server-side.
    @php
        $navLabels = [
            'logout' => __('messages.nav.logout'),
            'browse' => __('messages.nav.browse'),
            'my_orders' => __('messages.nav.my_orders'),
            'products' => __('messages.nav.products'),
            'orders' => __('messages.nav.orders'),
            'analytics' => __('messages.nav.analytics'),
        ];
    @endphp
    const NAV_LABELS = @json($navLabels);

    document.addEventListener('DOMContentLoaded', function () {
        const guestLinks = document.getElementById('guestLinks');
        const userMenu = document.getElementById('userMenu');
        const userDropdown = document.getElementById('userDropdown');
        const userMenuButton = document.getElementById('userMenuButton');
        const logoutButton = document.getElementById('logoutButton');

        const token = localStorage.getItem('token');
        let user = null;
        const storedUser = localStorage.getItem('user');
        if (storedUser) {
            try { user = JSON.parse(storedUser); } catch (e) { user = null; }
        }

        function renderUserMenu(u) {
            const isAdmin = u.role === 'admin';
            const displayName = u.name || u.email || 'User';

            guestLinks.classList.add('hidden');
            userMenu.classList.remove('hidden');

            document.getElementById('userName').textContent = displayName;
            document.getElementById('userMenuName').textContent = displayName;
            document.getElementById('userMenuEmail').textContent = u.email || '';
            document.getElementById('userRoleBadge').classList.toggle('hidden', !isAdmin);

            const links = isAdmin
                ? [['/admin/products', NAV_LABELS.products], ['/admin/orders', NAV_LABELS.orders], ['/admin/analytics', NAV_LABELS.analytics]]
                : [['/customer/products', NAV_LABELS.browse], ['/customer/orders', NAV_LABELS.my_orders]];

            const container = document.getElementById('userNavLinks');
            container.innerHTML = '';
            links.forEach(function ([href, label]) {
                const a = document.createElement('a');
                a.href = href;
                a.className = 'block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50';
                a.textContent = label;
                container.appendChild(a);
            });
        }

        if (token && user) {
            // The real browser flow: auth state lives in localStorage.
            renderUserMenu(user);
        } else if (token) {
            // Token exists but stored user is missing — recover from the API.
            fetch('/api/auth/me', {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token,
                },
            })
                .then(function (res) { return res.ok ? res.json() : Promise.reject(new Error('Unauthenticated')); })
                .then(function (res) {
                    localStorage.setItem('user', JSON.stringify(res.user));
                    renderUserMenu(res.user);
                })
                .catch(function () {
                    localStorage.removeItem('token');
                    localStorage.removeItem('user');
                });
        }

        // Dropdown toggle + close on outside click.
        function hideDropdown() { if (userDropdown) userDropdown.classList.add('hidden'); }

        if (userMenuButton) {
            userMenuButton.addEventListener('click', function (e) {
                e.stopPropagation();
                userDropdown.classList.toggle('hidden');
            });
        }
        document.addEventListener('click', hideDropdown);

        // Logout: invalidate the JWT (best-effort), clear local auth, go to login.
        if (logoutButton) {
            logoutButton.addEventListener('click', function () {
                const t = localStorage.getItem('token');

                localStorage.removeItem('token');
                localStorage.removeItem('user');
                localStorage.removeItem('cart');

                if (t) {
                    fetch('/api/auth/logout', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Authorization': 'Bearer ' + t,
                        },
                    }).catch(function () { /* token invalidation is best-effort */ });
                }

                window.location.href = '/login';
            });
        }
    });
</script>
</body>
</html>
