<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);

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
