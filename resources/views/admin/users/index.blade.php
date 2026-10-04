@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="mb-1 text-xs font-bold uppercase tracking-[0.12em] text-[#126b56]">Administrasi · Pengguna</p>
            <h1 class="text-2xl font-bold">Daftar Pengguna</h1>
        </div>
        <a class="btn-primary" href="{{ route('admin.users.create') }}">Tambah Pengguna</a>
    </div>

    <!-- Search -->
    <section class="mb-5 border border-[#dce5e1] bg-white p-5 sm:p-6">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[280px]">
                <label class="form-label" for="search">Cari (Nama / Username / Email)</label>
                <input class="form-field" id="search" name="search" type="text" value="{{ $search ?? '' }}" placeholder="Kata kunci pencarian" autocomplete="off">
            </div>
            <button class="btn-primary" type="submit">Cari</button>
            @if ($search)
                <a class="btn-secondary text-sm" href="{{ route('admin.users.index') }}">Reset</a>
            @endif
        </form>
    </section>

    <section class="overflow-hidden border border-[#dce5e1] bg-white">
        @if ($users->isEmpty())
            <p class="px-5 py-14 text-center text-sm text-[#687671]">Belum ada pengguna.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px] text-left text-sm">
                    <thead class="bg-[#f3f7f5] text-xs uppercase text-[#687671]">
                        <tr>
                            <th class="px-5 py-3">Nama / Username</th>
                            <th class="px-5 py-3">Email</th>
                            <th class="px-5 py-3">Peran</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e7eeea]">
                        @foreach ($users as $user)
                            <tr class="hover:bg-[#f9fbfa]">
                                <td class="px-5 py-4">
                                    <p class="font-semibold">{{ $user->name }}</p>
                                    <p class="text-xs text-[#687671]">{{ $user->username }}</p>
                                </td>
                                <td class="px-5 py-4">{{ $user->email }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold
                                        @if ($user->role->name === 'SUPER_ADMIN') bg-[#f3e8ff] text-[#7e22ce]
                                        @elseif ($user->role->name === 'HEAD_TEFA') bg-[#e0e7ff] text-[#3730a3]
                                        @elseif ($user->role->name === 'ADMIN_CS') bg-[#e6f2ed] text-[#175b49]
                                        @elseif ($user->role->name === 'MEKANIK') bg-[#fff2d9] text-[#8a5513]
                                        @else bg-[#fff0ed] text-[#9b3d36] @endif
                                    ">
                                        {{ $user->role->name }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    @if ($user->is_active)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#e6f2ed] px-2.5 py-1 text-xs font-semibold text-[#175b49]">
                                            <span class="size-1.5 rounded-full bg-[#126b56]"></span> Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#fff0ed] px-2.5 py-1 text-xs font-semibold text-[#9b3d36]">
                                            <span class="size-1.5 rounded-full bg-[#b54039]"></span> Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a class="btn-secondary text-xs" href="{{ route('admin.users.edit', $user) }}">Edit</a>
                                        <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" class="inline" onsubmit="return confirm('Reset password pengguna ini? Password sementara akan ditampilkan.')">
                                            @csrf
                                            @method('POST')
                                            <button class="btn-warning text-xs" type="submit">Reset Password</button>
                                        </form>
                                        @if ($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" onsubmit="return confirm('Hapus pengguna ini? Tindakan tidak bisa dibatalkan.')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn-danger text-xs" type="submit">Hapus</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-[#dce5e1] px-5 py-4">{{ $users->links() }}</div>
        @endif
    </section>
@endsection