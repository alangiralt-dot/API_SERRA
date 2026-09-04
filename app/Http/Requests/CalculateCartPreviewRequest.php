<?php

namespace App\Http\Requests;

use App\Rules\IsMultipleOfPack;
use Illuminate\Foundation\Http\FormRequest;

class CalculateCartPreviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items'            => ['required', 'array'],
            'items.*.id'       => ['required', 'integer', 'min:1', 'exists:child_products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', new IsMultipleOfPack],
        ];
    }
}
