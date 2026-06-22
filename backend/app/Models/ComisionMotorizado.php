<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComisionMotorizado extends Model
{
    use HasFactory;

    protected $table = 'comisiones_motorizado';

    protected $fillable = [
        'despacho_id',
        'motorizado_id',
        'monto',
        'estado',
        'cobrado_at',
        'cobrado_por',
    ];

    protected $casts = [
        'monto'      => 'float',
        'cobrado_at' => 'datetime',
    ];

    public function despacho()
    {
        return $this->belongsTo(Despacho::class);
    }

    public function motorizado()
    {
        return $this->belongsTo(Motorizado::class);
    }
}
