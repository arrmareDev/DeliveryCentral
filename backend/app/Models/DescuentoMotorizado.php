<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DescuentoMotorizado extends Model
{
    use HasFactory;

    protected $table = 'descuentos_motorizado';

    protected $fillable = [
        'motorizado_id',
        'monto',
        'motivo',
    ];

    protected $casts = [
        'monto' => 'float',
    ];

    public function motorizado()
    {
        return $this->belongsTo(Motorizado::class);
    }
}
