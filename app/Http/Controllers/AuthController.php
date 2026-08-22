<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

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
}
