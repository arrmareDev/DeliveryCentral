<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ComisionMotorizado;
use App\Models\ConfiguracionCentral;
use App\Models\Motorizado;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ComisionController extends Controller
{
    // GET /admin/comisiones — resumen por motorizado, filtrable por rango
    public function index(Request $request): JsonResponse
    {
        [$desde, $hasta] = $this->resolverRango($request);

        $motorizados = Motorizado::withSum(
            ['comisiones as deuda_pendiente' => function ($q) use ($desde, $hasta) {
                $q->where('estado', 'pendiente');
                $this->aplicarRango($q, $desde, $hasta, 'created_at');
            }],
            'monto'
        )->withSum(
            ['comisiones as total_cobrado' => function ($q) use ($desde, $hasta) {
                $q->where('estado', 'cobrado');
                $this->aplicarRango($q, $desde, $hasta, 'cobrado_at');
            }],
            'monto'
        )->withCount(
            ['comisiones as comisiones_pendientes_count' => function ($q) use ($desde, $hasta) {
                $q->where('estado', 'pendiente');
                $this->aplicarRango($q, $desde, $hasta, 'created_at');
            }]
        )->get()
            ->filter(fn($m) => $m->deuda_pendiente > 0 || $m->total_cobrado > 0)
            ->values()
            ->map(fn($m) => [
                'id'                     => $m->id,
                'nombre'                 => $m->nombre,
                'telefono'               => $m->telefono,
                'deuda_pendiente'        => (float) ($m->deuda_pendiente ?? 0),
                'total_cobrado'          => (float) ($m->total_cobrado ?? 0),
                'comisiones_pendientes'  => (int) $m->comisiones_pendientes_count,
            ]);

        return $this->success([
            'motorizados' => $motorizados,
            'rango'       => ['desde' => $desde?->toDateString(), 'hasta' => $hasta?->toDateString()],
        ]);
    }

    // GET /admin/comisiones/{motorizado_id} — detalle filtrable por rango
    public function detalle(Request $request, int $motorizadoId): JsonResponse
    {
        $motorizado = Motorizado::findOrFail($motorizadoId);
        [$desde, $hasta] = $this->resolverRango($request);

        $query = ComisionMotorizado::with('despacho.restaurant')
            ->where('motorizado_id', $motorizadoId);

        if ($desde && $hasta) {
            $query->where(function ($q) use ($desde, $hasta) {
                $q->whereBetween('created_at', [$desde, $hasta])
                    ->orWhereBetween('cobrado_at', [$desde, $hasta]);
            });
        }

        $comisiones = $query->orderByDesc('created_at')
            ->get()
            ->map(fn($c) => [
                'id'          => $c->id,
                'despacho_id' => $c->despacho_id,
                'order_id'    => $c->despacho?->external_order_id,
                'restaurant'  => $c->despacho?->restaurant?->name,
                'monto'       => (float) $c->monto,
                'estado'      => $c->estado,
                'cobrado_at'  => $c->cobrado_at?->toISOString(),
                'created_at'  => $c->created_at?->toISOString(),
            ]);

        // Deuda pendiente del rango filtrado (no la deuda total histórica)
        $deudaRango = (clone $query)->getQuery()->wheres
            ? ComisionMotorizado::where('motorizado_id', $motorizadoId)
            ->where('estado', 'pendiente')
            ->when($desde && $hasta, fn($q) => $q->whereBetween('created_at', [$desde, $hasta]))
            ->sum('monto')
            : $motorizado->deudaPendiente();

        return $this->success([
            'motorizado'      => [
                'id'     => $motorizado->id,
                'nombre' => $motorizado->nombre,
            ],
            'deuda_pendiente' => (float) $deudaRango,
            'deuda_total'     => (float) $motorizado->deudaPendiente(),
            'comisiones'      => $comisiones,
            'rango'           => ['desde' => $desde?->toDateString(), 'hasta' => $hasta?->toDateString()],
        ]);
    }

    // POST /admin/comisiones/cobrar — marcar como cobradas, opcionalmente por rango
    public function cobrar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'motorizado_id' => 'required|exists:motorizados,id',
            'comision_ids'  => 'nullable|array',
            'desde'         => 'nullable|date',
            'hasta'         => 'nullable|date',
        ]);

        $query = ComisionMotorizado::where('motorizado_id', $data['motorizado_id'])
            ->where('estado', 'pendiente');

        if (!empty($data['comision_ids'])) {
            $query->whereIn('id', $data['comision_ids']);
        } elseif (!empty($data['desde']) && !empty($data['hasta'])) {
            $desde = Carbon::parse($data['desde'])->startOfDay();
            $hasta = Carbon::parse($data['hasta'])->endOfDay();
            $query->whereBetween('created_at', [$desde, $hasta]);
        }
        // Si no manda comision_ids ni rango, cobra TODAS las pendientes (comportamiento original)

        $count = $query->count();
        $total = $query->sum('monto');

        $query->update([
            'estado'     => 'cobrado',
            'cobrado_at' => now(),
        ]);

        return $this->success([
            'comisiones_cobradas' => $count,
            'total_cobrado'       => (float) $total,
        ], 'Comisiones marcadas como cobradas');
    }

    // GET /admin/config
    public function getConfig(): JsonResponse
    {
        return $this->success([
            'comision_por_entrega' => (float) ConfiguracionCentral::get('comision_por_entrega', 0.50),
        ]);
    }

    // PUT /admin/config
    public function updateConfig(Request $request): JsonResponse
    {
        $data = $request->validate([
            'comision_por_entrega' => 'required|numeric|min:0',
        ]);

        ConfiguracionCentral::set('comision_por_entrega', $data['comision_por_entrega']);

        return $this->success(null, 'Configuración actualizada');
    }

    // ── Helpers de rango de fechas ─────────────────────────────

    /**
     * Resuelve el rango desde query params: ?preset=hoy|semana|mes  o  ?desde=...&hasta=...
     */
    private function resolverRango(Request $request): array
    {
        $preset = $request->query('preset');
        $desdeParam = $request->query('desde');
        $hastaParam = $request->query('hasta');

        if ($desdeParam && $hastaParam) {
            return [
                Carbon::parse($desdeParam)->startOfDay(),
                Carbon::parse($hastaParam)->endOfDay(),
            ];
        }

        return match ($preset) {
            'hoy'     => [now()->startOfDay(), now()->endOfDay()],
            'semana'  => [now()->startOfWeek(), now()->endOfWeek()],
            'mes'     => [now()->startOfMonth(), now()->endOfMonth()],
            default   => [null, null], // sin filtro = histórico completo
        };
    }

    private function aplicarRango($query, ?Carbon $desde, ?Carbon $hasta, string $campo): void
    {
        if ($desde && $hasta) {
            $query->whereBetween($campo, [$desde, $hasta]);
        }
    }
}
