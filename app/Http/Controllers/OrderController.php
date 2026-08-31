<?php

namespace App\Http\Controllers;

use App\Models\ChildProduct;
use App\Models\Order;
use App\Models\Status;
use App\Http\Requests\CalculateLineSubtotalRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Requests\ShowOrderDetailsRequest;
use App\Http\Requests\ConfirmOrderRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    
    public function showOrderDetails(ShowOrderDetailsRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $order = Order::with(['status', 'childProducts.fatherProduct'])->find($validated['id']);

        $total = $order->total_amount;
        $baseImposable = round($total / 1.21, 2);
        $iva = round($total - $baseImposable, 2);

        $orderLines = $order->childProducts->map(function ($childProduct) {
            return [
                'name'            => $childProduct->fatherProduct->name,
                'reference'       => $childProduct->reference,
                'width'           => $childProduct->width,
                'height'          => $childProduct->height,
                'length'          => $childProduct->length,
                'quantity'        => $childProduct->pivot->quantity,
                'sale_unit_price' => $childProduct->pivot->sale_unit_price,
                'subtotal'        => $childProduct->pivot->subtotal,
            ];
        });

        return response()->json([
            'id'                 => $order->id,
            'code'               => $order->code,
            'status'             => $order->status->status,
            'date'               => date('d/m/Y H:i', strtotime($order->date)),
            'order_availability' => $order->order_availability,
            'base_imposable'     => $baseImposable,
            'iva'                => $iva,
            'total_amount'       => $total,
            'order_lines'        => $orderLines
        ], 200);
    }

    public function updateStatus(UpdateOrderStatusRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $order = Order::find($validated['id']);

        $order->update([
            'status_id' => $validated['status_id']
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Order status successfully updated.'
        ], 200);
    }

    public function getStatuses(): JsonResponse
    {
        return response()->json(Status::all(), 200);
    }
    
    public function calculateLineSubtotal(CalculateLineSubtotalRequest $request): JsonResponse
    {
        $product = ChildProduct::find($request->input('id'));
        $quantity = $request->input('quantity');
        $subtotal = $this->computeSubtotal($product, $quantity);

        return response()->json([
            'subtotal' => $subtotal
        ], 200);
    }
    public function confirmOrder(ConfirmOrderRequest $request): JsonResponse
    {
        // 1. Instanciem la nova comanda lligada al client de Passport (el model generarà el 'code' sol)
        $order = new Order();
        $order->customer_id = $request->user()->customer_id;
        $order->status_id = 1; // Estat inicial natiu
        $order->date = now();
        $order->order_availability = '-'; // Valor inicial temporal per passar el NOT NULL
        $order->total_amount = 0.00;
        $order->save();

        // 2. Processem les línies de comanda del contracte validat
        $lineData = collect($request->validated('order_lines'))->keyBy('id');
        $productIds = $lineData->keys()->all();
        $products = ChildProduct::with('availability')->findMany($productIds);

        $pivotData = [];
        $runningTotal = 0;
        $maxWeight = 0;
        $finalAvailability = null;

        foreach ($products as $product) {
            $quantity = $lineData[$product->id]['quantity'];
            
            $subtotal = $this->computeSubtotal($product, $quantity);
            $runningTotal += $subtotal;

            $weight = $product->availability->delay_weight ?? 0;
            if ($weight > $maxWeight) {
                $maxWeight = $weight;
                $finalAvailability = $product->availability->availability;
            }

            $pivotData[$product->id] = [
                'quantity'        => $quantity,
                'sale_unit_price' => $product->current_unit_price,
                'subtotal'        => $subtotal,
            ];
        }

        $order->childProducts()->attach($pivotData);

        $order->update([
            'total_amount'       => round($runningTotal * 1.21, 2),
            'order_availability' => $finalAvailability
        ]);
        
        DB::update("
            UPDATE child_products 
            SET stock = stock - (
                SELECT quantity 
                FROM child_product_order 
                WHERE child_product_order.child_product_id = child_products.id 
                  AND child_product_order.order_id = ?
            )
            WHERE id IN (
                SELECT child_product_id 
                FROM child_product_order 
                WHERE order_id = ?
            )
        ", [$order->id, $order->id]);

        return response()->json([
            'status'   => 'success',
            'order_id' => $order->id
        ], 200);
    }
    private function computeSubtotal(ChildProduct $product, int $quantity): float
    {
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

        return round($subtotal, 2);
    }
}