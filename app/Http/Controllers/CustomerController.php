<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\SignInRequest;
use App\Models\Province;
use App\Models\City;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

class CustomerController extends Controller
{
    public function getProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $customer = Customer::with(['city.province'])->find($user->customer_id);

        return response()->json([
            'first_name'     => $customer->first_name,
            'last_name'      => $customer->last_name,
            'phone'          => $customer->phone,
            'street'         => $customer->street,
            'address_number' => $customer->address_number,
            'address_floor'  => $customer->address_floor,
            'door'           => $customer->door,
            'postal_code'    => $customer->postal_code,
            'city'           => $customer->city->city,
            'province'       => $customer->city->province->province,
        ], 200);
    }

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
    
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        
        if (Auth::guard('web')->attempt($credentials)) {
            $user = Auth::user();

            $user->tokens()->update(['revoked' => true]);
            
            $tokenResult = $user->createToken('API_SERRA_Token');
            $token = $tokenResult->token;
            $token->save();

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'access_token' => $tokenResult->accessToken,
                    'token_type'   => 'Bearer'
                ]
            ], 201);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'The email or password is incorrect.'
        ], 401);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Session successfully closed.'
        ], 200);
    }
}
