<?php

namespace App\Events;

use App\Models\Despacho;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PedidoDisponible implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Despacho $despacho)
    {
        $this->despacho->loadMissing('restaurant');
    }

    public function broadcastOn(): array
    {
        return [new Channel('motorizados')];
    }

    public function broadcastAs(): string
    {
        return 'pedido.disponible';
    }

    public function broadcastWith(): array
    {
        Log::info('PedidoDisponible - EJECUTANDO broadcastWith');

        $orderData = $this->despacho->order_data ?? [];

        Log::info('PedidoDisponible - payload', [
            'restaurant_name' => $this->despacho->restaurant?->name,
            'order_data'      => $orderData,
        ]);

        return [
            'id'                  => $this->despacho->id,
            'restaurant'          => $this->despacho->restaurant?->name,
            'order_id'            => $this->despacho->external_order_id,
            'estado'              => $this->despacho->estado,
            'comision_motorizado' => (float) $this->despacho->comision_motorizado,
            'monto_cobrado'       => $this->despacho->monto_cobrado !== null
                ? (float) $this->despacho->monto_cobrado
                : null,
            'nota_motorizado'     => $this->despacho->nota_motorizado,
            'solicitado_at'       => $this->despacho->solicitado_at?->toISOString(),
            'aceptado_at'         => $this->despacho->aceptado_at?->toISOString(),
            'recogido_at'         => $this->despacho->recogido_at?->toISOString(),
            'entregado_at'        => $this->despacho->entregado_at?->toISOString(),
            'order'               => [
                'client_name'  => $orderData['client_name']  ?? null,
                'client_phone' => $orderData['client_phone'] ?? null,
                'address'      => $orderData['address']      ?? null,
                'district'     => $orderData['district']     ?? null,
                'reference'    => $orderData['reference']    ?? null,
                'subtotal'     => isset($orderData['subtotal']) ? (float) $orderData['subtotal'] : null,
                'delivery_fee' => isset($orderData['delivery_fee']) ? (float) $orderData['delivery_fee'] : null,
                'total'        => (float) ($orderData['total'] ?? 0),
                'metodo_pago'  => $orderData['metodo_pago'] ?? null,
                'lat'          => $orderData['lat']  ?? null,
                'lng'          => $orderData['lng']  ?? null,
                'note'         => $orderData['note'] ?? null,
                'items'        => $orderData['items'] ?? [],
            ],
            'motorizado'          => null,
        ];
    }
}
