@extends('layouts.app')

@section('title', 'Rekap Bulanan')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <a class="mb-3 inline-block text-sm font-semibold text-[var(--orange-deep)]" href="{{ route('admin.reports.index') }}">← Dashboard</a>
            <p class="mb-1 text-xs font-bold uppercase tracking-[0.12em] text-[var(--orange-deep)]">Laporan · Rekap Bulanan</p>
            <h1 class="text-2xl font-bold">Rekapitulasi Bulanan</h1>
        </div>
        <div class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[280px]">
                <label class="form-label" for="year">Tahun</label>
                <select id="year" class="form-field" onchange="window.location.href='{{ route('admin.reports.monthly') }}?year='+this.value+'&month='+document.getElementById('month').value">
                    @for ($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="flex-1 min-w-[280px]">
                <label class="form-label" for="month">Bulan</label>
                <select id="month" class="form-field" onchange="window.location.href='{{ route('admin.reports.monthly') }}?year='+document.getElementById('year').value+'&month='+this.value">
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->isoFormat('MMMM') }}</option>
                    @endfor
                </select>
            </div>
            <a href="{{ route('admin.reports.monthly.export', ['year' => $year, 'month' => $month]) }}" class="btn-primary whitespace-nowrap">Unduh CSV</a>
        </div>
    </div>

    <section class="mb-6 border border-[var(--line)] bg-white p-5 sm:p-6">
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="p-4 bg-[var(--orange-light)] rounded-xl">
                <p class="text-xs font-semibold uppercase text-[var(--orange-deep)]">Total Servis</p>
                <p class="mt-1 text-2xl font-bold text-[var(--orange-deep)]">{{ $data['total_services'] }}</p>
            </div>
            <div class="p-4 bg-[var(--success)]/10 rounded-xl">
                <p class="text-xs font-semibold uppercase text-[var(--success)]">Total Pendapatan</p>
                <p class="mt-1 text-2xl font-bold text-[var(--success)]">Rp{{ number_format($data['total_revenue'], 0, ',', '.') }}</p>
            </div>
            <div class="p-4 bg-[var(--orange-light)] rounded-xl">
                <p class="text-xs font-semibold uppercase text-[var(--orange-deep)]">Rata-rata/Servis</p>
                <p class="mt-1 text-2xl font-bold text-[var(--orange-deep)]">{{ $data['total_services'] > 0 ? 'Rp'.number_format($data['total_revenue']/$data['total_services'], 0, ',', '.') : 'Rp0' }}</p>
            </div>
        </div>
    </section>

    <section class="mb-6 border border-[var(--line)] bg-white p-5 sm:p-6">
        <h2 class="mb-4 font-bold">10 Sparepart Terbanyak Terpakai</h2>
        @if (empty($data['top_spareparts']))
            <p class="text-center text-[var(--muted)] py-8">Belum ada data sparepart terpakai pada bulan ini.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px] text-left text-sm">
                    <thead class="bg-[var(--paper)] text-xs uppercase text-[var(--muted)]">
                        <tr>
                            <th class="px-5 py-3">No</th>
                            <th class="px-5 py-3">Kode</th>
                            <th class="px-5 py-3">Nama Sparepart</th>
                            <th class="px-5 py-3">Satuan</th>
                            <th class="px-5 py-3 text-right">Total Qty</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--line)]">
                        @foreach ($data['top_spareparts'] as $index => $s)
                            <tr class="hover:bg-[var(--paper)]">
                                <td class="px-5 py-4">{{ $loop->iteration }}</td>
                                <td class="px-5 py-4 font-mono">{{ $s['code'] }}</td>
                                <td class="px-5 py-4 font-semibold">{{ $s['name'] }}</td>
                                <td class="px-5 py-4">{{ $s['unit'] }}</td>
                                <td class="px-5 py-4 text-right font-semibold">{{ number_format($s['total_qty'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection