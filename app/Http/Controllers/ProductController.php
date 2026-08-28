<?php

namespace App\Http\Controllers;

use App\Http\Requests\DiscontinueProductRequest;
use App\Models\ChildProduct;
use App\Models\FatherProduct;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
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
}
