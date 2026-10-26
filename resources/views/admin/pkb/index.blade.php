@extends('layouts.app')

@section('title', 'Daftar PKB')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="mb-1 text-xs font-bold uppercase tracking-[0.12em] text-[var(--orange-deep)]">Workshop · PKB</p>
            <h1 class="text-2xl font-bold">Daftar PKB</h1>
        </div>
        @can('create', App\Models\Pkb::class)
            <a href="{{ route('admin.pkb.create') }}" class="btn-primary inline-flex items-center gap-2 text-sm"><span aria-hidden="true">+</span> Servis Baru</a>
        @endcan
    </div>

    <div class="overflow-hidden border border-[var(--line)] bg-white">
        <div class="flex items-center justify-between border-b border-[var(--line)] px-5 py-4">
            <h2 class="font-bold">Penerimaan servis</h2>
            <span class="text-sm text-[var(--muted)]">{{ $pkbs->total() }} PKB</span>
        </div>
        @if ($pkbs->isEmpty())
            <div class="px-5 py-14 text-center">
                <p class="font-semibold">Belum ada PKB untuk ditampilkan.</p>
                @can('create', App\Models\Pkb::class)
                    <a href="{{ route('admin.pkb.create') }}" class="mt-4 inline-block text-sm font-semibold text-[var(--orange-deep)]">Buat servis pertama</a>
                @endcan
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="bg-[#f3f7f5] text-xs uppercase text-[var(--muted)]">
                        <tr><th class="px-5 py-3 font-semibold">Nomor / tanggal</th><th class="px-5 py-3 font-semibold">Customer & kendaraan</th><th class="px-5 py-3 font-semibold">Mekanik</th><th class="px-5 py-3 font-semibold">Status</th><th class="px-5 py-3 text-right font-semibold">Estimasi</th></tr>
                    </thead>
                    <tbody class="divide-y divide-[#e7eeea]">
                        @foreach ($pkbs as $pkb)
                            @php
                                $statusLabels = [
                                    'WAITING_PARTS' => ['Menunggu part', 'bg-[var(--orange-light)] text-[var(--orange)]'],
                                    'WAITING_MECHANIC' => ['Menunggu dikerjakan', 'bg-[var(--success)]/10 text-[var(--success)]'],
                                    'IN_PROGRESS' => ['Sedang dikerjakan', 'bg-[#e7f0f6] text-[#315e76]'],
                                    'WAITING_PAYMENT' => ['Menunggu pembayaran', 'bg-[var(--orange-light)] text-[var(--orange)]'],
                                    'PAID' => ['Lunas', 'bg-[var(--success)]/10 text-[var(--success)]'],
                                    'CLOSED' => ['Ditutup', 'bg-[#edf0ee] text-[#53615b]'],
                                    'CANCELLED' => ['Dibatalkan', 'bg-[var(--orange-light)] text-[var(--red)]'],
                                ];
                                [$statusLabel, $statusClass] = $statusLabels[$pkb->status];
                            @endphp
                            <tr class="hover:bg-[#f9fbfa]">
                                <td class="px-5 py-4"><a href="{{ route('admin.pkb.show', $pkb) }}" class="font-bold text-[var(--orange-deep)] hover:underline">{{ $pkb->number }}</a><p class="mt-1 text-xs text-[var(--muted)]">{{ $pkb->service_date->format('d M Y, H:i') }}</p></td>
                                <td class="px-5 py-4"><p class="font-semibold">{{ $pkb->customer->name }}</p><p class="mt-1 text-xs text-[var(--muted)]">{{ $pkb->vehicle->plate_number }} · {{ $pkb->vehicle->brand }} {{ $pkb->vehicle->model }}</p></td>
                                <td class="px-5 py-4">{{ $pkb->mechanic_display_name ?? 'Belum ditugaskan' }}</td>
                                <td class="px-5 py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span></td>
                                <td class="px-5 py-4 text-right font-semibold">Rp{{ number_format((float) $pkb->estimated_total, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-[var(--line)] px-5 py-4">{{ $pkbs->links() }}</div>
        @endif
    </div>
@endsection
