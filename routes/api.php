<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CatalogueController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CustomerController;

Route::get('/menu', [CatalogueController::class, 'getMenu']);
Route::get('/categories/{id}/products', [CatalogueController::class, 'showChildProducts']);
Route::get('/orders/line-subtotal', [OrderController::class, 'calculateLineSubtotal']);
Route::post('/customers/tokens', [CustomerController::class, 'login']);
Route::post('/customers', [CustomerController::class, 'store']);

Route::middleware('auth:api')->group(function () {
    Route::put('/customers/tokens', [CustomerController::class, 'logout']);
});

Route::middleware(['auth:api', 'customer'])->group(function () {
    Route::post('/orders', [OrderController::class, 'confirmOrder']);
});

Route::middleware(['auth:api', 'admin'])->group(function () {
    Route::delete('/products/fathers/{id}', [CatalogueController::class, 'discontinueFatherProduct']);
    Route::delete('/products/children/{id}', [CatalogueController::class, 'discontinueChildProduct']);
    Route::get('/units', [CatalogueController::class, 'getUnits']);
    Route::get('/availabilities', [CatalogueController::class, 'getAvailabilities']);
    Route::post('/products', [CatalogueController::class, 'store']);
});
