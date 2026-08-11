<?php

use Illuminate\Support\Facades\Broadcast;

// Canal público — todos los motorizados conectados pueden escuchar
Broadcast::channel('motorizados', function () {
    return true;
});

// Canal privado por motorizado — solo él puede escuchar su propio canal
Broadcast::channel('motorizado.{id}', function ($user, $id) {
    return $user instanceof \App\Models\Motorizado && (int) $user->id === (int) $id;
});

// Canal de tu panel superadmin
Broadcast::channel('admin.despachos', function ($user) {
    return $user instanceof \App\Models\User;
});

// Canal por negocio — si en el futuro quieres frontend escuchando directo
Broadcast::channel('negocio.{id}', function ($user, $id) {
    return true; // se valida por API key en otra capa si se necesita
});
