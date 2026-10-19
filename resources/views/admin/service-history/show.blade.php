@extends('layouts.app')

@section('title', 'Detail Riwayat Servis · '.$serviceHistory->pkb->number)

@section('content')
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <a class="mb-3 inline-block text-sm font-semibold text-[var(--orange-deep)]" href="{{ route('admin.service-history.index', ['search' => $serviceHistory->vehicle->plate_number]) }}">← Kembali ke Riwayat</a>
            <p class="mb-1 text-xs font-bold uppercase tracking-[0.12em] text-[var(--orange-deep)]">{{ $serviceHistory->service_date->format('d M Y') }}</p>
            <h1 class="text-2xl font-bold">Detail Riwayat Servis</h1>
            <p class="mt-1 text-sm text-[var(--muted)]">PKB: {{ $serviceHistory->pkb->number }} · Invoice: {{ $serviceHistory->pkb->invoice->number ?? '-' }}</p>
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-[minmax(0,1.4fr)_minmax(320px,0.8fr)]">
        <section class="border border-[var(--line)] bg-white p-5 sm:p-6">
            <div class="mb-4 flex items-start justify-between gap-3">
                <div>
                    <h2 class="font-bold">Informasi Kendaraan & Customer</h2>
                    <p class="mt-1 text-sm text-[var(--muted)]">{{ $serviceHistory->vehicle->plate_number }} · {{ $serviceHistory->vehicle->brand }} {{ $serviceHistory->vehicle->model }}</p>
                </div>
            </div>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-3"><dt class="text-[var(--muted)]">Customer</dt><dd class="font-semibold">{{ $serviceHistory->pkb->customer->name }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-[var(--muted)]">Telepon</dt><dd>{{ $serviceHistory->pkb->customer->phone }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-[var(--muted)]">Plat Nomor</dt><dd>{{ $serviceHistory->vehicle->plate_number }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-[var(--muted)]">Kendaraan</dt><dd>{{ $serviceHistory->vehicle->brand }} {{ $serviceHistory->vehicle->model }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-[var(--muted)]">KM Saat Servis</dt><dd class="font-mono">{{ $serviceHistory->km ? number_format($serviceHistory->km, 0, ',', '.') : '-' }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-[var(--muted)]">Tanggal Servis</dt><dd>{{ $serviceHistory->service_date->format('d M Y H:i') }}</dd></div>
                <div class="flex justify-between gap-3 border-t border-[var(--line)] pt-3 text-base font-bold"><dt>Total Biaya</dt><dd>Rp{{ number_format((float) $serviceHistory->total_amount, 0, ',', '.') }}</dd></div>
            </dl>
        </section>

        <aside class="space-y-5">
            <section class="border border-[var(--line)] bg-white p-5 sm:p-6">
                <h2 class="font-bold">Ringkasan PKB</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-[var(--muted)]">Nomor PKB</dt><dd class="font-semibold">{{ $serviceHistory->pkb->number }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-[var(--muted)]">Status</dt><dd><span class="rounded-full bg-[var(--success)]/10 px-2.5 py-1 text-xs font-semibold text-[var(--success)]">{{ $serviceHistory->pkb->status }}</span></dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-[var(--muted)]">Keluhan</dt><dd>{{ $serviceHistory->pkb->complaint }}</dd></div>
                    @if ($serviceHistory->pkb->inspection_result)
                        <div class="flex justify-between gap-3"><dt class="text-[var(--muted)]">Hasil Cek</dt><dd>{{ $serviceHistory->pkb->inspection_result }}</dd></div>
                    @endif
                </dl>
            </section>
        </aside>
    </div>

    <div class="mt-6 grid gap-5 lg:grid-cols-2">
        <section class="border border-[var(--line)] bg-white p-5 sm:p-6">
            <h2 class="font-bold">Jasa yang Dikerjakan</h2>
            @if ($serviceHistory->pkb->services->isEmpty())
                <p class="mt-4 text-sm text-[var(--muted)]">Tidak ada jasa.</p>
            @else
                <div class="mt-4 divide-y divide-[#e7eeea]">
                    @foreach ($serviceHistory->pkb->services as $item)
                        <div class="py-3 flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold">{{ $item->name_snapshot }}</p>
                                <p class="text-xs text-[var(--muted)]">Qty: {{ number_format((float) $item->qty, 0, ',', '.') }} × Rp{{ number_format((float) $item->unit_price, 0, ',', '.') }}</p>
                            </div>
                            <span class="font-semibold">Rp{{ number_format((float) $item->subtotal, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="border border-[var(--line)] bg-white p-5 sm:p-6">
            <h2 class="font-bold">Sparepart yang Digunakan</h2>
            @if ($serviceHistory->pkb->spareparts->isEmpty())
                <p class="mt-4 text-sm text-[var(--muted)]">Tidak ada sparepart.</p>
            @else
                <div class="mt-4 divide-y divide-[#e7eeea]">
                    @foreach ($serviceHistory->pkb->spareparts as $item)
                        <div class="py-3 flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold">{{ $item->name_snapshot }}</p>
                                <p class="text-xs text-[var(--muted)]">Qty: {{ number_format((float) ($item->qty_used ?? $item->qty_planned), 0, ',', '.') }} × Rp{{ number_format((float) $item->unit_price, 0, ',', '.') }}</p>
                            </div>
                            <span class="font-semibold">Rp{{ number_format((float) $item->subtotal, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
