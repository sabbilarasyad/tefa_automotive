@extends('layouts.app')

@section('title', 'Riwayat Stok · '.$sparepart->name)

@section('content')
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <a class="mb-3 inline-block text-sm font-semibold text-[var(--orange-deep)]" href="{{ route('admin.stock.index') }}">← Daftar Stok</a>
            <p class="mb-1 text-xs font-bold uppercase tracking-[0.12em] text-[var(--orange-deep)]">{{ $sparepart->code }}</p>
            <h1 class="text-2xl font-bold">Riwayat Pergerakan Stok</h1>
            <p class="mt-1 text-sm text-[var(--muted)]">{{ $sparepart->name }} · Satuan: {{ $sparepart->unit }} · Stok Saat Ini: {{ number_format((float) $sparepart->stock_qty, 0, ',', '.') }}</p>
        </div>
    </div>

    <section class="overflow-hidden border border-[var(--line)] bg-white">
        @if ($movements->isEmpty())
            <p class="px-5 py-14 text-center text-sm text-[var(--muted)]">Belum ada pergerakan stok untuk sparepart ini.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px] text-left text-sm">
                    <thead class="bg-[#f3f7f5] text-xs uppercase text-[var(--muted)]">
                        <tr>
                            <th class="px-5 py-3">Tanggal</th>
                            <th class="px-5 py-3">Tipe</th>
                            <th class="px-5 py-3 text-right">Masuk</th>
                            <th class="px-5 py-3 text-right">Keluar</th>
                            <th class="px-5 py-3 text-right">Stok Sebelum</th>
                            <th class="px-5 py-3 text-right">Stok Sesudah</th>
                            <th class="px-5 py-3">Alasan</th>
                            <th class="px-5 py-3">Oleh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e7eeea]">
                        @foreach ($movements as $movement)
                            <tr class="hover:bg-[#f9fbfa]">
                                <td class="px-5 py-4">{{ $movement->created_at->format('d M Y H:i') }}</td>
                                <td class="px-5 py-4">
                                    @php
                                        $typeLabels = [
                                            'OUT' => ['Keluar', 'bg-[var(--orange-light)] text-[var(--red)]'],
                                            'MANUAL_IN' => ['Masuk Manual', 'bg-[var(--success)]/10 text-[var(--success)]'],
                                            'ADJUSTMENT' => ['Penyesuaian', 'bg-[#e0e7ff] text-[#3730a3]'],
                                            'RETURN' => ['Retur', 'bg-[var(--orange-light)] text-[var(--orange)]'],
                                        ];
                                        [$label, $badgeClass] = $typeLabels[$movement->type] ?? [$movement->type, 'bg-gray-100 text-gray-600'];
                                    @endphp
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $badgeClass }}">{{ $label }}</span>
                                </td>
                                <td class="px-5 py-4 text-right font-mono">{{ $movement->qty_in > 0 ? number_format((float) $movement->qty_in, 0, ',', '.') : '-' }}</td>
                                <td class="px-5 py-4 text-right font-mono">{{ $movement->qty_out > 0 ? number_format((float) $movement->qty_out, 0, ',', '.') : '-' }}</td>
                                <td class="px-5 py-4 text-right font-mono">{{ number_format((float) $movement->stock_before, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-right font-mono">{{ number_format((float) $movement->stock_after, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-sm text-[#34433e]">{{ $movement->reason }}</td>
                                <td class="px-5 py-4">{{ $movement->creator->name ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
