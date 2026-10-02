@extends('layouts.app')

@section('title', 'Daftar PKB')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="mb-1 text-xs font-bold uppercase tracking-[0.12em] text-[#126b56]">Workshop · PKB</p>
            <h1 class="text-2xl font-bold">Daftar PKB</h1>
        </div>
        @can('create', App\Models\Pkb::class)
            <a href="{{ route('admin.pkb.create') }}" class="btn-primary inline-flex items-center gap-2 text-sm"><span aria-hidden="true">+</span> Servis Baru</a>
        @endcan
    </div>

    <div class="overflow-hidden border border-[#dce5e1] bg-white">
        <div class="flex items-center justify-between border-b border-[#dce5e1] px-5 py-4">
            <h2 class="font-bold">Penerimaan servis</h2>
            <span class="text-sm text-[#687671]">{{ $pkbs->total() }} PKB</span>
        </div>
        @if ($pkbs->isEmpty())
            <div class="px-5 py-14 text-center">
                <p class="font-semibold">Belum ada PKB untuk ditampilkan.</p>
                @can('create', App\Models\Pkb::class)
                    <a href="{{ route('admin.pkb.create') }}" class="mt-4 inline-block text-sm font-semibold text-[#126b56]">Buat servis pertama</a>
                @endcan
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="bg-[#f3f7f5] text-xs uppercase text-[#687671]">
                        <tr><th class="px-5 py-3 font-semibold">Nomor / tanggal</th><th class="px-5 py-3 font-semibold">Customer & kendaraan</th><th class="px-5 py-3 font-semibold">Mekanik</th><th class="px-5 py-3 font-semibold">Status</th><th class="px-5 py-3 text-right font-semibold">Estimasi</th></tr>
                    </thead>
                    <tbody class="divide-y divide-[#e7eeea]">
                        @foreach ($pkbs as $pkb)
                            @php
                                $statusLabels = [
                                    'WAITING_PARTS' => ['Menunggu part', 'bg-[#fff2d9] text-[#8a5513]'],
                                    'WAITING_MECHANIC' => ['Menunggu dikerjakan', 'bg-[#e6f2ed] text-[#175b49]'],
                                    'IN_PROGRESS' => ['Sedang dikerjakan', 'bg-[#e7f0f6] text-[#315e76]'],
                                    'WAITING_PAYMENT' => ['Menunggu pembayaran', 'bg-[#fff2d9] text-[#8a5513]'],
                                    'PAID' => ['Lunas', 'bg-[#e6f2ed] text-[#175b49]'],
                                    'CLOSED' => ['Ditutup', 'bg-[#edf0ee] text-[#53615b]'],
                                    'CANCELLED' => ['Dibatalkan', 'bg-[#fff0ed] text-[#9b3d36]'],
                                ];
                                [$statusLabel, $statusClass] = $statusLabels[$pkb->status];
                            @endphp
                            <tr class="hover:bg-[#f9fbfa]">
                                <td class="px-5 py-4"><a href="{{ route('admin.pkb.show', $pkb) }}" class="font-bold text-[#126b56] hover:underline">{{ $pkb->number }}</a><p class="mt-1 text-xs text-[#687671]">{{ $pkb->service_date->format('d M Y, H:i') }}</p></td>
                                <td class="px-5 py-4"><p class="font-semibold">{{ $pkb->customer->name }}</p><p class="mt-1 text-xs text-[#687671]">{{ $pkb->vehicle->plate_number }} · {{ $pkb->vehicle->brand }} {{ $pkb->vehicle->model }}</p></td>
                                <td class="px-5 py-4">{{ $pkb->mechanic?->name ?? 'Belum ditugaskan' }}</td>
                                <td class="px-5 py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span></td>
                                <td class="px-5 py-4 text-right font-semibold">Rp{{ number_format((float) $pkb->estimated_total, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-[#dce5e1] px-5 py-4">{{ $pkbs->links() }}</div>
        @endif
    </div>
@endsection