<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIfCustomer
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->is_admin) {
            return response()->json([
                'error'   => 'Forbidden',
                'message' => 'An administrator cannot place orders in the system.'
            ], 403);
        }

        return $next($request);
    }
}
