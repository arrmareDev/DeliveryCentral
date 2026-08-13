<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    // GET /vapid-public-key — público, el frontend la necesita para suscribirse
    public function publicKey(): JsonResponse
    {
        return $this->success(['public_key' => config('webpush.vapid.public_key')]);
    }

    // POST /v1/motorizado/push/subscribe
    public function subscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint'        => 'required|string',
            'keys.p256dh'     => 'required|string',
            'keys.auth'       => 'required|string',
            'contentEncoding' => 'nullable|string',
        ]);

        $request->user()->updatePushSubscription(
            $data['endpoint'],
            $data['keys']['p256dh'],
            $data['keys']['auth'],
            $data['contentEncoding'] ?? 'aesgcm',
        );

        return $this->success(null, 'Notificaciones activadas');
    }

    // POST /v1/motorizado/push/unsubscribe
    public function unsubscribe(Request $request): JsonResponse
    {
        $data = $request->validate(['endpoint' => 'required|string']);

        $request->user()->pushSubscriptions()
            ->where('endpoint', $data['endpoint'])
            ->delete();

        return $this->success(null, 'Notificaciones desactivadas');
    }
}
