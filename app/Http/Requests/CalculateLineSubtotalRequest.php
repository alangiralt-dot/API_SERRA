<?php

namespace App\Http\Requests;

use App\Rules\IsMultipleOfPack;
use Illuminate\Foundation\Http\FormRequest;

class CalculateLineSubtotalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'min:1', 'exists:child_products,id'],
            'quantity' => ['required', 'integer', 'min:1', new IsMultipleOfPack],
        ];
    }
}
