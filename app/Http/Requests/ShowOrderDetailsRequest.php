<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class ShowOrderDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->user()->is_admin) return true;

        $rows = DB::select("SELECT customer_id FROM orders WHERE id = ?", [$this->route('id')]);

        if (!$rows || $this->user()->customer_id === $rows[0]->customer_id) return true;

        return false;
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
            'id' => ['required', 'integer', 'min:1', 'exists:orders,id'],
        ];
    }
}