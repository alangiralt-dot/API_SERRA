<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteChildProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'min:1', 'exists:child_products,id'],
        ];
    }

    public function validationData(): array
    {
        return array_merge($this->request->all(), [
            'id' => $this->route('id'),
        ]);
    }
}