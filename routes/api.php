<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CatalogueController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CustomerController;

Route::post('/customers/tokens', [CustomerController::class, 'login']);
Route::post('/customers', [CustomerController::class, 'store']);

Route::get('/menu', [CatalogueController::class, 'getMenu']);
Route::get('/categories/{id}/products', [CatalogueController::class, 'showChildProducts']);
Route::get('/units', [CatalogueController::class, 'getUnits']);
Route::get('/availabilities', [CatalogueController::class, 'getAvailabilities']);

Route::get('/orders/line-subtotal', [OrderController::class, 'calculateLineSubtotal']);
Route::get('/statuses', [OrderController::class, 'getStatuses']);

Route::middleware('auth:api')->group(function () {
    Route::delete('/customers/tokens', [CustomerController::class, 'logout']);
    Route::get('/customers/profiles', [CustomerController::class, 'getProfile']);
    Route::put('/customers/profiles', [CustomerController::class, 'updateProfile']);
    
    Route::get('/orders/{id}', [OrderController::class,'showOrderDetails']);
    Route::get('/orders', [OrderController::class,'showOrders']);
});

Route::middleware(['auth:api', 'customer'])->group(function () {
    Route::post('/orders', [OrderController::class, 'confirmOrder']);
});

Route::middleware(['auth:api', 'admin'])->group(function () {
    Route::delete('/products/fathers/{id}', [CatalogueController::class, 'discontinueFatherProduct']);
    Route::delete('/products/children/{id}', [CatalogueController::class, 'discontinueChildProduct']);
    Route::put('/products/fathers/{id}', [CatalogueController::class, 'updateFatherProduct']);
    Route::put('/products/children/{id}', [CatalogueController::class, 'updateChildProduct']);
    Route::post('/products', [CatalogueController::class, 'store']);
   
    Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);
});
