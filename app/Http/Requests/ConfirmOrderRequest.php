<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ChildProduct;

class ConfirmOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_lines'            => ['required', 'array', 'min:1'],
            'order_lines.*.id'       => ['required', 'integer', 'exists:child_products,id'],
            'order_lines.*.quantity' => ['required', 'integer', 'min:1']
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $orderLines = $this->input('order_lines', []);

            $productIds = collect($orderLines)->pluck('id')->all();
            $products = ChildProduct::whereIn('id', $productIds)->get()->keyBy('id');

            $errorsAccumulated = [];

            foreach ($orderLines as $index => $line) {
                $product = $products[$line['id']];

                if ($product->is_discontinued) {
                    $errorsAccumulated[] = "The product {$line['id']} is discontinued and cannot be ordered.";
                    continue; 
                }
                
                if ($line['quantity'] > $product->stock) {
                    $errorsAccumulated[] = "The stock for product {$line['id']} is $product->stock.";
                }

                if ($line['quantity'] % $product->pack !== 0) {
                    $errorsAccumulated[] = "The quantity for product {$line['id']} must be a multiple of $product->pack.";
                }
            }
            
            if (!empty($errorsAccumulated)) {
                $concatenatedText = implode(' ', $errorsAccumulated);
                $validator->errors()->add('order_error', $concatenatedText);
            }
        });
    }
}
