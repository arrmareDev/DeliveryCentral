<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return $this->error('Credenciales inválidas', 401);
        }

        $token = $user->createToken('superadmin')->plainTextToken;

        return $this->success([
            'token' => $token,
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(null, 'Sesión cerrada');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        return $this->success([
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
        ]);
    }
}
