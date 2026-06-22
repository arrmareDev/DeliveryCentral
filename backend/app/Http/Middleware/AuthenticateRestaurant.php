<?php

namespace App\Http\Middleware;

use App\Models\Restaurant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateRestaurant
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

        $restaurant = Restaurant::where('api_key', $apiKey)
            ->where('activo', true)
            ->first();

        if (!$restaurant) {
            return response()->json([
                'success' => false,
                'message' => 'API key inválida o restaurante inactivo',
            ], 401);
        }

        // Inyectamos el restaurante autenticado en el request
        $request->attributes->set('restaurant', $restaurant);

        return $next($request);
    }
}
