@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full mx-4">
        <h1 class="text-3xl font-bold text-center text-gray-900 mb-2">Grocery</h1>
        <h2 class="text-2xl font-bold text-center text-gray-900 mb-6">Login</h2>

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                <p class="font-semibold">Error</p>
                <ul class="list-disc ml-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="/api/auth/login" id="loginForm">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" name="email" id="email" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                       placeholder="your@email.com">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <input type="password" name="password" id="password" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                       placeholder="••••••••">
            </div>

            <button type="button" onclick="handleLogin()"
                    class="w-full px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">
                Login
            </button>
        </form>

        <p class="text-center text-gray-600 mt-6">
            Don't have an account?
            <a href="/register" class="text-green-600 hover:text-green-800 font-semibold">Register here</a>
        </p>
    </div>
</div>

<script>
    async function handleLogin() {
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        try {
            const response = await fetch('/api/auth/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            });

            if (response.ok) {
                const data = await response.json();
                localStorage.setItem('token', data.data.access_token);
                localStorage.setItem('user', JSON.stringify(data.data.user));

                // Redirect based on role
                const user = data.data.user;
                if (user.role === 'admin') {
                    window.location.href = '/admin/products';
                } else {
                    window.location.href = '/customer/products';
                }
            } else {
                const error = await response.json();
                alert('Login failed: ' + (error.errors?.email?.[0] || 'Invalid credentials'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        }
    }
</script>
@endsection
