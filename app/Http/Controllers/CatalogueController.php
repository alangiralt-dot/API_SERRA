<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogueController extends Controller
{
    public function getMenu(): JsonResponse
    {
        $categories = Category::all();
        return response()->json($categories, 200);
    }
}
