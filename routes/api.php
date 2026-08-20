<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CatalogueController;

Route::get('/menu', [CatalogueController::class, 'getMenu']);
Route::get('/categories/{id}/products', [CatalogueController::class, 'showChildProducts']);