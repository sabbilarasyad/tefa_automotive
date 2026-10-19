@extends('layouts.app')

@section('title', 'Master Jasa')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="mb-1 text-xs font-bold uppercase tracking-[0.12em] text-[var(--orange-deep)]">Master Data · Jasa</p>
            <h1 class="text-2xl font-bold">Daftar Jasa</h1>
        </div>
        <a class="btn-primary" href="{{ route('admin.services.create') }}">Tambah Jasa</a>
    </div>

    <!-- Search -->
    <section class="mb-5 border border-[var(--line)] bg-white p-5 sm:p-6">
        <form method="GET" action="{{ route('admin.services.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[280px]">
                <label class="form-label" for="search">Cari (Nama / Kode)</label>
                <input class="form-field" id="search" name="search" type="text" value="{{ $search ?? '' }}" placeholder="Kata kunci pencarian" autocomplete="off">
            </div>
            <button class="btn-primary" type="submit">Cari</button>
            @if ($search)
                <a class="btn-secondary text-sm" href="{{ route('admin.services.index') }}">Reset</a>
            @endif
        </form>
    </section>

    <section class="overflow-hidden border border-[var(--line)] bg-white">
        @if ($services->isEmpty())
            <p class="px-5 py-14 text-center text-sm text-[var(--muted)]">Belum ada jasa.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px] text-left text-sm">
                    <thead class="bg-[#f3f7f5] text-xs uppercase text-[var(--muted)]">
                        <tr>
                            <th class="px-5 py-3">Kode / Nama</th>
                            <th class="px-5 py-3">Harga</th>
                            <th class="px-5 py-3">Dokumentasi Wajib</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e7eeea]">
                        @foreach ($services as $service)
                            <tr class="hover:bg-[#f9fbfa]">
                                <td class="px-5 py-4">
                                    <p class="font-semibold">{{ $service->code }}</p>
                                    <p class="text-xs text-[var(--muted)]">{{ $service->name }}</p>
                                </td>
                                <td class="px-5 py-4 text-right">Rp{{ number_format((float) $service->price, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-center">
                                    @if ($service->documentation_required)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[var(--orange-light)] px-2.5 py-1 text-xs font-semibold text-[var(--red)]">Ya</span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[var(--success)]/10 px-2.5 py-1 text-xs font-semibold text-[var(--success)]">Tidak</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if ($service->is_active)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[var(--success)]/10 px-2.5 py-1 text-xs font-semibold text-[var(--success)]">
                                            <span class="size-1.5 rounded-full bg-[var(--orange)]"></span> Aktif
                                        </span>
                                    @else
<span class="inline-flex items-center gap-1.5 rounded-full bg-[var(--orange-light)] px-2.5 py-1 text-xs font-semibold text-[var(--red)]">
                                            <span class="size-1.5 rounded-full bg-[var(--red)]"></span> Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a class="btn-secondary text-xs" href="{{ route('admin.services.edit', $service) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.services.destroy', $service) }}" class="inline" onsubmit="return confirm('Hapus jasa ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-danger text-xs" type="submit">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-[var(--line)] px-5 py-4">{{ $services->links() }}</div>
        @endif
    </section>
@endsection
