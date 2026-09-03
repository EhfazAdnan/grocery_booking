@extends('layouts.app')

@section('title', __('Login'))

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full mx-4">
        <h1 class="text-3xl font-bold text-center text-gray-900 mb-2">{{ __('Grocery') }}</h1>
        <h2 class="text-2xl font-bold text-center text-gray-900 mb-6">{{ __('Login') }}</h2>

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                <p class="font-semibold">{{ __('Error') }}</p>
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
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Email') }}</label>
                <input type="email" name="email" id="email" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                       placeholder="your@email.com">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Password') }}</label>
                <div class="relative">
                    <input type="password" name="password" id="password" required
                           class="w-full px-3 pr-10 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                           placeholder="••••••••">
                    <button type="button"
                            data-password-toggle="#password"
                            data-label-show="{{ __('Show password') }}"
                            data-label-hide="{{ __('Hide password') }}"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                            aria-label="{{ __('Show password') }}"
                            title="{{ __('Show password') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" data-icon-show class="h-5 w-5" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M1 12s6-7 11-7 11 7 11 7-6 7-11 7-11-7-11-7z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" data-icon-hide class="h-5 w-5 hidden" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1l22 22"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M1 12s6-7 11-7 11 7 11 7-6 7-11 7-11-7-11-7z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="button" onclick="handleLogin()"
                    class="w-full px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">
                {{ __('Login') }}
            </button>
        </form>

        <p class="text-center text-gray-600 mt-6">
            {{ __("Don't have an account?") }}
            <a href="/register" class="text-green-600 hover:text-green-800 font-semibold">{{ __('Register here') }}</a>
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
                localStorage.setItem('token', data.access_token);
                localStorage.setItem('user', JSON.stringify(data.user));

                // Redirect based on role
                const user = data.user;
                if (user.role === 'admin') {
                    window.location.href = '/admin/products';
                } else {
                    window.location.href = '/customer/products';
                }
            } else {
                const error = await response.json();
                alert(t('Login failed: :details', { details: error.errors?.email?.[0] || t('Invalid credentials') }));
            }
        } catch (error) {
            console.error('Error:', error);
            alert(t('An error occurred. Please try again.'));
        }
    }
</script>
@endsection
