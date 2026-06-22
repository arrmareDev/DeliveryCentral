<?php

namespace App\Jobs;

use App\Models\Despacho;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotifyRestaurantWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 5;

    public function __construct(public Despacho $despacho) {}

    public function handle(): void
    {
        $restaurant = $this->despacho->restaurant;

        if (!$restaurant?->webhook_url) {
            return;
        }

        try {
            $payload = [
                'despacho_id'   => $this->despacho->id,
                'order_id'      => $this->despacho->external_order_id,
                'estado'        => $this->despacho->estado,
                'aceptado_at'   => $this->despacho->aceptado_at?->toISOString(),
                'recogido_at'   => $this->despacho->recogido_at?->toISOString(),
                'entregado_at'  => $this->despacho->entregado_at?->toISOString(),
                'monto_cobrado' => $this->despacho->monto_cobrado,
                'motorizado'    => $this->despacho->motorizado ? [
                    'id'       => $this->despacho->motorizado->id,
                    'nombre'   => $this->despacho->motorizado->nombre,
                    'telefono' => $this->despacho->motorizado->telefono,
                ] : null,
            ];

            $signature = hash_hmac(
                'sha256',
                json_encode($payload),
                $restaurant->webhook_secret ?? ''
            );

            $response = Http::timeout(10)
                ->withHeaders(['X-Webhook-Signature' => $signature])
                ->post($restaurant->webhook_url, $payload);

            if (!$response->successful()) {
                Log::warning('Webhook falló', [
                    'restaurant' => $restaurant->slug,
                    'status'     => $response->status(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Error enviando webhook: ' . $e->getMessage());
        }
    }
}
