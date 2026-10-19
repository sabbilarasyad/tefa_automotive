@extends('layouts.app')

@section('title', 'Pembayaran Tanpa Dok. Lengkap')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <a class="mb-3 inline-block text-sm font-semibold text-[var(--orange-deep)]" href="{{ route('admin.reports.index') }}">← Dashboard</a>
            <p class="mb-1 text-xs font-bold uppercase tracking-[0.12em] text-[var(--orange-deep)]">Laporan · Pembayaran Tanpa Dok. Lengkap</p>
            <h1 class="text-2xl font-bold">Pembayaran dengan Konfirmasi Dokumentasi Tidak Lengkap</h1>
        </div>
        <a href="{{ route('admin.reports.incomplete-payments.export', ['from' => $from?->format('Y-m-d'), 'to' => $to?->format('Y-m-d')]) }}" class="btn-primary whitespace-nowrap">Unduh CSV</a>
    </div>

    <section class="mb-6 border border-[var(--line)] bg-white p-5 sm:p-6">
        <form method="GET" action="{{ route('admin.reports.incomplete-payments') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[280px]">
                <label class="form-label" for="from">Dari Tanggal</label>
                <input type="date" id="from" name="from" class="form-field" value="{{ $from?->format('Y-m-d') }}">
            </div>
            <div class="flex-1 min-w-[280px]">
                <label class="form-label" for="to">Sampai Tanggal</label>
                <input type="date" id="to" name="to" class="form-field" value="{{ $to?->format('Y-m-d') }}">
            </div>
            <button class="btn-primary" type="submit">Filter</button>
            <a href="{{ route('admin.reports.incomplete-payments') }}" class="btn-secondary text-sm">Reset</a>
        </form>
    </section>

    <section class="overflow-hidden border border-[var(--line)] bg-white">
        @if (empty($data['confirmed_incomplete_payments']))
            <p class="px-5 py-14 text-center text-sm text-[var(--muted)]">Belum ada data pembayaran tanpa dokumentasi lengkap.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <thead class="bg-[var(--paper)] text-xs uppercase text-[var(--muted)]">
                        <tr>
                            <th class="px-5 py-3">ID Log</th>
                            <th class="px-5 py-3">User</th>
                            <th class="px-5 py-3">Invoice ID</th>
                            <th class="px-5 py-3">PKB</th>
                            <th class="px-5 py-3">Item Kurang</th>
                            <th class="px-5 py-3">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--line)]">
                        @foreach ($data['confirmed_incomplete_payments'] as $p)
                            <tr class="hover:bg-[var(--paper)]">
                                <td class="px-5 py-4 font-mono">{{ $p['id'] }}</td>
                                <td class="px-5 py-4">{{ $p['user'] }}</td>
                                <td class="px-5 py-4 font-mono">{{ $p['invoice_id'] }}</td>
                                <td class="px-5 py-4">{{ $p['pkb_id'] }}</td>
                                <td class="px-5 py-4 max-w-xs truncate">{{ $p['missing_items'] }}</td>
                                <td class="px-5 py-4 font-mono">{{ $p['created_at'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection