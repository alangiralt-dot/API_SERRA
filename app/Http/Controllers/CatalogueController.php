<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ChildProduct;
use App\Http\Requests\GetCategoryProductsRequest;
use Illuminate\Http\JsonResponse;

class CatalogueController extends Controller
{
    public function getMenu(): JsonResponse
    {
        $categories = Category::all();
        return response()->json($categories, 200);
    }
    public function showChildProducts(GetCategoryProductsRequest $request, int $id)
    {
        $category = Category::findOrFail($id);

        $productsRaw = ChildProduct::with(['fatherProduct', 'availability', 'unit'])
            ->whereHas('fatherProduct', function ($query) use ($id) {
                $query->where('category_id', $id);
            })
            ->get();

        $productsRaw->makeHidden(['cost_unit_price']);
        
        $groupedProducts = $productsRaw->groupBy(function ($product) {
            return $product->fatherProduct->name;
        });

        return response()->json([
            'category' => $category,
            'products' => $groupedProducts
        ], 200);
    }
}
