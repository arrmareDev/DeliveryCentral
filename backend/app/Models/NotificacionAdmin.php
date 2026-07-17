<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificacionAdmin extends Model
{
    protected $table = 'notificaciones_admin';

    protected $fillable = ['tipo', 'titulo', 'mensaje', 'data', 'leido'];

    protected $casts = [
        'data'  => 'array',
        'leido' => 'boolean',
    ];

    // Helper central: crea la notificación Y la transmite en tiempo real,
    // para no repetir esta pareja de líneas en cada punto donde se dispara.
    public static function crear(string $tipo, string $titulo, string $mensaje, array $data = []): self
    {
        $notificacion = static::create([
            'tipo'    => $tipo,
            'titulo'  => $titulo,
            'mensaje' => $mensaje,
            'data'    => $data,
        ]);

        broadcast(new \App\Events\AdminNotificacionCreada($notificacion));

        return $notificacion;
    }
}
