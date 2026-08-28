<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIfAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->is_admin) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have administrator privileges to access this path.'
            ], 403);
        }

        return $next($request);
    }
}
