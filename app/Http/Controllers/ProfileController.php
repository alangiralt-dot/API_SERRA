<?php

namespace App\Http\Controllers;

use App\Http\Requests\SignInRequest;
use App\Models\Province;
use App\Models\City;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;


class ProfileController extends Controller
{
    public function store(SignInRequest $request)
    {
        $validated = $request->validated();
        
        $cityNameClean = trim($validated['city_name']);
        $provinceNameClean = trim($validated['province_name']);

        $province = Province::firstOrCreate(['province' => $provinceNameClean]);

        $city = City::firstOrCreate([
            'city' => $cityNameClean,
            'province_id' => $province->id
        ]);
        
        $user = DB::transaction(function () use ($validated, $city) {
            $customer = Customer::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'phone' => $validated['phone'],
                'street' => $validated['street'],
                'address_number' => $validated['address_number'],
                'address_floor' => $validated['address_floor'] ?? null,
                'door' => $validated['door'] ?? null,
                'city_id' => $city->id,
                'postal_code' => $validated['postal_code'],
            ]);

            return User::create([
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'customer_id' => $customer->id,
            ]);
        });
        
        $loginRequest = \Illuminate\Http\Request::create('/api/customers/tokens', 'POST', [
            'email'    => $validated['email'],
            'password' => $validated['password'],
        ]);

        $loginRequest->headers->set('Accept', 'application/json');

        return Route::dispatch($loginRequest);
    }
}