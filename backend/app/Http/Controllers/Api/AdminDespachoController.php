<?php

namespace App\Http\Controllers\Api;

use App\Events\DespachoActualizado;
use App\Http\Controllers\Controller;
use App\Http\Resources\DespachoResource;
use App\Http\Resources\MotorizadoResource;
use App\Jobs\NotifyNegocioWebhook;
use App\Models\Despacho;
use App\Models\Motorizado;
use App\Models\NotificacionAdmin;
use App\Notifications\PedidoAsignadoNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

// Endpoints exclusivos del panel de administración de Central —
// listados globales, gestión de motorizados, cancelación con motivo.
class AdminDespachoController extends Controller
{
    // GET /admin/despachos
    public function index(Request $request): JsonResponse
    {
        $query = Despacho::with(['negocio', 'motorizado']);

        if ($request->filled('negocio_id')) {
            $query->where('negocio_id', $request->negocio_id);
        }

        $activos = (clone $query)
            ->whereNotIn('estado', ['entregado', 'cancelado'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($d) => (new DespachoResource($d))->resolve());

        $entregadosHoy = (clone $query)
            ->where('estado', 'entregado')
            ->whereDate('entregado_at', today())
            ->orderByDesc('entregado_at')
            ->get()
            ->map(fn($d) => (new DespachoResource($d))->resolve());

        return $this->success([
            'activos'        => $activos,
            'entregados_hoy' => $entregadosHoy,
            'stats'          => [
                'total_activos'           => $activos->count(),
                'entregados_hoy'          => $entregadosHoy->count(),
                'motorizados_ocupados'    => Motorizado::where('estado', 'ocupado')->count(),
                'motorizados_disponibles' => Motorizado::where('estado', 'disponible')->count(),
            ],
        ]);
    }

    // POST /admin/despachos/{id}/asignar — el admin elige directo a qué
    // motorizado va el pedido, sin esperar a que alguien lo acepte solo.
    // Útil cuando nadie toma un pedido pasado cierto tiempo.
    public function asignar(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'motorizado_id' => 'required|integer|exists:motorizados,id',
        ]);

        $maxSimultaneos = (int) config('services.motorizado.max_despachos_simultaneos', 3);

