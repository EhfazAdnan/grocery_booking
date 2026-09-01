<?php

use Illuminate\Support\Facades\Route;

// Home/Welcome
Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->isAdmin()
            ? redirect('/admin/products')
            : redirect('/customer/products');
    }
    return view('welcome');
});

// Authentication Routes
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::post('/logout', function () {
    // Clear local storage on client side (JS handles this)
    return redirect('/login');
})->name('logout');

// Admin Routes
Route::get('/admin/products', function () {
    return view('admin.products');
})->name('admin.products');

Route::get('/admin/orders', function () {
    return view('admin.orders');
})->name('admin.orders');

Route::get('/admin/analytics', function () {
    return view('admin.analytics');
})->name('admin.analytics');

// Customer Routes
Route::get('/customer/products', function () {
    return view('customer.products');
})->name('customer.products');

Route::get('/customer/orders', function () {
    return view('customer.orders');
})->name('customer.orders');

Route::get('/customer/checkout', function () {
    return view('customer.checkout');
})->name('customer.checkout');

Route::get('/customer/order-confirmation', function () {
    return view('customer.order-confirmation');
})->name('customer.order-confirmation');
