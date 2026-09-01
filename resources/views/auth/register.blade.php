@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full mx-4">
        <h1 class="text-3xl font-bold text-center text-gray-900 mb-2">Grocery</h1>
        <h2 class="text-2xl font-bold text-center text-gray-900 mb-6">Register</h2>

        <form method="POST" action="/api/auth/register" id="registerForm">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" name="email" id="email" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                       placeholder="your@email.com">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <input type="password" name="password" id="password" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                       placeholder="••••••••">
                <small class="text-gray-600">Min 8 characters</small>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                       placeholder="••••••••">
            </div>

            <button type="button" onclick="handleRegister()"
                    class="w-full px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">
                Create Account
            </button>
        </form>

        <p class="text-center text-gray-600 mt-6">
            Already have an account?
            <a href="/login" class="text-green-600 hover:text-green-800 font-semibold">Login here</a>
        </p>
    </div>
</div>

<script>
    async function handleRegister() {
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const password_confirmation = document.getElementById('password_confirmation').value;

        if (password !== password_confirmation) {
            alert('Passwords do not match');
            return;
        }

        try {
            const response = await fetch('/api/auth/register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password, password_confirmation })
            });

            if (response.ok) {
                const data = await response.json();
                localStorage.setItem('token', data.data.access_token);
                localStorage.setItem('user', JSON.stringify(data.data.user));

                alert('Account created successfully! Redirecting...');
                window.location.href = '/customer/products';
            } else {
                const error = await response.json();
                alert('Registration failed: ' + JSON.stringify(error.errors || error.message));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        }
    }
</script>
@endsection
