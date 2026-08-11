<?php

namespace App\Events;

use App\Models\Despacho;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DespachoActualizado implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Despacho $despacho) {}

    public function broadcastOn(): array
    {
        $channels = [
            new Channel('admin.despachos'),
            new Channel("negocio.{$this->despacho->negocio_id}"),
        ];

        if ($this->despacho->motorizado_id) {
            $channels[] = new Channel("motorizado.{$this->despacho->motorizado_id}");
        }

        if ($this->despacho->estado !== 'solicitado') {
            $channels[] = new Channel('motorizados');
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'despacho.actualizado';
    }

    public function broadcastWith(): array
    {
        return [
            'despacho_id'      => $this->despacho->id,
            'order_id'         => $this->despacho->external_order_id,
            'estado'           => $this->despacho->estado,
            'aceptado_at'      => $this->despacho->aceptado_at?->toISOString(),
            'recogido_at'      => $this->despacho->recogido_at?->toISOString(),
            'entregado_at'     => $this->despacho->entregado_at?->toISOString(),
            'monto_cobrado'    => $this->despacho->monto_cobrado,
            'motorizado'       => $this->despacho->motorizado ? [
                'id'       => $this->despacho->motorizado->id,
                'nombre'   => $this->despacho->motorizado->nombre,
                'telefono' => $this->despacho->motorizado->telefono,
                'lat'      => $this->despacho->motorizado->lat,
                'lng'      => $this->despacho->motorizado->lng,
            ] : null,
        ];
    }
}
