<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'father_product_id' => ['nullable', 'integer', 'min:1', 'exists:father_products,id'],
        
            'name'         => ['required_without:father_product_id', 'string', 'max:255', 'unique:father_products,name'],
            'description'  => ['nullable', 'string'],
            'details'      => ['nullable', 'string'],
            'image_path'   => ['required_without:father_product_id', 'string', 'max:255'],
            'category_id'  => ['required_without:father_product_id', 'integer', 'min:1', 'exists:categories,id'],

            'child_products'   => ['required', 'array', 'min:1'],
            
            'child_products.*.reference'          => ['required', 'string', 'max:255', 'distinct', 'unique:child_products,reference'],
            'child_products.*.width'              => ['required', 'integer', 'min:1'],
            'child_products.*.height'             => ['required', 'integer', 'min:-1', 'not_in:0'],
            'child_products.*.length'             => ['required', 'integer', 'min:1'],
            'child_products.*.cost_unit_price'    => ['required', 'numeric', 'gt:0'],
            'child_products.*.current_unit_price' => ['required', 'numeric', 'gt:0'],
            'child_products.*.pack'               => ['required', 'integer', 'min:1'],
            'child_products.*.stock'              => ['required', 'integer', 'min:0'],
            'child_products.*.availability_id'    => ['required', 'integer', 'min:1', 'exists:availabilities,id'],
            'child_products.*.unit_id'            => ['required', 'integer', 'min:1', 'exists:units,id'],
        ];
    }
}