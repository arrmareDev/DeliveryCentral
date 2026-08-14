<?php

namespace App\Http\Controllers\Api;

use App\Events\DespachoActualizado;
use App\Http\Controllers\Controller;
use App\Http\Resources\DespachoResource;
use App\Http\Resources\MotorizadoResource;
use App\Jobs\NotifyNegocioWebhook;
use App\Models\ComisionMotorizado;
use App\Models\Despacho;
use App\Models\Motorizado;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MotorizadoDespachoController extends Controller
{
    // PATCH /v1/motorizado/estado
    public function updateEstado(Request $request): JsonResponse
    {
        $data = $request->validate([
            'estado' => 'required|in:disponible,inactivo',
        ]);

        $motorizado = $request->user();

        if ($data['estado'] === 'disponible' && !$motorizado->hasVerifiedEmail()) {
            return $this->error('Debes confirmar tu correo antes de recibir pedidos', 403);
        }

        if ($data['estado'] === 'disponible' && !$motorizado->verificado) {
            return $this->error('Tu cuenta aún no ha sido verificada', 403);
        }

        if ($motorizado->estado === 'ocupado') {
            return $this->error('No puedes cambiar de estado mientras tienes un despacho activo', 422);
        }

        $motorizado->update(['estado' => $data['estado']]);

        return $this->success((new MotorizadoResource($motorizado))->resolve(), 'Estado actualizado');
    }

    // PUT /v1/motorizado/perfil
    public function updatePerfil(Request $request): JsonResponse
    {
        $motorizado = $request->user();

        $data = $request->validate([
            'nombre'           => 'sometimes|string|max:150',
            'telefono'         => 'sometimes|digits:9',
            'email'            => 'sometimes|email|unique:motorizados,email,' . $motorizado->id,
            'password_actual'  => 'required_with:password|string',
            'password'         => 'sometimes|string|min:8|confirmed',
        ]);

        if (!empty($data['password'])) {
            if (!Hash::check($data['password_actual'], $motorizado->password)) {
                return $this->error('La contraseña actual es incorrecta', 422);
            }
            $motorizado->password = Hash::make($data['password']);
        }

        $motorizado->fill([
            'nombre'   => $data['nombre']   ?? $motorizado->nombre,
            'telefono' => $data['telefono'] ?? $motorizado->telefono,
            'email'    => $data['email']    ?? $motorizado->email,
        ]);

        $motorizado->save();

        return $this->success((new MotorizadoResource($motorizado))->resolve(), 'Perfil actualizado correctamente');
    }

    // PATCH /v1/motorizado/ubicacion
    public function updateUbicacion(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $request->user()->update([
            'lat'         => $data['lat'],
            'lng'         => $data['lng'],
            'ultimo_ping' => now(),
        ]);

        return $this->success(null, 'Ubicación actualizada');
    }

    // GET /v1/motorizado/pedidos
    public function pedidosDisponibles(Request $request): JsonResponse
    {
        $despachos = Despacho::with('negocio')
            ->where('estado', 'solicitado')
            ->orderBy('solicitado_at')
            ->get()
            ->map(fn($d) => (new DespachoResource($d))->resolve());

        return $this->success($despachos);
    }

    // GET /v1/motorizado/despachos/activo
    // GET /v1/motorizado/despachos/activos
    public function despachosActivos(Request $request): JsonResponse
    {
        $despachos = Despacho::with(['negocio', 'motorizado'])
            ->where('motorizado_id', $request->user()->id)
            ->whereNotIn('estado', ['entregado', 'cancelado'])
            ->orderByDesc('aceptado_at')
            ->get();

        return $this->success($despachos->map(fn($d) => (new DespachoResource($d))->resolve())->all());
    }

    // POST /v1/motorizado/despachos/{id}/aceptar
    public function aceptar(Request $request, int $id): JsonResponse
    {
        $motorizado = $request->user();

        if (!$motorizado->hasVerifiedEmail()) {
            return $this->error('Debes confirmar tu correo antes de recibir pedidos', 403);
        }

        if (!$motorizado->verificado) {
            return $this->error('Tu cuenta no está verificada', 403);
        }

        $maxSimultaneos = (int) config('services.motorizado.max_despachos_simultaneos', 3);

        return DB::transaction(function () use ($id, $motorizado, $maxSimultaneos) {
            // Bloqueamos también la fila del propio motorizado — no solo
            // la del despacho. Sin esto, dos aceptaciones casi simultáneas
            // del mismo motorizado (dos pedidos distintos, un clic tras
            // otro muy rápido) podrían leer el mismo conteo "2 de 3" antes
            // de que ninguna de las dos haya confirmado la suya, y las dos
            // pasarían el límite. Bloqueando al motorizado, la segunda
            // espera a que la primera termine y recién ahí cuenta de nuevo.
            Motorizado::where('id', $motorizado->id)->lockForUpdate()->first();

            $despacho = Despacho::with('negocio')
                ->where('id', $id)
                ->lockForUpdate()
                ->first();

            if (!$despacho) return $this->notFound('Despacho no encontrado');

            if ($despacho->estado !== 'solicitado') {
                return $this->error('Este pedido ya fue tomado por otro motorizado', 409);
            }

            $activosCount = Despacho::where('motorizado_id', $motorizado->id)
                ->whereNotIn('estado', ['entregado', 'cancelado'])
                ->count();

            if ($activosCount >= $maxSimultaneos) {
                return $this->error("Ya tienes {$maxSimultaneos} pedidos activos — el máximo permitido", 422);
            }

            $despacho->update([
                'motorizado_id' => $motorizado->id,
                'estado'        => 'aceptado',
                'aceptado_at'   => now(),
            ]);

            // Solo queda "ocupado" (deja de recibir pedidos nuevos) al
            // llegar al máximo — con 1 o 2 de 3, sigue disponible.
            if ($activosCount + 1 >= $maxSimultaneos) {
                $motorizado->update(['estado' => 'ocupado']);
            }

            $despacho->load('motorizado', 'negocio');
            broadcast(new DespachoActualizado($despacho));
            NotifyNegocioWebhook::dispatch($despacho);

            return $this->success((new DespachoResource($despacho))->resolve(), 'Despacho aceptado');
        });
    }

    // PATCH /v1/motorizado/despachos/{id}/estado
    public function updateEstadoDespacho(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'estado'        => 'required|in:recogido,entregado',
            'nota'          => 'nullable|string|max:500',
            'monto_cobrado' => 'nullable|numeric|min:0',
        ]);

        $motorizado = $request->user();

        $despacho = Despacho::with('negocio')
            ->where('id', $id)
            ->where('motorizado_id', $motorizado->id)
            ->first();

        if (!$despacho) return $this->notFound('Despacho no encontrado');

        $timestamps = [
            'recogido'  => 'recogido_at',
            'entregado' => 'entregado_at',
        ];

        $update = [
            'estado'                      => $data['estado'],
            $timestamps[$data['estado']]  => now(),
        ];

        if (!empty($data['nota'])) {
            $update['nota_motorizado'] = $data['nota'];
        }

        if ($data['estado'] === 'entregado') {
            $orderData       = $despacho->order_data;
            $esContraentrega = in_array($orderData['metodo_pago'] ?? '', [
                'contraentrega_efectivo',
                'contraentrega_yape',
            ]);

            if ($esContraentrega) {
                $update['monto_cobrado'] = $data['monto_cobrado'] ?? ($orderData['total'] ?? 0);
            }
        }

        $despacho->update($update);

        if ($data['estado'] === 'entregado') {
            $motorizado->update(['estado' => 'disponible']);

            ComisionMotorizado::create([
                'despacho_id'   => $despacho->id,
                'motorizado_id' => $motorizado->id,
                'monto'         => $despacho->comision_motorizado,
                'estado'        => 'pendiente',
            ]);
        }

        $despacho->load('motorizado', 'negocio');
        broadcast(new DespachoActualizado($despacho));
        NotifyNegocioWebhook::dispatch($despacho);

        return $this->success((new DespachoResource($despacho))->resolve(), 'Estado actualizado');
    }

    // GET /v1/motorizado/historial
    public function historial(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 12), 50);

        $paginated = Despacho::with('negocio')
            ->where('motorizado_id', $request->user()->id)
            ->where('estado', 'entregado')
            ->orderByDesc('entregado_at')
            ->paginate($perPage);

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
