<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Responses\ApiResponse;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        $user = User::with('role.permissions')
            ->where('username', $credentials['username'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return ApiResponse::error('Username atau password salah.', status: 401);
        }

        if (! $user->is_active) {
            return ApiResponse::error('Akun tidak aktif. Hubungi administrator.', status: 403);
        }

        $user->forceFill(['last_login_at' => now()])->save();
        $token = $user->createToken($credentials['device_name'] ?? 'TEFA API');
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'LOGIN',
            'entity_type' => User::class,
            'entity_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return ApiResponse::success([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'user' => $this->userData($user),
        ], 'Login berhasil.');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success(null, 'Logout berhasil.');
    }

    public function me(Request $request)
    {
        $user = $request->user()->loadMissing('role.permissions');

        return ApiResponse::success($this->userData($user), 'Data sesi berhasil diambil.');
    }

    private function userData(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'role' => $user->role->name,
            'permissions' => $user->role->permissions->pluck('code')->values(),
        ];
    }
}
