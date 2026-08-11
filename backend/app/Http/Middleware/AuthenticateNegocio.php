<?php

namespace App\Http\Middleware;

use App\Models\Negocio;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateNegocio
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->bearerToken();

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key requerida',
            ], 401);
        }

        $negocio = Negocio::where('api_key', $apiKey)
            ->where('activo', true)
            ->first();

        if (!$negocio) {
            return response()->json([
                'success' => false,
                'message' => 'API key inválida o negocio inactivo',
            ], 401);
        }

        // Inyectamos el negocio autenticado en el request
        $request->attributes->set('negocio', $negocio);

        return $next($request);
    }
}
