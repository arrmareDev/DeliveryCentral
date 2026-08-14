<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DespachoResource extends JsonResource
{
    public function toArray($request): array
    {
        $orderData = $this->order_data ?? [];

        return [
            'id'                  => $this->id,
            'negocio_id'          => $this->negocio_id,
            'negocio'             => $this->negocio?->name,
            'order_id'            => $this->external_order_id,
            'estado'              => $this->estado,
            'motivo_cancelacion'  => $this->motivo_cancelacion,
            'comision_motorizado' => (float) $this->comision_motorizado,
            'monto_cobrado'       => $this->monto_cobrado !== null ? (float) $this->monto_cobrado : null,
            'nota_motorizado'     => $this->nota_motorizado,
            'solicitado_at'       => $this->solicitado_at?->toISOString(),
            'aceptado_at'         => $this->aceptado_at?->toISOString(),
            'recogido_at'         => $this->recogido_at?->toISOString(),
            'entregado_at'        => $this->entregado_at?->toISOString(),
            'order'               => [
                'client_name'  => $orderData['client_name']  ?? null,
                'client_phone' => $orderData['client_phone'] ?? null,
                'address'      => $orderData['address']      ?? null,
                'district'     => $orderData['district']     ?? null,
                'reference'    => $orderData['reference']    ?? null,
                'subtotal'     => isset($orderData['subtotal']) ? (float) $orderData['subtotal'] : null,
                'delivery_fee' => isset($orderData['delivery_fee']) ? (float) $orderData['delivery_fee'] : null,
                'total'        => (float) ($orderData['total'] ?? 0),
                'metodo_pago'  => $orderData['metodo_pago']   ?? null,
                'pagado'       => $orderData['pagado']        ?? null,
                'lat'          => $orderData['lat']           ?? null,
                'lng'          => $orderData['lng']           ?? null,
                'note'         => $orderData['note']          ?? null,
                'items'        => $orderData['items']         ?? [],
            ],
            'motorizado' => $this->motorizado ? [
                'id'       => $this->motorizado->id,
                'nombre'   => $this->motorizado->nombre,
                'telefono' => $this->motorizado->telefono,
                'foto'     => $this->motorizado->foto,
            ] : null,
        ];
    }
}
