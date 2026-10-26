@extends('layouts.app')

@section('title', 'Progres Pekerjaan')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="mb-1 text-xs font-bold uppercase tracking-[0.12em] text-[var(--orange-deep)]">Workshop · Operasional</p>
            <h1 class="text-2xl font-bold">Progres Pekerjaan</h1>
            <p class="mt-1 text-sm text-[var(--muted)]">Mulai pekerjaan, catat pemakaian aktual, dan validasi penyelesaian.</p>
        </div>
        <a class="btn-secondary text-sm" href="{{ route('admin.pkb.index') }}">Daftar PKB</a>
    </div>

    <section class="overflow-hidden border border-[var(--line)] bg-white">
        <div class="flex items-center justify-between border-b border-[var(--line)] px-5 py-4">
            <h2 class="font-bold">Siap dikerjakan & sedang berjalan</h2>
            <span class="text-sm text-[var(--muted)]">{{ $pkbs->total() }} PKB</span>
        </div>
        @if ($pkbs->isEmpty())
            <p class="px-5 py-12 text-center text-sm text-[var(--muted)]">Tidak ada pekerjaan yang menunggu atau sedang berjalan.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead class="bg-[#f3f7f5] text-xs uppercase text-[var(--muted)]"><tr><th class="px-5 py-3">PKB</th><th class="px-5 py-3">Customer / kendaraan</th><th class="px-5 py-3">Mekanik</th><th class="px-5 py-3">Status</th><th class="px-5 py-3"></th></tr></thead>
                    <tbody class="divide-y divide-[#e7eeea]">
                        @foreach ($pkbs as $pkb)
                            @php($statusLabel = $pkb->status === 'IN_PROGRESS' ? 'Sedang dikerjakan' : 'Menunggu dikerjakan')
                            <tr class="hover:bg-[#f9fbfa]">
                                <td class="px-5 py-4"><a class="font-bold text-[var(--orange-deep)] hover:underline" href="{{ route('admin.pkb.progress.show', $pkb) }}">{{ $pkb->number }}</a><p class="mt-1 text-xs text-[var(--muted)]">{{ $pkb->service_date->format('d M Y, H:i') }}</p></td>
                                <td class="px-5 py-4"><p class="font-semibold">{{ $pkb->customer->name }}</p><p class="text-xs text-[var(--muted)]">{{ $pkb->vehicle->plate_number }} · {{ $pkb->vehicle->brand }} {{ $pkb->vehicle->model }}</p></td>
                                <td class="px-5 py-4">{{ $pkb->mechanic_display_name ?? 'Belum ditugaskan' }}</td>
                                <td class="px-5 py-4"><span class="inline-flex rounded-full bg-[#e7f0f6] px-2.5 py-1 text-xs font-semibold text-[#315e76]">{{ $statusLabel }}</span></td>
                                <td class="px-5 py-4 text-right"><a class="font-semibold text-[var(--orange-deep)]" href="{{ route('admin.pkb.progress.show', $pkb) }}">Buka progres →</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-[var(--line)] px-5 py-4">{{ $pkbs->links() }}</div>
        @endif
    </section>
@endsection
