@extends('layouts.app')

@section('title', $pkb->number)

@section('content')
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
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <a href="{{ route('admin.pkb.index') }}" class="mb-3 inline-block text-sm font-semibold text-[#126b56]">← Daftar PKB</a>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold">{{ $pkb->number }}</h1>
                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
            </div>
            <p class="mt-1 text-sm text-[#687671]">Dibuat {{ $pkb->service_date->format('d M Y, H:i') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @can('viewProgress', $pkb)
                <a class="btn-secondary text-sm" href="{{ route('admin.pkb.progress.show', $pkb) }}">Input Progres</a>
            @endcan
            @if ($pkb->mechanic_id)
                @can('print', $pkb)
                    <a class="btn-primary text-sm" href="{{ route('admin.pkb.print', $pkb) }}">Cetak PKB</a>
                @endcan
            @endif
            @can('cancel', $pkb)
                <form method="POST" action="{{ route('admin.pkb.cancel', $pkb) }}" onsubmit="return requestCancelReason(this, 'Batalkan PKB ini?')">
                    @csrf
                    <input type="hidden" name="cancel_reason">
                    <button class="btn-danger text-sm" type="submit">Batalkan</button>
                </form>
            @endcan
            @can('forceCancel', $pkb)
                <form method="POST" action="{{ route('admin.pkb.force-cancel', $pkb) }}" onsubmit="return requestCancelReason(this, 'Force-cancel akan mengembalikan stok part aktual. Lanjutkan?')">
                    @csrf
                    <input type="hidden" name="cancel_reason">
                    <button class="btn-danger text-sm" type="submit">Force-cancel</button>
                </form>
            @endcan
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-[minmax(0,1.5fr)_minmax(280px,0.8fr)]">
        <div class="space-y-5">
            <section class="border border-[#dce5e1] bg-white p-5 sm:p-6">
                <h2 class="mb-4 font-bold">Customer & kendaraan</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><p class="text-xs font-semibold uppercase text-[#687671]">Customer</p><p class="mt-1 font-semibold">{{ $pkb->customer->name }}</p><p class="text-sm text-[#687671]">{{ $pkb->customer->phone }}</p></div>
                    <div><p class="text-xs font-semibold uppercase text-[#687671]">Kendaraan</p><p class="mt-1 font-semibold">{{ $pkb->vehicle->plate_number }}</p><p class="text-sm text-[#687671]">{{ $pkb->vehicle->brand }} {{ $pkb->vehicle->model }} · {{ number_format($pkb->vehicle->current_km ?? 0, 0, ',', '.') }} km</p></div>
                </div>
                <div class="section-rule mt-5 grid gap-4 pt-5 sm:grid-cols-2">
                    <div><p class="text-xs font-semibold uppercase text-[#687671]">Keluhan</p><p class="mt-1 whitespace-pre-line">{{ $pkb->complaint }}</p></div>
                    <div><p class="text-xs font-semibold uppercase text-[#687671]">Pemeriksaan awal</p><p class="mt-1 whitespace-pre-line">{{ $pkb->inspection_result ?: 'Belum dicatat' }}</p></div>
                </div>
            </section>

            <section class="border border-[#dce5e1] bg-white p-5 sm:p-6">
                <h2 class="mb-4 font-bold">Jasa & sparepart rencana</h2>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[500px] text-left text-sm">
                        <thead class="border-b border-[#dce5e1] text-xs uppercase text-[#687671]"><tr><th class="py-2">Item</th><th class="py-2 text-right">Qty rencana / aktual</th><th class="py-2 text-right">Jumlah</th></tr></thead>
                        <tbody class="divide-y divide-[#e7eeea]">
                            @foreach ($pkb->services as $item)
                                <tr><td class="py-3">{{ $item->name_snapshot }} <span class="text-xs text-[#687671]">· Jasa</span>
                                    @if ($item->documentations->isNotEmpty())<div class="mt-2 flex gap-2">@foreach ($item->documentations as $documentation)<a href="{{ Storage::disk('public')->url($documentation->file_path) }}" target="_blank" rel="noopener"><img class="size-12 rounded-sm object-cover" src="{{ Storage::disk('public')->url($documentation->file_path) }}" alt="Dokumentasi {{ $item->name_snapshot }}"></a>@endforeach</div>@endif
                                </td><td class="py-3 text-right">{{ $item->qty }}</td><td class="py-3 text-right">Rp{{ number_format((float) $item->subtotal, 0, ',', '.') }}</td></tr>
                            @endforeach
                            @foreach ($pkb->spareparts as $item)
                                <tr><td class="py-3">{{ $item->name_snapshot }} <span class="text-xs text-[#687671]">· Part</span>
                                    @if ($item->documentations->isNotEmpty())<div class="mt-2 flex gap-2">@foreach ($item->documentations as $documentation)<a href="{{ Storage::disk('public')->url($documentation->file_path) }}" target="_blank" rel="noopener"><img class="size-12 rounded-sm object-cover" src="{{ Storage::disk('public')->url($documentation->file_path) }}" alt="Dokumentasi {{ $item->name_snapshot }}"></a>@endforeach</div>@endif
                                </td><td class="py-3 text-right">{{ $item->qty_planned }} @if ($item->qty_used !== null)<span class="block text-xs text-[#126b56]">{{ $item->qty_used }} aktual</span>@endif</td><td class="py-3 text-right">Rp{{ number_format((float) $item->subtotal, 0, ',', '.') }}</td></tr>
                            @endforeach
                        </tbody>
                        <tfoot><tr class="font-bold"><td colspan="2" class="pt-4">{{ $pkb->status === 'WAITING_PAYMENT' || $pkb->status === 'PAID' || $pkb->status === 'CLOSED' ? 'Total final' : 'Estimasi' }}</td><td class="pt-4 text-right">Rp{{ number_format((float) (($pkb->final_total > 0) ? $pkb->final_total : $pkb->estimated_total), 0, ',', '.') }}</td></tr></tfoot>
                    </table>
                </div>
            </section>

            <section class="border border-[#dce5e1] bg-white p-5 sm:p-6">
                <h2 class="mb-4 font-bold">Riwayat status</h2>
                @if ($pkb->statusHistories->isEmpty())
                    <p class="text-sm text-[#687671]">Belum ada perubahan status.</p>
                @else
                    <ol class="space-y-3">
                        @foreach ($pkb->statusHistories as $history)
                            <li class="flex gap-3 text-sm"><span class="mt-1 size-2 shrink-0 rounded-full bg-[#126b56]"></span><div><p><span class="font-semibold">{{ str_replace('_', ' ', $history->from_status) }}</span> → <span class="font-semibold">{{ str_replace('_', ' ', $history->to_status) }}</span></p><p class="text-xs text-[#687671]">{{ $history->created_at?->format('d M Y, H:i') }} · {{ $history->changer?->name ?? 'Sistem' }}</p></div></li>
                        @endforeach
                    </ol>
                @endif
            </section>
        </div>

        <aside class="space-y-5">
            <section class="border border-[#dce5e1] bg-white p-5">
                <h2 class="font-bold">Mekanik bertugas</h2>
                <p class="mt-2 text-sm text-[#687671]">{{ $pkb->mechanic?->name ?? 'Belum ditugaskan' }}</p>
                @can('assignMechanic', $pkb)
                    <form method="POST" action="{{ route('admin.pkb.assign-mechanic', $pkb) }}" class="mt-4 space-y-3">
                        @csrf
                        <label class="form-label" for="mechanic_id">{{ $pkb->mechanic_id ? 'Ganti mekanik' : 'Tugaskan mekanik' }}</label>
                        <select class="form-field" id="mechanic_id" name="mechanic_id" required>
                            <option value="">Pilih mekanik</option>
                            @foreach ($mechanics as $mechanic)
                                <option value="{{ $mechanic->id }}" @selected($pkb->mechanic_id === $mechanic->id)>{{ $mechanic->name }}</option>
                            @endforeach
                        </select>
                        <button class="btn-primary w-full text-sm" type="submit">{{ $pkb->mechanic_id ? 'Simpan penugasan ulang' : 'Tugaskan mekanik' }}</button>
                    </form>
                @endcan
            </section>
            <section class="border border-[#dce5e1] bg-white p-5">
                <h2 class="font-bold">Ringkasan</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-[#687671]">Dibuat oleh</dt><dd class="text-right font-semibold">{{ $pkb->creator->name }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-[#687671]">Tanggal servis</dt><dd class="text-right font-semibold">{{ $pkb->service_date->format('d/m/Y H:i') }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-[#687671]">Status</dt><dd class="text-right font-semibold">{{ $statusLabel }}</dd></div>
                </dl>
                @if ($pkb->cancel_reason)
                    <div class="mt-4 border-t border-[#dce5e1] pt-4"><p class="text-xs font-semibold uppercase text-[#687671]">Alasan pembatalan</p><p class="mt-1 text-sm">{{ $pkb->cancel_reason }}</p></div>
                @endif
            </section>
        </aside>
    </div>
    <script>
        function requestCancelReason(form, confirmation) {
            const reason = window.prompt('Masukkan alasan pembatalan:');
            if (!reason?.trim()) return false;
            if (!window.confirm(confirmation)) return false;
            form.querySelector('[name="cancel_reason"]').value = reason.trim();
            return true;
        }
    </script>
@endsection