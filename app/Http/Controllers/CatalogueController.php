<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\FatherProduct;
use App\Models\ChildProduct;
use App\Models\Unit;
use App\Models\Availability;
use App\Http\Requests\DiscontinueProductRequest;
use App\Http\Requests\GetCategoryProductsRequest;
use App\Http\Requests\StoreProductRequest;
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

    public function store(StoreProductRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $fatherId = \Illuminate\Support\Facades\DB::transaction(function () use ($validated) {
            
            if (!empty($validated['father_product_id'])) {
                $fatherId = $validated['father_product_id'];
            } else {
                $father = FatherProduct::create([
                    'name'           => $validated['name'],
                    'description'    => $validated['description'] ?? null,
                    'details'        => $validated['details'] ?? null,
                    'image_path'     => $validated['image_path'],
                    'is_discontinued' => 1,
                    'category_id'    => $validated['category_id'],
                ]);
                $fatherId = $father->id;
            }

            foreach ($validated['child_products'] as $childData) {
                ChildProduct::create([
                    'reference'          => $childData['reference'],
                    'width'              => $childData['width'],
                    'height'             => $childData['height'],
                    'length'             => $childData['length'],
                    'cost_unit_price'    => $childData['cost_unit_price'],
                    'current_unit_price' => $childData['current_unit_price'],
                    'pack'               => $childData['pack'],
                    'stock'              => $childData['stock'],
                    'is_discontinued'    => 1,
                    'father_product_id'  => $fatherId,
                    'availability_id'    => $childData['availability_id'],
                    'unit_id'            => $childData['unit_id'],
                ]);
            }

            return $fatherId;
        });

        return response()->json([
            'status'            => 'success',
            'father_product_id' => $fatherId
        ], 201);
    }
}
