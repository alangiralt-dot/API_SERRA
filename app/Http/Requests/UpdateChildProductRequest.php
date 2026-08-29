<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChildProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function validationData(): array
    {
        return array_merge($this->request->all(), [
            'id' => $this->route('id'),
        ]);
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'min:1', 'exists:child_products,id'],

            'reference'          => ['sometimes', 'string', 'max:255', 'unique:child_products,reference'],
            'width'              => ['sometimes', 'integer', 'min:1'],
            'height'             => ['sometimes', 'integer', 'min:-1', 'not_in:0'],
            'length'             => ['sometimes', 'integer', 'min:1'],
            
            'cost_unit_price'    => ['sometimes', 'numeric', 'gt:0'],
            'current_unit_price' => ['sometimes', 'numeric', 'gt:0'],
            'pack'               => ['sometimes', 'integer', 'min:1'],
            'stock'              => ['sometimes', 'integer', 'min:0'],
            
            'is_discontinued'    => ['sometimes', 'integer', 'in:0,1'],
            
            'availability_id'    => ['sometimes', 'integer', 'min:1', 'exists:availabilities,id'],
            'unit_id'            => ['sometimes', 'integer', 'min:1', 'exists:units,id'],
        ];
    }
}
