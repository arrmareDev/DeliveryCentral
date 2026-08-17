<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Reenvía la alerta push de pedidos sin aceptar — cada minuto,
// mientras nadie tome el pedido.
Schedule::command('despachos:reenviar-alertas')->everyMinute();
