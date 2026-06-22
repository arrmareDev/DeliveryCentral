<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    protected function success($data = null, string $message = '', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    protected function created($data = null, string $message = 'Creado'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    protected function error(string $message, int $code = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $code);
    }

    protected function notFound(string $message = 'No encontrado'): JsonResponse
    {
        return $this->error($message, 404);
    }
}
