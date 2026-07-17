<?php

namespace App\Events;

use App\Models\NotificacionAdmin;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminNotificacionCreada implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public NotificacionAdmin $notificacion) {}

    public function broadcastOn(): array
    {
        return [new Channel('admin.notificaciones')];
    }

    public function broadcastAs(): string
    {
        return 'notificacion.creada';
    }

    public function broadcastWith(): array
    {
        return [
            'id'         => $this->notificacion->id,
            'tipo'       => $this->notificacion->tipo,
            'titulo'     => $this->notificacion->titulo,
            'mensaje'    => $this->notificacion->mensaje,
            'data'       => $this->notificacion->data,
            'leido'      => $this->notificacion->leido,
            'created_at' => $this->notificacion->created_at->toISOString(),
        ];
    }
}
