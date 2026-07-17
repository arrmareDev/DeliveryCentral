<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificacionAdmin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    // GET /admin/notificaciones
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 20), 50);
        $paginated = NotificacionAdmin::orderByDesc('created_at')->paginate($perPage);

        return $this->success([
            'data' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'total'        => $paginated->total(),
                'per_page'     => $paginated->perPage(),
            ],
            'no_leidas' => NotificacionAdmin::where('leido', false)->count(),
        ]);
    }

    // PATCH /admin/notificaciones/{id}/leer
    public function leer(int $id): JsonResponse
    {
        $notificacion = NotificacionAdmin::findOrFail($id);
        $notificacion->update(['leido' => true]);

        return $this->success(null, 'Notificación marcada como leída');
    }

    // PATCH /admin/notificaciones/leer-todas
    public function leerTodas(): JsonResponse
    {
        NotificacionAdmin::where('leido', false)->update(['leido' => true]);

        return $this->success(null, 'Todas las notificaciones marcadas como leídas');
    }
}