        return DB::transaction(function () use ($id, $data, $maxSimultaneos) {
            // Mismo bloqueo que aceptar() — evita que una asignación manual
            // choque con que el propio motorizado acepte otro pedido casi
            // al mismo tiempo.
            $motorizado = Motorizado::where('id', $data['motorizado_id'])->lockForUpdate()->first();
            if (!$motorizado) return $this->notFound('Motorizado no encontrado');

            if (!$motorizado->verificado) {
                return $this->error('Ese motorizado no está verificado', 422);
            }

            $despacho = Despacho::with('negocio')->where('id', $id)->lockForUpdate()->first();
            if (!$despacho) return $this->notFound('Despacho no encontrado');

            if ($despacho->estado !== 'solicitado') {
                return $this->error('Este pedido ya no está disponible para asignar', 409);
            }

            $activosCount = Despacho::where('motorizado_id', $motorizado->id)
                ->whereNotIn('estado', ['entregado', 'cancelado'])
                ->count();

            if ($activosCount >= $maxSimultaneos) {
                return $this->error("Ese motorizado ya tiene {$maxSimultaneos} pedidos activos — el máximo permitido", 422);
            }

            $despacho->update([
                'motorizado_id' => $motorizado->id,
                'estado'        => 'aceptado',
                'aceptado_at'   => now(),
            ]);

            if ($activosCount + 1 >= $maxSimultaneos) {
                $motorizado->update(['estado' => 'ocupado']);
            }

            $despacho->load('motorizado', 'negocio');
            broadcast(new DespachoActualizado($despacho));
            NotifyNegocioWebhook::dispatch($despacho);

            try {
                $motorizado->notify(new PedidoAsignadoNotification($despacho));
            } catch (\Throwable $e) {
                report($e);
            }

            NotificacionAdmin::crear(
                'pedido_asignado',
                'Pedido asignado manualmente',
                "Pedido #{$despacho->external_order_id} asignado a {$motorizado->nombre}",
                ['despacho_id' => $despacho->id, 'motorizado_id' => $motorizado->id],
            );

            return $this->success((new DespachoResource($despacho))->resolve(), 'Pedido asignado');
        });
    }

    // GET /admin/motorizados/disponibles — lista liviana para el selector
    // del modal de asignación manual (no la tabla paginada completa).
    public function motorizadosDisponibles(): JsonResponse
    {
        $maxSimultaneos = (int) config('services.motorizado.max_despachos_simultaneos', 3);

        $motorizados = Motorizado::where('verificado', true)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get()
            ->map(function ($m) use ($maxSimultaneos) {
                $activos = $m->despachos()->whereNotIn('estado', ['entregado', 'cancelado'])->count();
                return [
                    'id'             => $m->id,
                    'nombre'         => $m->nombre,
                    'estado'         => $m->estado,
                    'activos'        => $activos,
                    'max'            => $maxSimultaneos,
                    'puede_recibir'  => $activos < $maxSimultaneos,
                ];
            });

        return $this->success($motorizados);
    }

    // GET /admin/motorizados
    public function motorizados(Request $request): JsonResponse
    {
        $query = Motorizado::query();

        // Búsqueda por nombre, teléfono, DNI o placa
        if ($request->filled('buscar')) {
            $term = $request->query('buscar');
            $query->where(function ($q) use ($term) {
                $q->where('nombre', 'ilike', "%{$term}%")
                    ->orWhere('telefono', 'ilike', "%{$term}%")
                    ->orWhere('dni', 'ilike', "%{$term}%")
                    ->orWhere('placa', 'ilike', "%{$term}%");
            });
        }

        // Filtro por estado de verificación
        $filtroEstado = $request->query('filtro_estado');
        if ($filtroEstado === 'pendiente') {
            $query->where('verificado', false);
        } elseif ($filtroEstado === 'verificado') {
            $query->where('verificado', true)->where('activo', true);
        } elseif ($filtroEstado === 'inactivo') {
            $query->where('verificado', true)->where('activo', false);
        }

        $query->orderByDesc('created_at');

        $perPage = min((int) $request->query('per_page', 12), 50);
        $paginated = $query->paginate($perPage);

        return $this->success([
            'data' => collect($paginated->items())->map(fn($m) => (new MotorizadoResource($m, true))->resolve()),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'total'        => $paginated->total(),
                'per_page'     => $paginated->perPage(),
            ],
            'stats' => [
                'total'        => Motorizado::count(),
                'verificados'  => Motorizado::where('verificado', true)->count(),
                'disponibles'  => Motorizado::where('estado', 'disponible')->count(),
                'pendientes'   => Motorizado::where('verificado', false)->count(),
            ],
        ]);
    }

    // PATCH /admin/motorizados/{id}/verificar
    public function verificar(int $id): JsonResponse
    {
        $motorizado = Motorizado::findOrFail($id);
        $motorizado->update([
            'verificado' => !$motorizado->verificado,
            'activo'     => !$motorizado->verificado,
        ]);

        return $this->success(
            (new MotorizadoResource($motorizado, true))->resolve(),
            $motorizado->verificado ? 'Motorizado verificado' : 'Verificación removida'
        );
    }

    // PATCH /admin/motorizados/{id}/toggle-activo
    public function toggleActivo(int $id): JsonResponse
    {
        $motorizado = Motorizado::findOrFail($id);

        if (!$motorizado->verificado) {
            return $this->error('Debes verificar al motorizado primero', 422);
        }

        $motorizado->update(['activo' => !$motorizado->activo]);

        return $this->success(
            (new MotorizadoResource($motorizado, true))->resolve(),
            $motorizado->activo ? 'Motorizado activado' : 'Motorizado desactivado'
        );
    }

    // POST /admin/despachos/{id}/cancelar
    public function cancelar(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'motivo' => 'required|string|max:255',
        ]);

        $despacho = Despacho::with('negocio')->findOrFail($id);
        $despacho->update([
            'estado'             => 'cancelado',
            'motivo_cancelacion' => $data['motivo'],
        ]);

        if ($despacho->motorizado_id) {
            Motorizado::where('id', $despacho->motorizado_id)
                ->update(['estado' => 'disponible']);
        }

        broadcast(new DespachoActualizado($despacho));

        NotificacionAdmin::crear(
            'despacho_cancelado',
            'Despacho cancelado',
            "Pedido #{$despacho->external_order_id} cancelado: {$data['motivo']}",
            ['despacho_id' => $despacho->id],
        );

        NotifyNegocioWebhook::dispatch($despacho);

        return $this->success((new DespachoResource($despacho))->resolve(), 'Despacho cancelado');
    }

    // GET /admin/despachos/historial — historial completo, paginado y filtrable
    public function historialCentral(Request $request): JsonResponse
    {
        $query = Despacho::with(['negocio', 'motorizado']);

        if ($request->filled('desde') && $request->filled('hasta')) {
            $desde = Carbon::parse($request->query('desde'))->startOfDay();
            $hasta = Carbon::parse($request->query('hasta'))->endOfDay();
            $query->whereBetween('created_at', [$desde, $hasta]);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->query('estado'));
        }

        if ($request->filled('negocio_id')) {
            $query->where('negocio_id', $request->query('negocio_id'));
        }

        if ($request->filled('motorizado_id')) {
            $query->where('motorizado_id', $request->query('motorizado_id'));
        }

        if ($request->filled('buscar')) {
            $term = $request->query('buscar');
            $query->where(function ($q) use ($term) {
                $q->where('external_order_id', 'ilike', "%{$term}%")
                    ->orWhereRaw("order_data->>'client_name' ilike ?", ["%{$term}%"]);
            });
        }

        $sortDir = $request->query('sort_dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy('created_at', $sortDir);

        $perPage = min((int) $request->query('per_page', 12), 50);
        $paginated = $query->paginate($perPage);

        return $this->success([
            'data' => collect($paginated->items())->map(fn($d) => (new DespachoResource($d))->resolve()),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'total'        => $paginated->total(),
                'per_page'     => $paginated->perPage(),
            ],
        ]);
    }
}
