<?php

namespace App\Http\Controllers\Api;

use App\Events\DespachoActualizado;
use App\Events\PedidoDisponible;
use App\Http\Controllers\Controller;
use App\Http\Resources\DespachoResource;
use App\Models\ConfiguracionCentral;
use App\Models\Despacho;
use App\Models\Motorizado;
use App\Models\NotificacionAdmin;
use App\Notifications\NuevoPedidoDisponibleNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

// Endpoints que llama el backend de un negocio cliente (ej. Birds),
// autenticado con su api_key vía el middleware negocio.auth.
class NegocioDespachoController extends Controller
{
    // POST /v1/despachos/solicitar  (auth: negocio.auth)
    public function solicitar(Request $request): JsonResponse
    {
        $negocio = $request->attributes->get('negocio');

        $data = $request->validate([
            'order_id'              => 'required|integer',
            'order_data'             => 'required|array',
            'order_data.client_name'  => 'required|string',
            'order_data.client_phone' => 'required|string',
            'order_data.address'      => 'nullable|string',
            'order_data.district'     => 'nullable|string',
            'order_data.reference'    => 'nullable|string',
            'order_data.subtotal'     => 'nullable|numeric|min:0',
            'order_data.delivery_fee' => 'nullable|numeric|min:0',
            'order_data.total'        => 'required|numeric|min:0',
            'order_data.metodo_pago'  => 'nullable|string',
            'order_data.pagado'       => 'nullable|boolean',
            'order_data.lat'          => 'nullable|numeric',
            'order_data.lng'          => 'nullable|numeric',
            'order_data.items'        => 'nullable|array',
            'order_data.note'         => 'nullable|string',
        ]);

        // Evitar duplicar solicitud activa para el mismo pedido
        $existente = Despacho::where('negocio_id', $negocio->id)
            ->where('external_order_id', $data['order_id'])
            ->whereNotIn('estado', ['entregado', 'cancelado'])
            ->first();

        if ($existente) {
            return $this->success(
                (new DespachoResource($existente))->resolve(),
                'Ya existe un despacho activo para este pedido'
            );
        }

        $comisionDefault = (float) ConfiguracionCentral::get('comision_por_entrega', 0.50);

        $despacho = Despacho::create([
            'negocio_id'        => $negocio->id,
            'external_order_id'    => $data['order_id'],
            'estado'               => 'solicitado',
            'order_data'           => $data['order_data'],
            'comision_motorizado'  => $comisionDefault,
            'solicitado_at'        => now(),
        ]);

        broadcast(new PedidoDisponible($despacho));

        // Push a todos los motorizados verificados y disponibles — con la
        // pantalla apagada o la app cerrada, esto es lo único que de
        // verdad les avisa (el WebSocket solo funciona con la app abierta).
        // Si falla, el despacho ya quedó creado — no debe romper la
        // respuesta al negocio que lo solicitó.
        try {
            $motorizados = Motorizado::where('verificado', true)
                ->whereNotNull('email_verified_at')
                ->where('estado', 'disponible')
                ->get();

            Notification::send($motorizados, new NuevoPedidoDisponibleNotification($despacho));
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar notificación push de pedido disponible: ' . $e->getMessage());
        }

        NotificacionAdmin::crear(
            'nuevo_despacho',
            'Nuevo pedido solicitado',
            "{$negocio->name} solicitó un despacho para el pedido #{$data['order_id']}",
            ['despacho_id' => $despacho->id, 'negocio_id' => $negocio->id],
        );

        return $this->created((new DespachoResource($despacho))->resolve(), 'Despacho solicitado');
    }

    // GET /v1/despachos/{order_id}/estado  (auth: negocio.auth)
    public function estadoPorOrderId(Request $request, int $orderId): JsonResponse
    {
        $negocio = $request->attributes->get('negocio');

        $despacho = Despacho::where('negocio_id', $negocio->id)
            ->where('external_order_id', $orderId)
            ->latest()
            ->first();

        if (!$despacho) return $this->notFound('Sin despacho para este pedido');

        return $this->success((new DespachoResource($despacho))->resolve());
    }

    // POST /v1/despachos/{order_id}/cancelar  (auth: negocio.auth)
    public function cancelarPorNegocio(Request $request, int $orderId): JsonResponse
    {
        $negocio = $request->attributes->get('negocio');

        $despacho = Despacho::where('negocio_id', $negocio->id)
            ->where('external_order_id', $orderId)
            ->whereNotIn('estado', ['entregado', 'cancelado'])
            ->first();

        if (!$despacho) return $this->notFound('Sin despacho activo para este pedido');

        $despacho->update(['estado' => 'cancelado']);

        if ($despacho->motorizado_id) {
            Motorizado::where('id', $despacho->motorizado_id)
                ->update(['estado' => 'disponible']);
        }

        broadcast(new DespachoActualizado($despacho));

        NotificacionAdmin::crear(
            'despacho_cancelado',
            'El negocio canceló un pedido',
            "{$negocio->name} canceló el pedido #{$orderId}",
            ['despacho_id' => $despacho->id],
        );

        return $this->success((new DespachoResource($despacho))->resolve(), 'Despacho cancelado');
    }
}
