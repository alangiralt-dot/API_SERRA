<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CatalogueController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;

Route::get('/menu', [CatalogueController::class, 'getMenu']);
Route::get('/categories/{id}/products', [CatalogueController::class, 'showChildProducts']);
Route::get('/orders/line-subtotal', [OrderController::class, 'calculateLineSubtotal']);
Route::post('/customers/tokens', [AuthController::class, 'login']);
Route::post('/customers', [ProfileController::class, 'store']);

Route::middleware('auth:api')->group(function () {
    Route::put('/customers/tokens', [AuthController::class, 'logout']);
});

Route::middleware(['auth:api', 'customer'])->group(function () {
    Route::post('/orders', [OrderController::class, 'confirmOrder']);
});

Route::middleware(['auth:api', 'admin'])->group(function () {
    Route::delete('/products/fathers/{id}', [ProductController::class, 'destroyFather']);
    Route::delete('/products/children/{id}', [ProductController::class, 'destroyChild']);
});
