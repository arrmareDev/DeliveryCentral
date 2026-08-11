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

class NotifyNegocioWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 5;

    public function __construct(public Despacho $despacho) {}

    public function handle(): void
    {
        $negocio = $this->despacho->negocio;

        if (!$negocio?->webhook_url) {
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
                $negocio->webhook_secret ?? ''
            );

            $response = Http::timeout(10)
                ->withHeaders(['X-Webhook-Signature' => $signature])
                ->post($negocio->webhook_url, $payload);

            if (!$response->successful()) {
                Log::warning('Webhook falló', [
                    'negocio' => $negocio->slug,
                    'status'  => $response->status(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Error enviando webhook: ' . $e->getMessage());
        }
    }
}
