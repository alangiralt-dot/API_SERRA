<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShowOrdersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function validationData(): array
    {
        return array_merge($this->all(), [
            'customer_id' => $this->user()->customer_id,
        ]);
    }

    public function rules(): array
    {
        if ($this->user()->is_admin) {
            return [
                'customer_id' => ['required', 'integer', 'min:1'],
            ];
        }

        return [
            'customer_id' => ['required', 'integer', 'min:1', 'exists:orders,customer_id'],
        ];
    }
}
