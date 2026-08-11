<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Despacho extends Model
{
    use HasFactory;

    protected $fillable = [
        'negocio_id',
        'external_order_id',
        'motorizado_id',
        'estado',
        'motivo_cancelacion',
        'order_data',
        'comision_motorizado',
        'monto_cobrado',
        'nota_motorizado',
        'solicitado_at',
        'aceptado_at',
        'recogido_at',
        'entregado_at',
    ];

    protected $casts = [
        'order_data'           => 'array',
        'comision_motorizado'  => 'float',
        'monto_cobrado'        => 'float',
        'solicitado_at'        => 'datetime',
        'aceptado_at'          => 'datetime',
        'recogido_at'          => 'datetime',
        'entregado_at'         => 'datetime',
    ];

    public function negocio()
    {
        return $this->belongsTo(Negocio::class);
    }

    public function motorizado()
    {
        return $this->belongsTo(Motorizado::class);
    }

    public function comision()
    {
        return $this->hasOne(ComisionMotorizado::class);
    }
}
