<?php

use App\Http\Controllers\Api\ComisionController;
use App\Http\Controllers\Api\DespachoController;
use App\Http\Controllers\Api\RestaurantController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\ReportesController;
use App\Http\Controllers\Api\NotificacionController;
use App\Http\Controllers\Api\BusquedaController;

// ══════════════════════════════════════════════════════════
// ── RESTAURANTES — autenticados con API key ─────────────────
// ══════════════════════════════════════════════════════════
Route::prefix('v1')
    ->middleware(['restaurant.auth', 'throttle:120,1'])
    ->group(function () {
        Route::post('despachos/solicitar', [DespachoController::class, 'solicitar']);
        Route::get('despachos/{order_id}/estado', [DespachoController::class, 'estadoPorOrderId'])
            ->where('order_id', '[0-9]+');
        Route::post('despachos/{order_id}/cancelar', [DespachoController::class, 'cancelarPorRestaurante'])
            ->where('order_id', '[0-9]+');
    });

// ══════════════════════════════════════════════════════════
// ── MOTORIZADOS — auth pública ────────────────────────────
// ══════════════════════════════════════════════════════════
Route::prefix('v1/motorizado')->group(function () {

    Route::post('auth/register', [DespachoController::class, 'register'])
        ->middleware('throttle:5,1');
    Route::post('auth/login', [DespachoController::class, 'login'])
        ->middleware('throttle:10,1');

    // ↓ NUEVO — recuperar contraseña (público, no requiere sesión)
    Route::post('auth/forgot-password', [DespachoController::class, 'forgotPassword'])
        ->middleware('throttle:5,1');
    Route::post('auth/reset-password', [DespachoController::class, 'resetPassword'])
        ->middleware('throttle:5,1');

    // ↓ NUEVO — el enlace del correo de verificación llega aquí directo,
    // no pasa por el SPA. Protegido por firma (signed), no por Sanctum.
    Route::get('auth/verify-email/{id}/{hash}', [DespachoController::class, 'verifyEmail'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('motorizado.verification.verify');

    Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {
        Route::post('auth/logout', [DespachoController::class, 'logout']);
        Route::get('me', [DespachoController::class, 'me']);
        Route::put('perfil', [DespachoController::class, 'updatePerfil']);

        // ↓ NUEVO — reenviar correo de verificación (requiere estar logueado)
        Route::post('auth/resend-verification', [DespachoController::class, 'resendVerification'])
            ->middleware('throttle:3,1');

        Route::patch('estado', [DespachoController::class, 'updateEstado']);
        Route::patch('ubicacion', [DespachoController::class, 'updateUbicacion']);

        Route::get('pedidos', [DespachoController::class, 'pedidosDisponibles']);
        Route::get('despachos/activo', [DespachoController::class, 'despachoActivo']);

        Route::post('despachos/{id}/aceptar', [DespachoController::class, 'aceptar'])
            ->where('id', '[0-9]+');
        Route::patch('despachos/{id}/estado', [DespachoController::class, 'updateEstadoDespacho'])
            ->where('id', '[0-9]+');

        // Historial PERSONAL del motorizado (sus propias entregas) —
        // no confundir con el historial global del panel admin de abajo.
        Route::get('historial', [DespachoController::class, 'historial']);
    });
});

// ══════════════════════════════════════════════════════════
// ── TU PANEL SUPERADMIN — auth Sanctum normal (tu usuario) ──
// ══════════════════════════════════════════════════════════
Route::prefix('v1/admin')
    ->middleware(['auth:sanctum', 'throttle:120,1'])
    ->group(function () {

        // Restaurantes (tus clientes)
        Route::get('restaurants', [RestaurantController::class, 'index']);
        Route::post('restaurants', [RestaurantController::class, 'store']);
        Route::get('restaurants/{id}', [RestaurantController::class, 'show'])
            ->where('id', '[0-9]+');
        Route::put('restaurants/{id}', [RestaurantController::class, 'update'])
            ->where('id', '[0-9]+');
        Route::post('restaurants/{id}/regenerate-key', [RestaurantController::class, 'regenerateKey'])
            ->where('id', '[0-9]+');
        Route::delete('restaurants/{id}', [RestaurantController::class, 'destroy'])
            ->where('id', '[0-9]+');

        // Despachos globales
        Route::get('despachos', [DespachoController::class, 'index']);

        // Historial GLOBAL del panel admin (todos los restaurantes/motorizados) —
        // método distinto al de arriba (historialCentral, no historial), porque
        // ya existía un método `historial()` para el motorizado individual.
        Route::get('despachos/historial', [DespachoController::class, 'historialCentral']);

        Route::post('despachos/{id}/cancelar', [DespachoController::class, 'cancelar'])
            ->where('id', '[0-9]+');

        // Motorizados
        Route::get('motorizados', [DespachoController::class, 'motorizados']);
        Route::patch('motorizados/{id}/verificar', [DespachoController::class, 'verificar'])
            ->where('id', '[0-9]+');
        Route::patch('motorizados/{id}/toggle-activo', [DespachoController::class, 'toggleActivo'])
            ->where('id', '[0-9]+');

        // Comisiones
        Route::get('comisiones', [ComisionController::class, 'index']);
        Route::get('comisiones/{motorizado_id}', [ComisionController::class, 'detalle'])
            ->where('motorizado_id', '[0-9]+');
        Route::post('comisiones/cobrar', [ComisionController::class, 'cobrar']);

        // Configuración
        Route::get('config', [ComisionController::class, 'getConfig']);
        Route::put('config', [ComisionController::class, 'updateConfig']);

        Route::get('analytics/dashboard', [AnalyticsController::class, 'dashboard']);

        Route::get('reportes/despachos/pdf', [ReportesController::class, 'despachosPdf']);
        Route::get('reportes/despachos/excel', [ReportesController::class, 'despachosExcel']);
        Route::get('reportes/comisiones/pdf', [ReportesController::class, 'comisionesPdf']);
        Route::get('reportes/comisiones/excel', [ReportesController::class, 'comisionesExcel']);

        Route::get('notificaciones', [NotificacionController::class, 'index']);
        Route::patch('notificaciones/{id}/leer', [NotificacionController::class, 'leer'])
            ->where('id', '[0-9]+');
        Route::patch('notificaciones/leer-todas', [NotificacionController::class, 'leerTodas']);

        //Buscar general
        Route::get('buscar', [BusquedaController::class, 'index']);

    });

// ══════════════════════════════════════════════════════════
// ── AUTH de tu usuario superadmin ───────────────────────────
// ══════════════════════════════════════════════════════════
Route::prefix('v1/admin/auth')->group(function () {
    Route::post('login', [\App\Http\Controllers\Api\AuthController::class, 'login'])
        ->middleware('throttle:10,1');
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
        Route::get('me', [\App\Http\Controllers\Api\AuthController::class, 'me']);
    });
});
