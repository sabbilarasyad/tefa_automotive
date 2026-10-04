@extends('layouts.app')

@section('title', 'Master Sparepart')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="mb-1 text-xs font-bold uppercase tracking-[0.12em] text-[#126b56]">Master Data · Sparepart</p>
            <h1 class="text-2xl font-bold">Daftar Sparepart</h1>
        </div>
        <a class="btn-primary" href="{{ route('admin.spareparts.create') }}">Tambah Sparepart</a>
    </div>

    <!-- Search & Filter -->
    <section class="mb-5 border border-[#dce5e1] bg-white p-5 sm:p-6">
        <form method="GET" action="{{ route('admin.spareparts.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[280px]">
                <label class="form-label" for="search">Cari (Nama / Kode)</label>
                <input class="form-field" id="search" name="search" type="text" value="{{ $search ?? '' }}" placeholder="Kata kunci pencarian" autocomplete="off">
            </div>
            <div>
                <label class="form-label" for="filter">Filter Status</label>
                <select class="form-field" id="filter" name="filter">
                    <option value="" {{ $filter === null ? 'selected' : '' }}>Semua</option>
                    <option value="active" {{ $filter === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ $filter === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <button class="btn-primary" type="submit">Terapkan</button>
            @if ($search || $filter)
                <a class="btn-secondary text-sm" href="{{ route('admin.spareparts.index') }}">Reset</a>
            @endif
        </form>
    </section>

    <section class="overflow-hidden border border-[#dce5e1] bg-white">
        @if ($spareparts->isEmpty())
            <p class="px-5 py-14 text-center text-sm text-[#687671]">Belum ada sparepart.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px] text-left text-sm">
                    <thead class="bg-[#f3f7f5] text-xs uppercase text-[#687671]">
                        <tr>
                            <th class="px-5 py-3">Kode / Nama</th>
                            <th class="px-5 py-3">Satuan</th>
                            <th class="px-5 py-3 text-right">Harga Jual / HPP</th>
                            <th class="px-5 py-3 text-right">Stok / Min</th>
                            <th class="px-5 py-3">Dok. Wajib</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e7eeea]">
                        @foreach ($spareparts as $sparepart)
                            <tr class="hover:bg-[#f9fbfa]">
                                <td class="px-5 py-4">
                                    <p class="font-semibold">{{ $sparepart->code }}</p>
                                    <p class="text-xs text-[#687671]">{{ $sparepart->name }}</p>
                                </td>
                                <td class="px-5 py-4">{{ $sparepart->unit }}</td>
                                <td class="px-5 py-4 text-right">
                                    <p>Rp{{ number_format((float) $sparepart->selling_price, 0, ',', '.') }}</p>
                                    <p class="text-xs text-[#687671]">HPP: Rp{{ number_format((float) $sparepart->cost_price, 0, ',', '.') }}</p>
                                </td>
                                <td class="px-5 py-4 text-right font-mono">
                                    {{ number_format((float) $sparepart->stock_qty, 0, ',', '.') }} / {{ number_format((float) $sparepart->min_stock, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-4 text-center">
                                    @if ($sparepart->documentation_required)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#fff0ed] px-2.5 py-1 text-xs font-semibold text-[#9b3d36]">Ya</span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#e6f2ed] px-2.5 py-1 text-xs font-semibold text-[#175b49]">Tidak</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if ($sparepart->is_active)
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
                                    <a class="btn-secondary text-xs" href="{{ route('admin.spareparts.edit', $sparepart) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.spareparts.destroy', $sparepart) }}" class="inline" onsubmit="return confirm('Hapus sparepart ini?')">
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
            <div class="border-t border-[#dce5e1] px-5 py-4">{{ $spareparts->links() }}</div>
        @endif
    </section>
@endsection