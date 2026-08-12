<?php

namespace App\Http\Controllers\Api;

use App\Exports\ComisionesExport;
use App\Exports\DespachosExport;
use App\Http\Controllers\Controller;
use App\Models\ComisionMotorizado;
use App\Models\Despacho;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ReportesController extends Controller
{
    // GET /admin/reportes/despachos/pdf
    public function despachosPdf(Request $request)
    {
        $despachos = $this->consultarDespachos($request)->get();
        $rango = $this->labelRango($request);

        $pdf = Pdf::loadView('reportes.despachos', [
            'despachos' => $despachos,
            'rango'     => $rango,
            'generado'  => now()->format('d/m/Y H:i'),
            'total'     => $despachos->sum(fn($d) => $d->order_data['total'] ?? 0),
        ])->setPaper('a4', 'landscape');

        return $pdf->download("despachos-{$rango['slug']}.pdf");
    }

    // GET /admin/reportes/despachos/excel
    public function despachosExcel(Request $request)
    {
        $despachos = $this->consultarDespachos($request)->get();
        $rango = $this->labelRango($request);

        return Excel::download(new DespachosExport($despachos), "despachos-{$rango['slug']}.xlsx");
    }

    // GET /admin/reportes/comisiones/pdf
    public function comisionesPdf(Request $request)
    {
        $comisiones = $this->consultarComisiones($request)->get();
        $rango = $this->labelRango($request);

        $pdf = Pdf::loadView('reportes.comisiones', [
            'comisiones' => $comisiones,
            'rango'      => $rango,
            'generado'   => now()->format('d/m/Y H:i'),
            'totalPendiente' => $comisiones->where('estado', 'pendiente')->sum('monto'),
            'totalCobrado'   => $comisiones->where('estado', 'cobrado')->sum('monto'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download("comisiones-{$rango['slug']}.pdf");
    }

    // GET /admin/reportes/comisiones/excel
    public function comisionesExcel(Request $request)
    {
        $comisiones = $this->consultarComisiones($request)->get();
        $rango = $this->labelRango($request);

        return Excel::download(new ComisionesExport($comisiones), "comisiones-{$rango['slug']}.xlsx");
    }

    // ── Helpers compartidos ────────────────────────────────────

    private function consultarDespachos(Request $request)
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

        return $query->orderByDesc('created_at');
    }

    private function consultarComisiones(Request $request)
    {
        $query = ComisionMotorizado::with(['motorizado', 'despacho.negocio']);

        if ($request->filled('desde') && $request->filled('hasta')) {
            $desde = Carbon::parse($request->query('desde'))->startOfDay();
            $hasta = Carbon::parse($request->query('hasta'))->endOfDay();
            $query->whereBetween('created_at', [$desde, $hasta]);
        }

        if ($request->filled('motorizado_id')) {
            $query->where('motorizado_id', $request->query('motorizado_id'));
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->query('estado'));
        }

        return $query->orderByDesc('created_at');
    }

    private function labelRango(Request $request): array
    {
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');

        if ($desde && $hasta) {
            return [
                'texto' => "Del {$desde} al {$hasta}",
                'slug'  => "{$desde}_a_{$hasta}",
            ];
        }

        return ['texto' => 'Histórico completo', 'slug' => 'historico'];
    }
}
