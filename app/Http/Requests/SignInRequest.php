<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SignInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name'     => ['required', 'string', 'max:255'],
            'last_name'      => ['required', 'string', 'max:255'],
            'phone'          => ['required', 'string', 'max:255'],
            'street'         => ['required', 'string', 'max:255'],
            'address_number' => ['required', 'string', 'max:255'],
            'address_floor'  => ['nullable', 'string', 'max:255'],
            'door'           => ['nullable', 'string', 'max:255'],
            'postal_code'    => ['required', 'string', 'max:255'],
            'city_name'      => ['required', 'string', 'max:255'],     
            'province_name'  => ['required', 'string', 'max:255'], 
            'email'          => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'       => ['required', 'string', 'min:6'],
        ];
    }
}