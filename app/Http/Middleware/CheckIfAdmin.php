<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIfAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Fase 3: Si l'usuari NO és administrador, li tallem el pas de forma immediata
        if (! $request->user() || ! $request->user()->is_admin) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'No tens privilegis d\'administrador per accedir a aquesta ruta.'
            ], 403); // Retornem el codi 403 estàndard de REST d'accés prohibit
        }

        // Si és administrador (is_admin == true), pugem la barrera
        return $next($request);
    }
}
