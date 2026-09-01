<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
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
            'id'       => ['required', 'integer', 'min:1', 'exists:customers,id'],
            'is_admin' => ['required', 'boolean'],
        ];
    }
}
