<?php

namespace App\Http\Controllers\Api;

use App\Events\DespachoActualizado;
use App\Events\PedidoDisponible;
use App\Http\Controllers\Controller;
use App\Jobs\NotifyRestaurantWebhook;
use App\Models\ComisionMotorizado;
use App\Models\ConfiguracionCentral;
use App\Models\Despacho;
use App\Models\Motorizado;
use App\Models\NotificacionAdmin;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Carbon;


class DespachoController extends Controller
{
    // ════════════════════════════════════════════════════════
    // ── RESTAURANTE: solicitar despacho ──────────────────────
    // ════════════════════════════════════════════════════════

    // POST /v1/despachos/solicitar  (auth: restaurant.auth)
    public function solicitar(Request $request): JsonResponse
    {
        $restaurant = $request->attributes->get('restaurant');

        $data = $request->validate([
            'order_id'              => 'required|integer',
            'order_data'             => 'required|array',
            'order_data.client_name'  => 'required|string',
            'order_data.client_phone' => 'required|string',
            'order_data.address'      => 'nullable|string',
            'order_data.district'     => 'nullable|string',
            'order_data.reference'    => 'nullable|string',
            'order_data.total'        => 'required|numeric|min:0',
            'order_data.metodo_pago'  => 'nullable|string',
            'order_data.lat'          => 'nullable|numeric',
            'order_data.lng'          => 'nullable|numeric',
            'order_data.items'        => 'nullable|array',
            'order_data.note'         => 'nullable|string',
        ]);

        // Evitar duplicar solicitud activa para el mismo pedido
        $existente = Despacho::where('restaurant_id', $restaurant->id)
            ->where('external_order_id', $data['order_id'])
            ->whereNotIn('estado', ['entregado', 'cancelado'])
            ->first();

        if ($existente) {
            return $this->success(
                $this->formatDespacho($existente),
                'Ya existe un despacho activo para este pedido'
            );
        }

        $comisionDefault = (float) ConfiguracionCentral::get('comision_por_entrega', 0.50);

        $despacho = Despacho::create([
            'restaurant_id'        => $restaurant->id,
            'external_order_id'    => $data['order_id'],
            'estado'               => 'solicitado',
            'order_data'           => $data['order_data'],
            'comision_motorizado'  => $comisionDefault,
            'solicitado_at'        => now(),
        ]);

        broadcast(new PedidoDisponible($despacho));

        NotificacionAdmin::crear(
            'nuevo_despacho',
            'Nuevo pedido solicitado',
            "{$restaurant->name} solicitó un despacho para el pedido #{$data['order_id']}",
            ['despacho_id' => $despacho->id, 'restaurant_id' => $restaurant->id],
        );

        return $this->created($this->formatDespacho($despacho), 'Despacho solicitado');
    }

    // GET /v1/despachos/{order_id}/estado  (auth: restaurant.auth)
    public function estadoPorOrderId(Request $request, int $orderId): JsonResponse
    {
        $restaurant = $request->attributes->get('restaurant');

        $despacho = Despacho::where('restaurant_id', $restaurant->id)
            ->where('external_order_id', $orderId)
            ->latest()
            ->first();

        if (!$despacho) return $this->notFound('Sin despacho para este pedido');

        return $this->success($this->formatDespacho($despacho));
    }

    // POST /v1/despachos/{order_id}/cancelar  (auth: restaurant.auth)
    public function cancelarPorRestaurante(Request $request, int $orderId): JsonResponse
    {
        $restaurant = $request->attributes->get('restaurant');

        $despacho = Despacho::where('restaurant_id', $restaurant->id)
            ->where('external_order_id', $orderId)
            ->whereNotIn('estado', ['entregado', 'cancelado'])
            ->first();

        if (!$despacho) return $this->notFound('Sin despacho activo para este pedido');

        $despacho->update(['estado' => 'cancelado']);

        if ($despacho->motorizado_id) {
            Motorizado::where('id', $despacho->motorizado_id)
                ->update(['estado' => 'disponible']);
        }

        broadcast(new DespachoActualizado($despacho));

        NotificacionAdmin::crear(
            'despacho_cancelado',
            'Restaurante canceló un pedido',
            "{$restaurant->name} canceló el pedido #{$orderId}",
            ['despacho_id' => $despacho->id],
        );

        return $this->success($this->formatDespacho($despacho), 'Despacho cancelado');
    }

