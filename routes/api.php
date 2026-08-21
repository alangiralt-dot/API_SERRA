<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CatalogueController;
use App\Http\Controllers\OrderController;


Route::get('/menu', [CatalogueController::class, 'getMenu']);
Route::get('/categories/{id}/products', [CatalogueController::class, 'showChildProducts']);
Route::get('/orders/line-subtotal', [OrderController::class, 'calculateLineSubtotal']);
