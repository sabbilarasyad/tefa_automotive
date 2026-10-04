@extends('layouts.app')

@section('title', 'Tambah Pengguna')

@section('content')
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <a class="mb-3 inline-block text-sm font-semibold text-[#126b56]" href="{{ route('admin.users.index') }}">← Daftar Pengguna</a>
            <h1 class="text-2xl font-bold">Tambah Pengguna Baru</h1>
        </div>
    </div>

    <section class="border border-[#dce5e1] bg-white p-5 sm:p-6 max-w-2xl">
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="form-label" for="name">Nama Lengkap</label>
                <input class="form-field" id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="150" autocomplete="name">
                @error('name')<p class="mt-1 text-sm text-[#b54039]">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label" for="username">Username</label>
                <input class="form-field" id="username" name="username" type="text" value="{{ old('username') }}" required maxlength="50" autocomplete="username">
                @error('username')<p class="mt-1 text-sm text-[#b54039]">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label" for="email">Email</label>
                <input class="form-field" id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="150" autocomplete="email">
                @error('email')<p class="mt-1 text-sm text-[#b54039]">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label" for="password">Password</label>
                <input class="form-field" id="password" name="password" type="password" required minlength="8" autocomplete="new-password">
                @error('password')<p class="mt-1 text-sm text-[#b54039]">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
                <input class="form-field" id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password">
            </div>

            <div>
                <label class="form-label" for="role_id">Peran</label>
                <select class="form-field" id="role_id" name="role_id" required>
                    <option value="">Pilih peran</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
                </select>
                @error('role_id')<p class="mt-1 text-sm text-[#b54039]">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-2">
                <input class="form-checkbox" id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                <label class="form-label cursor-pointer mb-0" for="is_active">Aktif</label>
            </div>

            <div class="flex flex-wrap justify-end gap-3 pt-4 border-t border-[#dce5e1]">
                <a class="btn-secondary" href="{{ route('admin.users.index') }}">Batal</a>
                <button class="btn-primary" type="submit">Simpan Pengguna</button>
            </div>
        </form>
    </section>
@endsection