    // ════════════════════════════════════════════════════════
    // ── MOTORIZADO: auth ──────────────────────────────────────
    // ════════════════════════════════════════════════════════

    // POST /v1/motorizado/auth/register
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'dni'               => 'required|string|max:15|unique:motorizados,dni',
            'nombres'           => 'required|string|max:100',
            'apellidos'         => 'required|string|max:100',
            'fecha_nacimiento'  => 'required|date|before:-18 years',
            'telefono'          => 'required|string|max:20',
            'email'             => 'required|email|unique:motorizados,email',
            'password'          => 'required|string|min:6',

            'placa'             => 'required|string|max:10|unique:motorizados,placa',
            'marca_vehiculo'    => 'required|string|max:50',
            'modelo_vehiculo'   => 'required|string|max:50',
            'anio_vehiculo'     => 'required|integer|min:1990|max:' . (date('Y') + 1),
            'foto_vehiculo'     => 'required|image|max:5120',
            'soat_numero'       => 'nullable|string|max:30',
        ], [
            'fecha_nacimiento.before' => 'Debes ser mayor de 18 años para registrarte',
        ]);

        $fotoPath = $request->file('foto_vehiculo')->store('vehiculos', 'public');

        $motorizado = Motorizado::create([
            'nombre'            => "{$data['nombres']} {$data['apellidos']}",
            'nombres'           => $data['nombres'],
            'apellidos'         => $data['apellidos'],
            'dni'               => $data['dni'],
            'fecha_nacimiento'  => $data['fecha_nacimiento'],
            'telefono'          => $data['telefono'],
            'email'             => $data['email'],
            'password'          => Hash::make($data['password']),
            'placa'             => strtoupper($data['placa']),
            'marca_vehiculo'    => $data['marca_vehiculo'],
            'modelo_vehiculo'   => $data['modelo_vehiculo'],
            'anio_vehiculo'     => $data['anio_vehiculo'],
            'foto_vehiculo'     => $fotoPath,
            'soat_numero'       => $data['soat_numero'] ?? null,
            'estado'            => 'inactivo',
            'verificado'        => false,
            'activo'            => false,
        ]);

        // ↓ Envuelto en try/catch — si el correo falla (ej. Resend suspendido
        // en revisión), el registro se completa igual. El motorizado puede
        // reenviar el correo de verificación más tarde desde la app.
        try {
            $motorizado->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            NotificacionAdmin::crear(
                'motorizado_pendiente',
                'Nuevo motorizado registrado',
                "{$motorizado->nombre} ({$motorizado->placa}) se registró y espera verificación",
                ['motorizado_id' => $motorizado->id],
            );
        } catch (\Throwable $e) {
            report($e);
        }

        $token = $motorizado->createToken('motorizado')->plainTextToken;

        return $this->created([
            'token'      => $token,
            'motorizado' => $this->formatMotorizado($motorizado),
        ], 'Registro exitoso. Espera la verificación del administrador.');
    }

    // POST /v1/motorizado/auth/login
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $motorizado = Motorizado::where('email', $data['email'])->first();

        if (!$motorizado || !Hash::check($data['password'], $motorizado->password)) {
            return $this->error('Credenciales inválidas', 401);
        }

        $token = $motorizado->createToken('motorizado')->plainTextToken;

        return $this->success([
            'token'      => $token,
            'motorizado' => $this->formatMotorizado($motorizado),
        ]);
    }

    // POST /v1/motorizado/auth/logout
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(null, 'Sesión cerrada');
    }

    // POST /v1/motorizado/auth/forgot-password
    public function forgotPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
        ]);

        Password::broker('motorizados')->sendResetLink(
            $data,
        );

        return $this->success(
            null,
            'Si el correo está registrado, te enviamos un enlace para recuperar tu contraseña.',
        );
    }

    // POST /v1/motorizado/auth/reset-password
    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token'                 => 'required|string',
            'email'                 => 'required|email',
            'password'              => 'required|string|min:6|confirmed',
        ]);

        $status = Password::broker('motorizados')->reset(
            $data,
            function (Motorizado $motorizado, string $password) {
                $motorizado->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            return $this->error(
                $status === Password::INVALID_TOKEN
                    ? 'El enlace no es válido o ya expiró'
                    : 'No se pudo restablecer la contraseña',
                422,
            );
        }

        return $this->success(null, 'Contraseña actualizada correctamente');
    }

    // POST /v1/motorizado/auth/resend-verification  (auth: sanctum)
    public function resendVerification(Request $request): JsonResponse
    {
        $motorizado = $request->user();

        if ($motorizado->hasVerifiedEmail()) {
            return $this->success(null, 'Tu correo ya estaba verificado');
        }

        $motorizado->sendEmailVerificationNotification();

        return $this->success(null, 'Te reenviamos el correo de confirmación');
    }

    // GET /v1/motorizado/auth/verify-email/{id}/{hash}  (firmada, sin auth)
    public function verifyEmail(Request $request, int $id, string $hash): \Illuminate\Http\RedirectResponse
    {
        $frontend = rtrim(config('app.frontend_url'), '/');
        $motorizado = Motorizado::find($id);

        if (!$motorizado || !hash_equals($hash, sha1($motorizado->getEmailForVerification()))) {
            return redirect("{$frontend}/login?verified=0");
        }

        if (!$motorizado->hasVerifiedEmail()) {
            $motorizado->markEmailAsVerified();
            event(new Verified($motorizado));
        }

        return redirect("{$frontend}/login?verified=1");
    }

    // GET /v1/motorizado/me
    public function me(Request $request): JsonResponse
    {
        return $this->success($this->formatMotorizado($request->user()));
    }

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

        return $this->success($this->formatMotorizado($motorizado), 'Estado actualizado');
    }

    // PUT /v1/motorizado/perfil
    public function updatePerfil(Request $request): JsonResponse
    {
        $motorizado = $request->user();

        $data = $request->validate([
            'nombre'           => 'sometimes|string|max:150',
            'telefono'         => 'sometimes|string|max:20',
            'email'            => 'sometimes|email|unique:motorizados,email,' . $motorizado->id,
            'password_actual'  => 'required_with:password|string',
            'password'         => 'sometimes|string|min:6|confirmed',
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

        return $this->success($this->formatMotorizado($motorizado), 'Perfil actualizado correctamente');
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

    // ════════════════════════════════════════════════════════
    // ── MOTORIZADO: despachos ──────────────────────────────
    // ════════════════════════════════════════════════════════

    // GET /v1/motorizado/pedidos
    public function pedidosDisponibles(Request $request): JsonResponse
    {
        $despachos = Despacho::with('restaurant')
            ->where('estado', 'solicitado')
            ->orderBy('solicitado_at')
            ->get()
            ->map(fn($d) => $this->formatDespacho($d));

        return $this->success($despachos);
    }

    // GET /v1/motorizado/despachos/activo
    public function despachoActivo(Request $request): JsonResponse
    {
        $despacho = Despacho::with(['restaurant', 'motorizado'])
            ->where('motorizado_id', $request->user()->id)
            ->whereNotIn('estado', ['entregado', 'cancelado'])
            ->latest()
            ->first();

        return $this->success($despacho ? $this->formatDespacho($despacho) : null);
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

        return DB::transaction(function () use ($id, $motorizado) {
            $despacho = Despacho::with('restaurant')
                ->where('id', $id)
                ->lockForUpdate()
                ->first();

            if (!$despacho) return $this->notFound('Despacho no encontrado');

            if ($despacho->estado !== 'solicitado') {
                return $this->error('Este pedido ya fue tomado por otro motorizado', 409);
            }

            $yaOcupado = Despacho::where('motorizado_id', $motorizado->id)
                ->whereNotIn('estado', ['entregado', 'cancelado'])
                ->exists();

            if ($yaOcupado) {
                return $this->error('Ya tienes un despacho activo', 422);
            }

            $despacho->update([
                'motorizado_id' => $motorizado->id,
                'estado'        => 'aceptado',
                'aceptado_at'   => now(),
            ]);

            $motorizado->update(['estado' => 'ocupado']);

            $despacho->load('motorizado', 'restaurant');
            broadcast(new DespachoActualizado($despacho));
            NotifyRestaurantWebhook::dispatch($despacho);

            return $this->success($this->formatDespacho($despacho), 'Despacho aceptado');
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

        $despacho = Despacho::with('restaurant')
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

        $despacho->load('motorizado', 'restaurant');
        broadcast(new DespachoActualizado($despacho));
        NotifyRestaurantWebhook::dispatch($despacho);

        return $this->success($this->formatDespacho($despacho), 'Estado actualizado');
    }

    // GET /v1/motorizado/historial
    public function historial(Request $request): JsonResponse
    {
        $despachos = Despacho::with('restaurant')
            ->where('motorizado_id', $request->user()->id)
            ->where('estado', 'entregado')
            ->orderByDesc('entregado_at')
            ->limit(50)
            ->get()
            ->map(fn($d) => $this->formatDespacho($d));

        return $this->success($despachos);
    }

    // ════════════════════════════════════════════════════════
    // ── TU PANEL: gestión global ─────────────────────────────
    // ════════════════════════════════════════════════════════

    // GET /admin/despachos
    public function index(Request $request): JsonResponse
    {
        $query = Despacho::with(['restaurant', 'motorizado']);

        if ($request->filled('restaurant_id')) {
            $query->where('restaurant_id', $request->restaurant_id);
        }

        $activos = (clone $query)
            ->whereNotIn('estado', ['entregado', 'cancelado'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($d) => $this->formatDespacho($d));

        $entregadosHoy = (clone $query)
            ->where('estado', 'entregado')
            ->whereDate('entregado_at', today())
            ->orderByDesc('entregado_at')
            ->get()
            ->map(fn($d) => $this->formatDespacho($d));

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

        $perPage = min((int) $request->query('per_page', 10), 50);
        $paginated = $query->paginate($perPage);

        return $this->success([
            'data' => collect($paginated->items())->map(fn($m) => $this->formatMotorizado($m, true)),
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
            $this->formatMotorizado($motorizado, true),
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
            $this->formatMotorizado($motorizado, true),
            $motorizado->activo ? 'Motorizado activado' : 'Motorizado desactivado'
        );
    }

    // POST /admin/despachos/{id}/cancelar
    public function cancelar(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'motivo' => 'required|string|max:255',
        ]);

        $despacho = Despacho::with('restaurant')->findOrFail($id);
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

        NotifyRestaurantWebhook::dispatch($despacho);

        return $this->success($this->formatDespacho($despacho), 'Despacho cancelado');
    }

    // ════════════════════════════════════════════════════════
    // ── Helpers de formato ────────────────────────────────────
    // ════════════════════════════════════════════════════════

    public static function formatDespacho(Despacho $d): array
    {
        $orderData = $d->order_data ?? [];

        return [
            'id'                  => $d->id,
            'restaurant_id'       => $d->restaurant_id,
            'restaurant'          => $d->restaurant?->name,
            'order_id'            => $d->external_order_id,
            'estado'              => $d->estado,
            'motivo_cancelacion'  => $d->motivo_cancelacion,
            'comision_motorizado' => (float) $d->comision_motorizado,
            'monto_cobrado'       => $d->monto_cobrado !== null ? (float) $d->monto_cobrado : null,
            'nota_motorizado'     => $d->nota_motorizado,
            'solicitado_at'       => $d->solicitado_at?->toISOString(),
            'aceptado_at'         => $d->aceptado_at?->toISOString(),
            'recogido_at'         => $d->recogido_at?->toISOString(),
            'entregado_at'        => $d->entregado_at?->toISOString(),
            'order'               => [
                'client_name'  => $orderData['client_name']  ?? null,
                'client_phone' => $orderData['client_phone'] ?? null,
                'address'      => $orderData['address']      ?? null,
                'district'     => $orderData['district']     ?? null,
                'reference'    => $orderData['reference']    ?? null,
                'total'        => (float) ($orderData['total'] ?? 0),
                'metodo_pago'  => $orderData['metodo_pago']   ?? null,
                'lat'          => $orderData['lat']           ?? null,
                'lng'          => $orderData['lng']           ?? null,
                'note'         => $orderData['note']          ?? null,
                'items'        => $orderData['items']         ?? [],
            ],
            'motorizado' => $d->motorizado ? [
                'id'       => $d->motorizado->id,
                'nombre'   => $d->motorizado->nombre,
                'telefono' => $d->motorizado->telefono,
                'foto'     => $d->motorizado->foto,
            ] : null,
        ];
    }

    // GET /admin/despachos/historial — historial completo, paginado y filtrable
    public function historialCentral(Request $request): JsonResponse
    {
        $query = Despacho::with(['restaurant', 'motorizado']);

        if ($request->filled('desde') && $request->filled('hasta')) {
            $desde = Carbon::parse($request->query('desde'))->startOfDay();
            $hasta = Carbon::parse($request->query('hasta'))->endOfDay();
            $query->whereBetween('created_at', [$desde, $hasta]);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->query('estado'));
        }

        if ($request->filled('restaurant_id')) {
            $query->where('restaurant_id', $request->query('restaurant_id'));
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

        $perPage = min((int) $request->query('per_page', 15), 50);
        $paginated = $query->paginate($perPage);

        return $this->success([
            'data' => collect($paginated->items())->map(fn($d) => $this->formatDespacho($d)),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'total'        => $paginated->total(),
                'per_page'     => $paginated->perPage(),
            ],
        ]);
    }

    private function formatMotorizado(Motorizado $m, bool $withStats = false): array
    {
        $data = [
            'id'                => $m->id,
            'nombre'            => $m->nombre,
            'nombres'           => $m->nombres,
            'apellidos'         => $m->apellidos,
            'dni'               => $m->dni,
            'fecha_nacimiento'  => $m->fecha_nacimiento?->toDateString(),
            'telefono'          => $m->telefono,
            'email'             => $m->email,
            'foto'              => $m->foto,
            'estado'            => $m->estado,
            'verificado'        => $m->verificado,
            'activo'            => $m->activo,
            'lat'               => $m->lat,
            'lng'               => $m->lng,
            'ultimo_ping'       => $m->ultimo_ping?->toISOString(),
            'email_verificado'  => $m->hasVerifiedEmail(),
            'placa'             => $m->placa,
            'marca_vehiculo'    => $m->marca_vehiculo,
            'modelo_vehiculo'   => $m->modelo_vehiculo,
            'anio_vehiculo'     => $m->anio_vehiculo,
            'foto_vehiculo'     => $m->foto_vehiculo ? asset('storage/' . $m->foto_vehiculo) : null,
            'soat_numero'       => $m->soat_numero,
        ];

        if ($withStats) {
            $data['stats'] = [
                'total_entregas'  => $m->despachos()->where('estado', 'entregado')->count(),
                'entregas_hoy'    => $m->despachosHoy()->count(),
                'deuda_pendiente' => (float) $m->deudaPendiente(),
            ];
        }

        return $data;
    }
}
