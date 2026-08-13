<?php

namespace App\Notifications;

use App\Models\Despacho;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class NuevoPedidoDisponibleNotification extends Notification
{
    public function __construct(private Despacho $despacho) {}

    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        $orderData = $this->despacho->order_data ?? [];
        $negocio   = $this->despacho->negocio?->name ?? 'Negocio';
        $distrito  = $orderData['district'] ?? '';

        return (new WebPushMessage)
            ->title('🛵 Nuevo pedido disponible')
            ->icon('/manifest-icon-192.maskable.png')
            ->body("{$negocio}" . ($distrito ? " · {$distrito}" : ''))
            ->action('Ver pedido', 'ver_pedido')
            ->data(['url' => '/'])
            ->options(['TTL' => 120]);
    }
}
