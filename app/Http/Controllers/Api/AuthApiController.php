<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

/**
 * Autenticación REST con tokens Bearer (Sanctum).
 *
 * Flujo JWT-equivalente:
 *   1. POST /api/auth/login  → devuelve { token, user }
 *   2. Cliente incluye: Authorization: Bearer <token>
 *   3. POST /api/auth/logout → revoca el token activo
 *   4. GET  /api/auth/me     → datos del usuario autenticado
 *
 * Los tokens se almacenan hasheados en personal_access_tokens.
 * Cada dispositivo/sesión puede tener su propio token independiente.
 */
class AuthApiController extends ApiController
{
    /**
     * Autenticarse y obtener un Bearer token.
     *
     * @bodyParam email    string required Correo electrónico. Example: admin@store.com
     * @bodyParam password string required Contraseña.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Sesión iniciada correctamente",
     *   "data": {
     *     "token": "1|abc123xyz...",
     *     "token_type": "Bearer",
     *     "user": { "id": 1, "name": "Admin", "email": "...", "role": "admin" }
     *   }
     * }
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return $this->error('Credenciales inválidas', 401);
        }

        if ($user->blocked || $user->deleted_at) {
            return $this->forbidden('Usuario bloqueado o eliminado. Contacta al administrador.');
        }

        if (!Auth::attempt($credentials, false)) {
            return $this->error('Credenciales inválidas', 401);
        }

        // Revocar tokens anteriores del mismo usuario (sesión única por usuario)
        $user->tokens()->delete();

        // Crear un nuevo Bearer token con habilidades según el rol
        $abilities = $this->abilitiesForRole($user->role);
        $tokenResult = $user->createToken('api-login', $abilities, now()->addHours(8));

        return $this->success([
            'token'      => $tokenResult->plainTextToken,
            'token_type' => 'Bearer',
            'expires_in' => 8 * 3600,
            'user'       => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ], 'Sesión iniciada correctamente');
    }

    /**
     * Revocar el token activo (cerrar sesión).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Sesión cerrada correctamente');
    }

    /**
     * Datos del usuario autenticado actualmente.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->success([
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'role'       => $user->role,
            'blocked'    => $user->blocked,
            'created_at' => $user->created_at,
        ]);
    }

    // ─── helpers privados ───────────────────────────────────────────────

    private function abilitiesForRole(string $role): array
    {
        return match ($role) {
            'admin'      => ['*'],
            'seller'     => ['products:read', 'sales:write', 'repairs:write', 'orders:write'],
            'technician' => ['products:read', 'repairs:write'],
            default      => ['products:read'],
        };
    }
}
