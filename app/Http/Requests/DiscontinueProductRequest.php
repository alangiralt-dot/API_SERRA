<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DiscontinueProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $table = str_contains($this->route()->uri(), 'children') ? 'child_products' : 'father_products';

        return [
            'id' => ['required', 'integer', 'min:1', "exists:{$table},id"],
        ];
    }
    public function validationData(): array
    {
        return array_merge($this->request->all(), [
            'id' => $this->route('id'),
        ]);
    }
}