<?php

namespace App\Http\Requests;

use App\Rules\IsTerminalCategory;
use Illuminate\Foundation\Http\FormRequest;

class GetCategoryProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'min:1', 'exists:categories,id', new IsTerminalCategory],
        ];
    }
    public function validationData(): array
    {
        return array_merge($this->request->all(), [
            'id' => $this->route('id'),
        ]);
    }
}
