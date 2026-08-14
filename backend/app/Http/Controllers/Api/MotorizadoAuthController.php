<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MotorizadoResource;
use App\Models\Motorizado;
use App\Models\NotificacionAdmin;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class MotorizadoAuthController extends Controller
{
    // POST /v1/motorizado/auth/register
    public function register(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'dni'               => 'required|digits:8|unique:motorizados,dni',
                'nombres'           => 'required|string|max:100',
                'apellidos'         => 'required|string|max:100',
                'fecha_nacimiento'  => 'required|date|before:-18 years',
                'telefono'          => 'required|digits:9',
                'email'             => 'required|email|unique:motorizados,email',
                'password'          => 'required|string|min:8',

                'placa'             => 'required|string|max:10|unique:motorizados,placa',
                'marca_vehiculo'    => 'required|string|max:50',
                'modelo_vehiculo'   => 'required|string|max:50',
                'anio_vehiculo'     => 'required|integer|min:1990|max:' . (date('Y') + 1),
                'foto_vehiculo'     => 'required|image|max:5120',
                'soat_numero'       => 'nullable|string|max:30',
            ], [
                'fecha_nacimiento.before' => 'Debes ser mayor de 18 años para registrarte',
            ]);
        } catch (ValidationException $e) {
            // No se guarda en ninguna tabla — solo queda en el log, para
            // poder revisar por consola si hay patrones (DNI/placa
            // repetidos, gente menor de edad intentando registrarse, etc.).
            // Nunca se registra la contraseña.
            Log::warning('Registro de motorizado rechazado', [
                'motivos' => $e->errors(),
                'dni'     => $request->input('dni'),
                'email'   => $request->input('email'),
                'placa'   => $request->input('placa'),
                'ip'      => $request->ip(),
            ]);
            throw $e;
        }

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
            'motorizado' => (new MotorizadoResource($motorizado))->resolve(),
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
            'motorizado' => (new MotorizadoResource($motorizado))->resolve(),
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
            'password'              => 'required|string|min:8|confirmed',
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
        return $this->success((new MotorizadoResource($request->user()))->resolve());
    }
}
