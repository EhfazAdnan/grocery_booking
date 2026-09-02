<?php

use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Locale Switcher
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// Home/Welcome
Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->isAdmin()
            ? redirect('/admin/products')
            : redirect('/customer/products');
    }
    return view('welcome');
})->name('home');

// Authentication Routes
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

// Logout — ends the Blade (session) auth. The browser also invalidates the
// JWT via POST /api/auth/logout (see layouts/app.blade.php).
Route::post('/logout', function () {
    if (Auth::guard('web')->check()) {
        Auth::guard('web')->logout();
    }

    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/login');
})->name('logout');

// Admin Routes — role-based access enforced at the middleware level.
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/products', function () {
        return view('admin.products');
    })->name('admin.products');

    Route::get('/admin/orders', function () {
        return view('admin.orders');
    })->name('admin.orders');

    Route::get('/admin/analytics', function () {
        return view('admin.analytics');
    })->name('admin.analytics');
});

// Customer Routes — public browsing stays public; booking pages require auth.
Route::get('/customer/products', function () {
    return view('customer.products');
})->name('customer.products');

Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/customer/orders', function () {
        return view('customer.orders');
    })->name('customer.orders');

    Route::get('/customer/checkout', function () {
        return view('customer.checkout');
    })->name('customer.checkout');

    Route::get('/customer/order-confirmation', function () {
        return view('customer.order-confirmation');
    })->name('customer.order-confirmation');
});
