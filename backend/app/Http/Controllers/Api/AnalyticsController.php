<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ComisionMotorizado;
use App\Models\Despacho;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    // GET /admin/analytics/dashboard
    public function dashboard(Request $request): JsonResponse
    {
        return $this->success([
            'despachos_por_dia'  => $this->despachosPorDia(),
            'metodos_pago'       => $this->distribucionMetodosPago(),
            'comparativa_mensual' => $this->comparativaMensual(),
            'top_motorizados'    => $this->topMotorizadosPorComisiones(),
        ]);
    }

    // Últimos 30 días: total solicitados, entregados, cancelados por día
    private function despachosPorDia(): array
    {
        $desde = now()->subDays(29)->startOfDay();

        $rows = Despacho::selectRaw("
                DATE(created_at) as fecha,
                COUNT(*) as total,
                COUNT(*) FILTER (WHERE estado = 'entregado') as entregados,
                COUNT(*) FILTER (WHERE estado = 'cancelado') as cancelados
            ")
            ->where('created_at', '>=', $desde)
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get()
            ->keyBy(fn($r) => $r->fecha);

        // Rellenamos los días sin datos con ceros, para que la gráfica
        // no tenga huecos y se vea una línea continua de 30 días
        $result = [];
        for ($i = 0; $i < 30; $i++) {
            $fecha = now()->subDays(29 - $i)->toDateString();
            $row = $rows->get($fecha);
            $result[] = [
                'fecha'      => $fecha,
                'total'      => $row ? (int) $row->total : 0,
                'entregados' => $row ? (int) $row->entregados : 0,
                'cancelados' => $row ? (int) $row->cancelados : 0,
            ];
        }

        return $result;
    }

    // Distribución de métodos de pago entre despachos entregados (histórico)
    private function distribucionMetodosPago(): array
    {
        $rows = Despacho::selectRaw("order_data->>'metodo_pago' as metodo, COUNT(*) as total")
            ->where('estado', 'entregado')
            ->whereNotNull(DB::raw("order_data->>'metodo_pago'"))
            ->groupBy('metodo')
            ->get();

        $labels = [
            'anticipado'              => 'Pago anticipado',
            'contraentrega_efectivo'  => 'Efectivo',
            'contraentrega_yape'      => 'Yape/Plin',
        ];

        return $rows->map(fn($r) => [
            'metodo' => $labels[$r->metodo] ?? $r->metodo,
            'total'  => (int) $r->total,
        ])->values()->all();
    }

    // Comparativa: mes actual vs. mes anterior (despachos entregados + comisiones generadas)
    private function comparativaMensual(): array
    {
        $inicioMesActual = now()->startOfMonth();
        $inicioMesAnterior = now()->subMonthNoOverflow()->startOfMonth();
        $finMesAnterior = now()->subMonthNoOverflow()->endOfMonth();

        $mesActual = [
            'despachos' => Despacho::where('estado', 'entregado')
                ->where('entregado_at', '>=', $inicioMesActual)
                ->count(),
            'comisiones' => (float) ComisionMotorizado::where('created_at', '>=', $inicioMesActual)
                ->sum('monto'),
        ];

        $mesAnterior = [
            'despachos' => Despacho::where('estado', 'entregado')
                ->whereBetween('entregado_at', [$inicioMesAnterior, $finMesAnterior])
                ->count(),
            'comisiones' => (float) ComisionMotorizado::whereBetween('created_at', [$inicioMesAnterior, $finMesAnterior])
                ->sum('monto'),
        ];

        return ['mes_actual' => $mesActual, 'mes_anterior' => $mesAnterior];
    }

    // Top 5 motorizados por comisiones generadas (histórico)
    private function topMotorizadosPorComisiones(): array
    {
        return ComisionMotorizado::selectRaw('motorizado_id, SUM(monto) as total')
            ->with('motorizado:id,nombre')
            ->groupBy('motorizado_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn($r) => [
                'nombre' => $r->motorizado?->nombre ?? 'Desconocido',
                'total'  => (float) $r->total,
            ])->all();
    }
}
