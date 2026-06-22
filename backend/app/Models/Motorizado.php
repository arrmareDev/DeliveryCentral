<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Motorizado extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'nombre',
        'telefono',
        'email',
        'password',
        'foto',
        'estado',
        'verificado',
        'activo',
        'lat',
        'lng',
        'ultimo_ping',
        'push_token',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'verificado'  => 'boolean',
        'activo'      => 'boolean',
        'lat'         => 'float',
        'lng'         => 'float',
        'ultimo_ping' => 'datetime',
    ];

    public function isDisponible(): bool
    {
        return $this->estado === 'disponible' && $this->verificado && $this->activo;
    }

    public function despachos()
    {
        return $this->hasMany(Despacho::class);
    }

    public function despachosHoy()
    {
        return $this->despachos()
            ->where('estado', 'entregado')
            ->whereDate('entregado_at', today());
    }

    public function comisiones()
    {
        return $this->hasMany(ComisionMotorizado::class);
    }

    public function deudaPendiente()
    {
        return $this->comisiones()
            ->where('estado', 'pendiente')
            ->sum('monto');
    }
}
