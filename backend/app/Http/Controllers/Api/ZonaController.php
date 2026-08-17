<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Motorizado;
use App\Models\Zona;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ZonaController extends Controller
{
    // GET /admin/zonas
    public function index(): JsonResponse
    {
        $zonas = Zona::withCount('motorizados')
            ->orderBy('nombre')
            ->get()
            ->map(fn($z) => $this->format($z));

        return $this->success($zonas);
    }

    // GET /admin/zonas/{id}
    public function show(int $id): JsonResponse
    {
        $zona = Zona::with('motorizados:id,nombre,telefono,estado,verificado,activo')->find($id);
        if (!$zona) return $this->notFound('Zona no encontrada');

        return $this->success($this->format($zona, incluirMotorizados: true));
    }

    // POST /admin/zonas
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $zona = Zona::create([
            'nombre'      => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'activo'      => true,
        ]);

        return $this->created($this->format($zona), 'Zona creada');
    }

    // PUT /admin/zonas/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $zona = Zona::find($id);
        if (!$zona) return $this->notFound('Zona no encontrada');

        $data = $request->validate([
            'nombre'      => 'sometimes|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'activo'      => 'sometimes|boolean',
        ]);

        $zona->update($data);

        return $this->success($this->format($zona->fresh()->loadCount('motorizados')), 'Zona actualizada');
    }

    // DELETE /admin/zonas/{id}
    public function destroy(int $id): JsonResponse
    {
        $zona = Zona::find($id);
        if (!$zona) return $this->notFound('Zona no encontrada');

        $zona->motorizados()->detach();
        $zona->delete();

        return $this->success(null, 'Zona eliminada');
    }

    // POST /admin/zonas/{id}/motorizados — reemplaza la lista completa
    // de motorizados asignados a la zona (sync, no un solo agregar/quitar).
    public function sincronizarMotorizados(Request $request, int $id): JsonResponse
    {
        $zona = Zona::find($id);
        if (!$zona) return $this->notFound('Zona no encontrada');

        $data = $request->validate([
            'motorizado_ids'   => 'present|array',
            'motorizado_ids.*' => 'integer|exists:motorizados,id',
        ]);

        $zona->motorizados()->sync($data['motorizado_ids']);

        $zona->load('motorizados:id,nombre,telefono,estado,verificado,activo');

        return $this->success($this->format($zona, incluirMotorizados: true), 'Motorizados actualizados');
    }

    private function format(Zona $z, bool $incluirMotorizados = false): array
    {
        $data = [
            'id'                => $z->id,
            'nombre'            => $z->nombre,
            'descripcion'       => $z->descripcion,
            'activo'            => $z->activo,
            'total_motorizados' => $z->motorizados_count ?? $z->motorizados()->count(),
            'created_at'        => $z->created_at?->toISOString(),
        ];

        if ($incluirMotorizados) {
            $data['motorizados'] = $z->motorizados->map(fn(Motorizado $m) => [
                'id'         => $m->id,
                'nombre'     => $m->nombre,
                'telefono'   => $m->telefono,
                'estado'     => $m->estado,
                'verificado' => $m->verificado,
                'activo'     => $m->activo,
            ]);
        }

        return $data;
    }
}
