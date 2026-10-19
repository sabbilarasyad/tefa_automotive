@extends('layouts.app')

@section('title', 'Riwayat Servis Kendaraan')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="mb-1 text-xs font-bold uppercase tracking-[0.12em] text-[var(--orange-deep)]">Bengkel · Riwayat Servis</p>
            <h1 class="text-2xl font-bold">Riwayat Servis per Kendaraan</h1>
        </div>
    </div>

    <!-- Search Form -->
    <section class="mb-6 border border-[var(--line)] bg-white p-5 sm:p-6">
        <form method="GET" action="{{ route('admin.service-history.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[280px]">
                <label class="form-label" for="search">Cari Kendaraan (Plat / No Rangka / No Mesin)</label>
                <input class="form-field" id="search" name="search" type="text" value="{{ $search ?? '' }}" placeholder="Contoh: B 1234 ABC" autocomplete="off">
            </div>
            <button class="btn-primary" type="submit">Cari</button>
            @if ($search)
                <a class="btn-secondary text-sm" href="{{ route('admin.service-history.index') }}">Reset</a>
            @endif
        </form>
    </section>

    @if ($search && ! $vehicle)
        <div class="mb-5 border-l-4 border-[#f59e0b] bg-[#fffbeb] px-4 py-3 text-sm text-[#854d0e]" role="alert">
            Kendaraan dengan kata kunci "{{ $search }}" tidak ditemukan.
        </div>
    @endif

    @if ($vehicle)
        <section class="mb-6 border border-[var(--line)] bg-white p-5 sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--orange-deep)]">Kendaraan Ditemukan</p>
                    <h2 class="text-xl font-bold">{{ $vehicle->plate_number }} · {{ $vehicle->brand }} {{ $vehicle->model }}</h2>
                    <p class="mt-1 text-sm text-[var(--muted)]">{{ $vehicle->customer->name }} · {{ $vehicle->customer->phone }}</p>
                </div>
                <span class="rounded-full bg-[var(--success)]/10 px-3 py-1.5 text-xs font-semibold text-[var(--success)]">{{ $histories->total() }} Riwayat</span>
            </div>
        </section>

        <section class="overflow-hidden border border-[var(--line)] bg-white">
            @if ($histories->isEmpty())
                <p class="px-5 py-14 text-center text-sm text-[var(--muted)]">Belum ada riwayat servis untuk kendaraan ini.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-left text-sm">
                        <thead class="bg-[#f3f7f5] text-xs uppercase text-[var(--muted)]">
                            <tr>
                                <th class="px-5 py-3">Tanggal</th>
                                <th class="px-5 py-3">PKB / Invoice</th>
                                <th class="px-5 py-3">Keluhan / Hasil Cek</th>
                                <th class="px-5 py-3">KM</th>
                                <th class="px-5 py-3 text-right">Total</th>
                                <th class="px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e7eeea]">
                            @foreach ($histories as $history)
                                <tr class="hover:bg-[#f9fbfa]">
                                    <td class="px-5 py-4">{{ $history->service_date->format('d M Y H:i') }}</td>
                                    <td class="px-5 py-4">
                                        <a class="font-bold text-[var(--orange-deep)] hover:underline" href="{{ route('admin.service-history.show', $history) }}">{{ $history->pkb->number }}</a>
                                        @if ($history->pkb->invoice)
                                            <p class="mt-1 text-xs text-[var(--muted)]">{{ $history->pkb->invoice->number }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 max-w-xs">
                                        <p class="font-semibold truncate">{{ $history->pkb->complaint }}</p>
                                        @if ($history->pkb->inspection_result)
                                            <p class="mt-1 text-xs text-[var(--muted)] truncate">{{ $history->pkb->inspection_result }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 font-mono">{{ $history->km ? number_format($history->km, 0, ',', '.') : '-' }}</td>
                                    <td class="px-5 py-4 text-right font-semibold">Rp{{ number_format((float) $history->total_amount, 0, ',', '.') }}</td>
                                    <td class="px-5 py-4 text-right">
                                        <a class="font-semibold text-[var(--orange-deep)]" href="{{ route('admin.service-history.show', $history) }}">Detail</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-[var(--line)] px-5 py-4">{{ $histories->links() }}</div>
            @endif
        </section>
    @endif
@endsection
