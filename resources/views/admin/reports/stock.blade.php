@extends('layouts.app')

@section('title', 'Laporan Stok')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <a class="mb-3 inline-block text-sm font-semibold text-[var(--orange-deep)]" href="{{ route('admin.reports.index') }}">← Dashboard</a>
            <p class="mb-1 text-xs font-bold uppercase tracking-[0.12em] text-[var(--orange-deep)]">Laporan · Stok</p>
            <h1 class="text-2xl font-bold">Laporan Stok Sparepart</h1>
        </div>
        <a href="{{ route('admin.reports.stock.export') }}" class="btn-primary whitespace-nowrap">Unduh CSV</a>
    </div>

    <section class="mb-6 border border-[var(--line)] bg-white p-5 sm:p-6">
        <div class="grid gap-4 sm:grid-cols-4">
            <div class="p-4 bg-[var(--orange-light)] rounded-xl">
                <p class="text-xs font-semibold uppercase text-[var(--orange-deep)]">Total Sparepart</p>
                <p class="mt-1 text-2xl font-bold text-[var(--orange-deep)]">{{ $data['total_spareparts'] }}</p>
            </div>
            <div class="p-4 bg-[var(--red)]/10 rounded-xl">
                <p class="text-xs font-semibold uppercase text-[var(--red)]">Stok Menipis</p>
                <p class="mt-1 text-2xl font-bold text-[var(--red)]">{{ $data['low_stock_count'] }}</p>
            </div>
            <div class="p-4 bg-[var(--success)]/10 rounded-xl">
                <p class="text-xs font-semibold uppercase text-[var(--success)]">Stok Aman</p>
                <p class="mt-1 text-2xl font-bold text-[var(--success)]">{{ $data['total_spareparts'] - $data['low_stock_count'] }}</p>
            </div>
        </div>
    </section>

    <section class="overflow-hidden border border-[var(--line)] bg-white">
        @if (empty($data['items']))
            <p class="px-5 py-14 text-center text-sm text-[var(--muted)]">Belum ada sparepart.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <thead class="bg-[var(--paper)] text-xs uppercase text-[var(--muted)]">
                        <tr>
                            <th class="px-5 py-3">Kode</th>
                            <th class="px-5 py-3">Nama</th>
                            <th class="px-5 py-3">Satuan</th>
                            <th class="px-5 py-3 text-right">Stok</th>
                            <th class="px-5 py-3 text-right">Min Stok</th>
                            <th class="px-5 py-3 text-right">Harga Jual</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--line)]">
                        @foreach ($data['items'] as $s)
                            <tr class="hover:bg-[var(--paper)] {{ $s['is_low'] ? 'bg-red-50' : '' }}">
                                <td class="px-5 py-4 font-mono">{{ $s['code'] }}</td>
                                <td class="px-5 py-4 font-semibold">{{ $s['name'] }}</td>
                                <td class="px-5 py-4">{{ $s['unit'] }}</td>
                                <td class="px-5 py-4 text-right font-mono font-semibold {{ $s['is_low'] ? 'text-[var(--red)]' : '' }}">{{ number_format($s['stock_qty'], 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-right font-mono">{{ number_format($s['min_stock'], 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-right">Rp{{ number_format($s['selling_price'], 0, ',', '.') }}</td>
                                <td class="px-5 py-4">
                                    @if ($s['is_low'])
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[var(--red)]/10 px-2.5 py-1 text-xs font-semibold text-[var(--red)]">
                                            <span class="size-1.5 rounded-full bg-[var(--red)]"></span> MENIPIS
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[var(--success)]/10 px-2.5 py-1 text-xs font-semibold text-[var(--success)]">
                                            <span class="size-1.5 rounded-full bg-[var(--success)]"></span> AMAN
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection