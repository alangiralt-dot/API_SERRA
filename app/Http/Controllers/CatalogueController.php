<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\FatherProduct;
use App\Models\ChildProduct;
use App\Models\Unit;
use App\Models\Availability;
use App\Http\Requests\DiscontinueProductRequest;
use App\Http\Requests\GetCategoryProductsRequest;
use Illuminate\Http\JsonResponse;

class CatalogueController extends Controller
{
    public function discontinueChildProduct(DiscontinueProductRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $product = ChildProduct::with('fatherProduct')->find($validated['id']);

        if ($product->fatherProduct->is_discontinued) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot discontinue a child variant if its father product is already discontinued.'
            ], 422);
        }

        $product->is_discontinued = true;
        $product->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Product successfully discontinued.'
        ], 200);
    }
    
    public function discontinueFatherProduct(DiscontinueProductRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $father = FatherProduct::find($validated['id']);

        $father->is_discontinued = true;
        $father->save();

        ChildProduct::where('father_product_id', $father->id)->update([
            'is_discontinued' => true
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Father product and all its child variants successfully discontinued.'
        ], 200);
    }
    
    public function getMenu(): JsonResponse
    {
        $categories = Category::all();
        return response()->json($categories, 200);
    }
    
    public function showChildProducts(GetCategoryProductsRequest $request, int $id)
    {
        $category = Category::findOrFail($id);

        $productsRaw = ChildProduct::with(['fatherProduct', 'availability', 'unit'])
            ->where('is_discontinued', false) 
            ->whereHas('fatherProduct', function ($query) use ($id) {
                $query->where('category_id', $id)
                      ->where('is_discontinued', false);
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

    public function getUnits(): JsonResponse
    {
        return response()->json(Unit::all(), 200);
    }

    public function getAvailabilities(): JsonResponse
    {
        return response()->json(Availability::all(), 200);
    }
}
