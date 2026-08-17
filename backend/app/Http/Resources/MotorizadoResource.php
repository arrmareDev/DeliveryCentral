<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MotorizadoResource extends JsonResource
{
    protected bool $withStats;

    public function __construct($resource, bool $withStats = false)
    {
        parent::__construct($resource);
        $this->withStats = $withStats;
    }

    public function toArray($request): array
    {
        $data = [
            'id'                => $this->id,
            'nombre'            => $this->nombre,
            'nombres'           => $this->nombres,
            'apellidos'         => $this->apellidos,
            'dni'               => $this->dni,
            'fecha_nacimiento'  => $this->fecha_nacimiento?->toDateString(),
            'telefono'          => $this->telefono,
            'email'             => $this->email,
            'foto'              => $this->foto,
            'estado'            => $this->estado,
            'verificado'        => $this->verificado,
            'activo'            => $this->activo,
            'lat'               => $this->lat,
            'lng'               => $this->lng,
            'ultimo_ping'       => $this->ultimo_ping?->toISOString(),
            'email_verificado'  => $this->hasVerifiedEmail(),
            'placa'             => $this->placa,
            'marca_vehiculo'    => $this->marca_vehiculo,
            'modelo_vehiculo'   => $this->modelo_vehiculo,
            'anio_vehiculo'     => $this->anio_vehiculo,
            'foto_vehiculo'     => $this->foto_vehiculo ? asset('storage/' . $this->foto_vehiculo) : null,
            'soat_numero'       => $this->soat_numero,
            'created_at'        => $this->created_at?->toISOString(),
        ];

        if ($this->withStats) {
            $data['stats'] = [
                'total_entregas'  => $this->despachos()->where('estado', 'entregado')->count(),
                'entregas_hoy'    => $this->despachosHoy()->count(),
                'deuda_pendiente' => (float) $this->deudaPendiente(),
            ];
        }

        return $data;
    }
}
