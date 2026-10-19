@extends('layouts.app')

@section('title', 'Rekap Harian')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <a class="mb-3 inline-block text-sm font-semibold text-[var(--orange-deep)]" href="{{ route('admin.reports.index') }}">← Dashboard</a>
            <p class="mb-1 text-xs font-bold uppercase tracking-[0.12em] text-[var(--orange-deep)]">Laporan · Rekap Harian</p>
            <h1 class="text-2xl font-bold">Rekapitulasi Harian</h1>
        </div>
        <div class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[280px]">
                <label class="form-label" for="date">Tanggal</label>
                <input type="date" id="date" class="form-field" value="{{ $date->format('Y-m-d') }}" onchange="window.location.href='{{ route('admin.reports.daily') }}?date='+this.value">
            </div>
            <a href="{{ route('admin.reports.daily.export', ['date' => $date->format('Y-m-d')]) }}" class="btn-primary whitespace-nowrap">Unduh CSV</a>
            <a href="{{ route('admin.reports.daily', ['date' => now()->format('Y-m-d')]) }}" class="btn-secondary text-sm">Hari Ini</a>
        </div>
    </div>

    <section class="mb-6 border border-[var(--line)] bg-white p-5 sm:p-6">
        <div class="grid gap-4 sm:grid-cols-4">
            <div class="p-4 bg-[var(--orange-light)] rounded-xl">
                <p class="text-xs font-semibold uppercase text-[var(--orange-deep)]">Jumlah PKB</p>
                <p class="mt-1 text-2xl font-bold text-[var(--orange-deep)]">{{ $data['pkb_count'] }}</p>
            </div>
            <div class="p-4 bg-[var(--orange-light)] rounded-xl">
                <p class="text-xs font-semibold uppercase text-[var(--orange-deep)]">Jumlah Transaksi</p>
                <p class="mt-1 text-2xl font-bold text-[var(--orange-deep)]">{{ $data['transaction_count'] }}</p>
            </div>
            <div class="p-4 bg-[var(--success)]/10 rounded-xl">
                <p class="text-xs font-semibold uppercase text-[var(--success)]">Total Pendapatan</p>
                <p class="mt-1 text-2xl font-bold text-[var(--success)]">Rp{{ number_format($data['total_revenue'], 0, ',', '.') }}</p>
            </div>
            <div class="p-4 bg-[var(--orange-light)] rounded-xl">
                <p class="text-xs font-semibold uppercase text-[var(--orange-deep)]">Rata-rata/Transaksi</p>
                <p class="mt-1 text-2xl font-bold text-[var(--orange-deep)]">{{ $data['transaction_count'] > 0 ? 'Rp'.number_format($data['total_revenue']/$data['transaction_count'], 0, ',', '.') : 'Rp0' }}</p>
            </div>
        </div>
    </section>

    <section class="mb-6 border border-[var(--line)] bg-white p-5 sm:p-6">
        <h2 class="mb-4 font-bold">Per Metode Pembayaran</h2>
        <div class="grid gap-4 sm:grid-cols-4">
            @foreach (['CASH','TRANSFER','QRIS','OTHER'] as $method)
                <div class="p-4 bg-white border border-[var(--line)] rounded-xl text-center">
                    <p class="text-xs font-semibold uppercase text-[var(--muted)]">{{ $method }}</p>
                    <p class="mt-2 text-2xl font-bold text-[var(--ink)]">Rp{{ number_format($data['by_method'][$method] ?? 0, 0, ',', '.') }}</p>
                    <p class="mt-1 text-xs text-[var(--muted)]">{{ $data['total_revenue'] > 0 ? round((($data['by_method'][$method] ?? 0) / $data['total_revenue']) * 100) : 0 }}%</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="overflow-hidden border border-[var(--line)] bg-white">
        <h2 class="mb-4 p-5 font-bold border-b border-[var(--line)]">Detail Transaksi</h2>
        @if (empty($data['details']))
            <p class="px-5 py-14 text-center text-sm text-[var(--muted)]">Belum ada transaksi pada tanggal ini.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <thead class="bg-[var(--paper)] text-xs uppercase text-[var(--muted)]">
                        <tr>
                            <th class="px-5 py-3">Waktu</th>
                            <th class="px-5 py-3">Invoice</th>
                            <th class="px-5 py-3">PKB</th>
                            <th class="px-5 py-3">Customer</th>
                            <th class="px-5 py-3">Kendaraan</th>
                            <th class="px-5 py-3">Metode</th>
                            <th class="px-5 py-3 text-right">Jumlah</th>
                            <th class="px-5 py-3">Diterima Oleh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--line)]">
                        @foreach ($data['details'] as $d)
                            <tr class="hover:bg-[var(--paper)]">
                                <td class="px-5 py-4 font-mono">{{ $d['time'] }}</td>
                                <td class="px-5 py-4"><a class="font-semibold text-[var(--orange-deep)] hover:underline" href="{{ route('admin.pkb.show', $d['pkb']) }}">{{ $d['invoice'] }}</a></td>
                                <td class="px-5 py-4"><a class="font-semibold text-[var(--orange-deep)] hover:underline" href="{{ route('admin.pkb.show', $d['pkb']) }}">{{ $d['pkb'] }}</a></td>
                                <td class="px-5 py-4">{{ $d['customer'] }}</td>
                                <td class="px-5 py-4">{{ $d['vehicle'] }}</td>
                                <td class="px-5 py-4"><span class="rounded-full px-2 py-1 text-xs font-semibold bg-[var(--orange-light)] text-[var(--orange-deep)]">{{ $d['method'] }}</span></td>
                                <td class="px-5 py-4 text-right font-semibold">Rp{{ number_format($d['amount'], 0, ',', '.') }}</td>
                                <td class="px-5 py-4">{{ $d['received_by'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection