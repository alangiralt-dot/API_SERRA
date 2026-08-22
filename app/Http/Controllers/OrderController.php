<?php

namespace App\Http\Controllers;

use App\Models\ChildProduct;
use App\Http\Requests\CalculateLineSubtotalRequest;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function calculateLineSubtotal(CalculateLineSubtotalRequest $request): JsonResponse
    {
        $childProductId = $request->input('id');
        $quantity = $request->input('quantity');

        $product = ChildProduct::with('fatherProduct')->findOrFail($childProductId);

        $subtotal = 0.0;
        $unitId = $product->unit_id;

        if ($unitId == 1 || $unitId == 4) { 
            $subtotal = $quantity * $product->current_unit_price;
        } elseif ($unitId == 2) { 
            $linearMetres = ($product->length / 1000) * $quantity;
            $subtotal = $linearMetres * $product->current_unit_price;
        } elseif ($unitId == 5) { 
            $squareMetres = (($product->width / 1000) * ($product->length / 1000)) * $quantity;
            $subtotal = $squareMetres * $product->current_unit_price;
        } elseif ($unitId == 3) { 
            $cubicMetres = (($product->width / 1000) * ($product->height / 1000) * ($product->length / 1000)) * $quantity;
            $subtotal = $cubicMetres * $product->current_unit_price;
        }

        return response()->json([
            'subtotal' => round($subtotal, 2)
        ], 200);
    }
}
