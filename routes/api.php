<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CatalogueController;

Route::get('/menu', [CatalogueController::class, 'getMenu']);
