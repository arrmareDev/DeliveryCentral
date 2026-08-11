<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Despacho;
use App\Models\Motorizado;
use App\Models\Negocio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BusquedaController extends Controller
{
    // GET /admin/buscar?q=...
    public function index(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));

        if (mb_strlen($term) < 2) {
            return $this->success(['despachos' => [], 'negocios' => [], 'motorizados' => []]);
        }

        return $this->success([
            'despachos'   => $this->buscarDespachos($term),
            'negocios'    => $this->buscarNegocios($term),
            'motorizados' => $this->buscarMotorizados($term),
        ]);
    }

    private function buscarDespachos(string $term): array
    {
        return Despacho::with(['negocio', 'motorizado'])
            ->where(function ($q) use ($term) {
                $q->where('external_order_id', 'ilike', "%{$term}%")
                    ->orWhereRaw("order_data->>'client_name' ilike ?", ["%{$term}%"]);
            })
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn($d) => [
                'id'       => $d->id,
                'titulo'   => "Pedido #{$d->external_order_id}",
                'subtitulo' => ($d->order_data['client_name'] ?? '—') . ' · ' . ($d->negocio?->name ?? '—'),
                'estado'   => $d->estado,
            ])->all();
    }

    private function buscarNegocios(string $term): array
    {
        return Negocio::where('name', 'ilike', "%{$term}%")
            ->limit(5)
            ->get()
            ->map(fn($n) => [
                'id'       => $n->id,
                'titulo'   => $n->name,
                'subtitulo' => $n->activo ? 'Activo' : 'Inactivo',
            ])->all();
    }

    private function buscarMotorizados(string $term): array
    {
        return Motorizado::where(function ($q) use ($term) {
            $q->where('nombre', 'ilike', "%{$term}%")
                ->orWhere('telefono', 'ilike', "%{$term}%");
        })
            ->limit(5)
            ->get()
            ->map(fn($m) => [
                'id'       => $m->id,
                'titulo'   => $m->nombre,
                'subtitulo' => $m->telefono,
            ])->all();
    }
}
