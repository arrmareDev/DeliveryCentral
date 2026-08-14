<?php

use App\Http\Controllers\Api\ComisionController;
use App\Http\Controllers\Api\NegocioDespachoController;
use App\Http\Controllers\Api\MotorizadoAuthController;
use App\Http\Controllers\Api\MotorizadoDespachoController;
use App\Http\Controllers\Api\AdminDespachoController;
use App\Http\Controllers\Api\NegocioController;
use App\Http\Controllers\Api\PushSubscriptionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\ReportesController;
use App\Http\Controllers\Api\NotificacionController;
use App\Http\Controllers\Api\BusquedaController;

// ══════════════════════════════════════════════════════════
// ── PÚBLICO ───────────────────────────────────────────────
// ══════════════════════════════════════════════════════════
Route::get('v1/vapid-public-key', [PushSubscriptionController::class, 'publicKey']);

// ══════════════════════════════════════════════════════════
// ── NEGOCIOS CLIENTES — autenticados con API key ─────────────
// ══════════════════════════════════════════════════════════
Route::prefix('v1')
    ->middleware(['negocio.auth', 'throttle:120,1'])
    ->group(function () {
        Route::post('despachos/solicitar', [NegocioDespachoController::class, 'solicitar']);
        Route::get('despachos/{order_id}/estado', [NegocioDespachoController::class, 'estadoPorOrderId'])
            ->where('order_id', '[0-9]+');
        Route::post('despachos/{order_id}/cancelar', [NegocioDespachoController::class, 'cancelarPorNegocio'])
            ->where('order_id', '[0-9]+');
    });

// ══════════════════════════════════════════════════════════
// ── MOTORIZADOS — auth pública ────────────────────────────
// ══════════════════════════════════════════════════════════
Route::prefix('v1/motorizado')->group(function () {

    Route::post('auth/register', [MotorizadoAuthController::class, 'register'])
        ->middleware('throttle:5,1');
    Route::post('auth/login', [MotorizadoAuthController::class, 'login'])
        ->middleware('throttle:10,1');

    // ↓ NUEVO — recuperar contraseña (público, no requiere sesión)
    Route::post('auth/forgot-password', [MotorizadoAuthController::class, 'forgotPassword'])
        ->middleware('throttle:5,1');
    Route::post('auth/reset-password', [MotorizadoAuthController::class, 'resetPassword'])
        ->middleware('throttle:5,1');

    // ↓ NUEVO — el enlace del correo de verificación llega aquí directo,
    // no pasa por el SPA. Protegido por firma (signed), no por Sanctum.
    Route::get('auth/verify-email/{id}/{hash}', [MotorizadoAuthController::class, 'verifyEmail'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('motorizado.verification.verify');

    Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {
        Route::post('auth/logout', [MotorizadoAuthController::class, 'logout']);
        Route::get('me', [MotorizadoAuthController::class, 'me']);
        Route::put('perfil', [MotorizadoDespachoController::class, 'updatePerfil']);

        // ↓ NUEVO — reenviar correo de verificación (requiere estar logueado)
        Route::post('auth/resend-verification', [MotorizadoAuthController::class, 'resendVerification'])
            ->middleware('throttle:3,1');

        Route::patch('estado', [MotorizadoDespachoController::class, 'updateEstado']);
        Route::patch('ubicacion', [MotorizadoDespachoController::class, 'updateUbicacion']);

        Route::get('pedidos', [MotorizadoDespachoController::class, 'pedidosDisponibles']);
        Route::get('despachos/activos', [MotorizadoDespachoController::class, 'despachosActivos']);

        Route::post('push/subscribe', [PushSubscriptionController::class, 'subscribe']);
        Route::post('push/unsubscribe', [PushSubscriptionController::class, 'unsubscribe']);

        Route::post('despachos/{id}/aceptar', [MotorizadoDespachoController::class, 'aceptar'])
            ->where('id', '[0-9]+');
        Route::patch('despachos/{id}/estado', [MotorizadoDespachoController::class, 'updateEstadoDespacho'])
            ->where('id', '[0-9]+');

        // Historial PERSONAL del motorizado (sus propias entregas) —
        // no confundir con el historial global del panel admin de abajo.
        Route::get('historial', [MotorizadoDespachoController::class, 'historial']);
    });
});

// ══════════════════════════════════════════════════════════
// ── TU PANEL SUPERADMIN — auth Sanctum normal (tu usuario) ──
// ══════════════════════════════════════════════════════════
Route::prefix('v1/admin')
    ->middleware(['auth:sanctum', 'throttle:120,1'])
    ->group(function () {

        // Negocios (tus clientes)
        Route::get('negocios', [NegocioController::class, 'index']);
        Route::post('negocios', [NegocioController::class, 'store']);
        Route::get('negocios/{id}', [NegocioController::class, 'show'])
            ->where('id', '[0-9]+');
        Route::put('negocios/{id}', [NegocioController::class, 'update'])
            ->where('id', '[0-9]+');
        Route::post('negocios/{id}/regenerate-key', [NegocioController::class, 'regenerateKey'])
            ->where('id', '[0-9]+');
        Route::delete('negocios/{id}', [NegocioController::class, 'destroy'])
            ->where('id', '[0-9]+');

        // Despachos globales
        Route::get('despachos', [AdminDespachoController::class, 'index']);

        // Historial GLOBAL del panel admin (todos los negocios/motorizados) —
        // método distinto al de arriba (historialCentral, no historial), porque
        // ya existía un método `historial()` para el motorizado individual.
        Route::get('despachos/historial', [AdminDespachoController::class, 'historialCentral']);

        Route::post('despachos/{id}/cancelar', [AdminDespachoController::class, 'cancelar'])
            ->where('id', '[0-9]+');

        // Motorizados
        Route::get('motorizados', [AdminDespachoController::class, 'motorizados']);
        Route::patch('motorizados/{id}/verificar', [AdminDespachoController::class, 'verificar'])
            ->where('id', '[0-9]+');
        Route::patch('motorizados/{id}/toggle-activo', [AdminDespachoController::class, 'toggleActivo'])
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
