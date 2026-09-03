@extends('layouts.app')

@section('title', __('Register'))

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full mx-4">
        <h1 class="text-3xl font-bold text-center text-gray-900 mb-2">{{ __('Grocery') }}</h1>
        <h2 class="text-2xl font-bold text-center text-gray-900 mb-6">{{ __('Register') }}</h2>

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

        <form method="POST" action="/api/auth/register" id="registerForm">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Name') }}</label>
                <input type="text" name="name" id="name" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                       placeholder="John Doe">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Email') }}</label>
                <input type="email" name="email" id="email" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                       placeholder="your@email.com">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Password') }}</label>
                <div class="relative">
                    <input type="password" name="password" id="password" required
                           class="w-full px-3 pr-10 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                           placeholder="e.g., MyP@ssw0rd">
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
                <ul class="text-xs text-gray-600 mt-2 space-y-1">
                    <li class="flex items-center"><span class="mr-2">•</span> {{ __('At least 8 characters') }}</li>
                    <li class="flex items-center"><span class="mr-2">•</span> {{ __('At least one uppercase letter (A-Z)') }}</li>
                    <li class="flex items-center"><span class="mr-2">•</span> {{ __('At least one number (0-9)') }}</li>
                    <li class="flex items-center"><span class="mr-2">•</span> {{ __('Must match confirmation') }}</li>
                </ul>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Confirm Password') }}</label>
                <div class="relative">
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                           class="w-full px-3 pr-10 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                           placeholder="••••••••">
                    <button type="button"
                            data-password-toggle="#password_confirmation"
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

            <button type="button" onclick="handleRegister()"
                    class="w-full px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">
                {{ __('Create Account') }}
            </button>
        </form>

        <p class="text-center text-gray-600 mt-6">
            {{ __('Already have an account?') }}
            <a href="/login" class="text-green-600 hover:text-green-800 font-semibold">{{ __('Login here') }}</a>
        </p>
    </div>
</div>

<script>
    async function handleRegister() {
        const name = document.getElementById('name').value;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const password_confirmation = document.getElementById('password_confirmation').value;

        if (password !== password_confirmation) {
            alert(t('Passwords do not match'));
            return;
        }

        try {
            const response = await fetch('/api/auth/register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name, email, password, password_confirmation })
            });

            if (response.ok) {
                const data = await response.json();
                localStorage.setItem('token', data.access_token);
                localStorage.setItem('user', JSON.stringify(data.user));

                alert(t('Account created successfully! Redirecting...'));
                window.location.href = '/customer/products';
            } else {
                const error = await response.json();
                alert(t('Registration failed: :details', { details: JSON.stringify(error.errors || error.message) }));
            }
        } catch (error) {
            console.error('Error:', error);
            alert(t('An error occurred. Please try again.'));
        }
    }
</script>
@endsection
