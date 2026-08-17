<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DescuentoMotorizado;
use App\Models\Motorizado;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// Descuentos aplicados por el admin al motorizado — típicamente por
// faltas (no llegar a recoger un pedido aceptado, mal trato al
// cliente, daño a mercadería, etc.). Separado de comisiones a
// propósito, ver la migración para el porqué.
class DescuentoMotorizadoController extends Controller
{
    // GET /admin/motorizados/{id}/descuentos
    public function index(int $id): JsonResponse
    {
        $motorizado = Motorizado::find($id);
        if (!$motorizado) return $this->notFound('Motorizado no encontrado');

        $descuentos = $motorizado->descuentos()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($d) => $this->format($d));

        return $this->success($descuentos);
    }

    // POST /admin/motorizados/{id}/descuentos
    public function store(Request $request, int $id): JsonResponse
    {
        $motorizado = Motorizado::find($id);
        if (!$motorizado) return $this->notFound('Motorizado no encontrado');

        $data = $request->validate([
            'monto'  => 'required|numeric|min:0.01',
            'motivo' => 'required|string|max:255',
        ]);

        $descuento = $motorizado->descuentos()->create($data);

        return $this->created($this->format($descuento), 'Descuento aplicado');
    }

    // DELETE /admin/descuentos/{id} — por si se aplicó por error
    public function destroy(int $id): JsonResponse
    {
        $descuento = DescuentoMotorizado::find($id);
        if (!$descuento) return $this->notFound('Descuento no encontrado');

        $descuento->delete();

        return $this->success(null, 'Descuento eliminado');
    }

    private function format(DescuentoMotorizado $d): array
    {
        return [
            'id'         => $d->id,
            'monto'      => $d->monto,
            'motivo'     => $d->motivo,
            'created_at' => $d->created_at?->toISOString(),
        ];
    }
}
