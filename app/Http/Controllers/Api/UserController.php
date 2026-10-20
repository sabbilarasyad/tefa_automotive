<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreUserRequest;
use App\Http\Requests\Api\UpdateUserRequest;
use App\Http\Requests\Api\ResetPasswordRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('manageUsers', User::class);
        
        $query = User::query()->with('role')->orderBy('name');

        $search = request()->get('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('manageUsers', User::class);
        
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Pengguna berhasil ditambahkan.',
            'data' => $user->load('role'),
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('manageUsers', $user);

        return response()->json([
            'success' => true,
            'data' => $user->load('role'),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->authorize('manageUsers', $user);
        
        $data = $request->validated();

        if ($data['password'] ?? null) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Pengguna berhasil diperbarui.',
            'data' => $user->load('role'),
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request, User $user): JsonResponse
    {
        $this->authorize('resetPassword', $user);
        
        $temporaryPassword = fake()->password(10, true);

        $user->update(['password' => Hash::make($temporaryPassword)]);

        return response()->json([
            'success' => true,
            'message' => "Password direset. Password sementara: {$temporaryPassword}",
            'temporary_password' => $temporaryPassword,
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('manageUsers', $user);
        
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa menonaktifkan akun sendiri.',
            ], 422);
        }

        // Check if target is the last active SUPER_ADMIN
        if ($user->hasRole('SUPER_ADMIN')) {
            $activeSuperAdmins = User::whereHas('role', fn ($q) => $q->where('name', 'SUPER_ADMIN'))
                ->where('is_active', true)
                ->count();
            if ($activeSuperAdmins <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak bisa menonaktifkan SUPER_ADMIN aktif terakhir.',
                ], 422);
            }
        }

        // Revoke all Sanctum tokens
        $user->tokens()->delete();

        // Deactivate instead of delete
        $user->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Pengguna berhasil dinonaktifkan.',
        ]);
    }
}