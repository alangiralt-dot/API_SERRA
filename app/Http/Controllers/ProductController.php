<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteChildProductRequest;
use App\Models\ChildProduct;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function destroyChild(DeleteChildProductRequest $request): JsonResponse
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
}
