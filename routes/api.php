<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::get('/customer/products', [\App\Http\Controllers\Api\Customer\CustomerController::class, 'products']);

Route::middleware('auth:api')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);

    Route::middleware('role:customer')->group(function () {
        Route::post('/customer/orders', [\App\Http\Controllers\Api\Customer\CustomerController::class, 'placeOrder']);
        Route::get('/customer/orders', [\App\Http\Controllers\Api\Customer\CustomerController::class, 'orders']);
        Route::get('/customer/profile', [\App\Http\Controllers\Api\Customer\CustomerController::class, 'profile']);
        Route::put('/customer/profile', [\App\Http\Controllers\Api\Customer\CustomerController::class, 'updateProfile']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/test', function () {
            return response()->json(['ok' => true]);
        });

        Route::get('/admin/grocery-items', [\App\Http\Controllers\Api\Admin\GroceryItemController::class, 'index']);
        Route::post('/admin/grocery-items', [\App\Http\Controllers\Api\Admin\GroceryItemController::class, 'store']);
        Route::get('/admin/grocery-items/{groceryItem}', [\App\Http\Controllers\Api\Admin\GroceryItemController::class, 'show']);
        Route::put('/admin/grocery-items/{groceryItem}', [\App\Http\Controllers\Api\Admin\GroceryItemController::class, 'update']);
        Route::patch('/admin/grocery-items/{groceryItem}/stock', [\App\Http\Controllers\Api\Admin\GroceryItemController::class, 'updateStock']);
        Route::delete('/admin/grocery-items/{groceryItem}', [\App\Http\Controllers\Api\Admin\GroceryItemController::class, 'destroy']);
    });
});
