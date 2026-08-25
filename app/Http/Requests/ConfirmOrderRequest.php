<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
}
