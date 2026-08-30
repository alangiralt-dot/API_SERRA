<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFatherProductRequest extends FormRequest
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
            'id' => ['required', 'integer', 'min:1', 'exists:father_products,id'],
            'name'        => ['sometimes', 'string', 'max:255', 'unique:father_products,name'],
            'description' => ['nullable', 'string'],
            'details'     => ['nullable', 'string'],
            'image_path'  => ['sometimes', 'string', 'max:255'],
            'is_discontinued' => ['sometimes', 'integer', 'in:0,1'],
        ];
    }
}
