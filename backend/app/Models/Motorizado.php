<?php

namespace App\Models;

use App\Notifications\MotorizadoVerifyEmail;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Motorizado extends Authenticatable implements MustVerifyEmailContract
{
    use HasFactory, HasApiTokens, Notifiable, MustVerifyEmail;

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
        'verificado'         => 'boolean',
        'activo'             => 'boolean',
        'lat'                => 'float',
        'lng'                => 'float',
        'ultimo_ping'        => 'datetime',
        'email_verified_at'  => 'datetime',
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

    // ── Verificación de correo ──────────────────────────────
    // Motorizado usa Sanctum (tokens), no sesiones web, así que el
    // enlace de verificación no debe apuntar a las rutas por defecto
    // de Laravel (`verification.verify`, pensadas para auth de sesión).
    // Este notification personalizado apunta a nuestra propia ruta
    // firmada bajo /v1/motorizado/auth/verify-email/{id}/{hash}.
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new MotorizadoVerifyEmail());
    }
}
