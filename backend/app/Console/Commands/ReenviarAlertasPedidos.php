<?php

namespace App\Console\Commands;

use App\Models\Despacho;
use App\Models\Motorizado;
use App\Notifications\NuevoPedidoDisponibleNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ReenviarAlertasPedidos extends Command
{
    protected $signature = 'despachos:reenviar-alertas';

    protected $description = 'Reenvía la notificación push de pedido disponible a los motorizados '
        . 'cuando un despacho lleva más de 1 minuto sin ser aceptado, y sigue reenviando '
        . 'cada minuto mientras nadie lo tome.';

    public function handle(): void
    {
        $pendientes = Despacho::where('estado', 'solicitado')
            ->where('solicitado_at', '<=', now()->subMinute())
            ->where(function ($q) {
                $q->whereNull('ultima_alerta_at')
                    ->orWhere('ultima_alerta_at', '<=', now()->subMinute());
            })
            ->get();

        if ($pendientes->isEmpty()) {
            return;
        }

        $motorizados = Motorizado::where('verificado', true)
            ->whereNotNull('email_verified_at')
            ->where('estado', 'disponible')
            ->get();

        foreach ($pendientes as $despacho) {
            try {
                Notification::send($motorizados, new NuevoPedidoDisponibleNotification($despacho));
            } catch (\Throwable $e) {
                Log::warning("No se pudo reenviar alerta del despacho {$despacho->id}: " . $e->getMessage());
            }

            $despacho->update(['ultima_alerta_at' => now()]);
        }

        $this->info("Alerta reenviada para {$pendientes->count()} despacho(s) sin aceptar.");
    }
}
