<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('manageUsers', User::class);

        $query = User::query()->with('role')->orderBy('name');

        $search = $request->query('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users', 'search'));
    }

    public function create(): View
    {
        $this->authorize('manageUsers', User::class);

        $roles = Role::query()->orderBy('name')->get();

        return view('admin.users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('manageUsers', User::class);

        $data = $request->validated();
        $data['password'] = bcrypt($data['password']);

        User::create($data);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        $this->authorize('manageUsers', $user);

        $roles = Role::query()->orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('manageUsers', $user);

        $data = $request->validated();

        if ($data['password'] ?? null) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('manageUsers', $user);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menonaktifkan akun sendiri.');
        }

        // Check if target is the last active SUPER_ADMIN
        if ($user->hasRole('SUPER_ADMIN')) {
            $activeSuperAdmins = User::whereHas('role', fn ($q) => $q->where('name', 'SUPER_ADMIN'))
                ->where('is_active', true)
                ->count();
            if ($activeSuperAdmins <= 1) {
                return back()->with('error', 'Tidak bisa menonaktifkan SUPER_ADMIN aktif terakhir.');
            }
        }

        // Revoke all Sanctum tokens
        $user->tokens()->delete();

        // Deactivate instead of delete
        $user->update(['is_active' => false]);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dinonaktifkan.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $this->authorize('resetPassword', $user);

        $temporaryPassword = fake()->password(10, true);

        $user->update(['password' => bcrypt($temporaryPassword)]);

        return back()->with('success', "Password direset. Password sementara: {$temporaryPassword}");
    }
}