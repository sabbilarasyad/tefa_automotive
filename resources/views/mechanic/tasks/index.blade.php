@extends('layouts.app')

@section('title', 'Tugas & Riwayat Saya')

@section('content')
    <div class="mb-6">
        <p class="mb-1 text-xs font-bold uppercase tracking-[0.12em] text-[var(--orange-deep)]">Mekanik · View-only</p>
        <h1 class="text-2xl font-bold">Tugas & Riwayat Saya</h1>
        <p class="mt-1 text-sm text-[var(--muted)]">PKB yang ditugaskan kepada {{ auth()->user()->name }}.</p>
    </div>

    <nav class="mb-5 flex border-b border-[var(--line)]" aria-label="Tugas dan riwayat">
        <a class="border-b-2 px-4 py-3 text-sm font-semibold {{ $tab === 'active' ? 'border-[var(--orange-deep)] text-[var(--orange-deep)]' : 'border-transparent text-[var(--muted)]' }}" href="{{ route('mechanic.tasks', ['tab' => 'active']) }}">Aktif</a>
        <a class="border-b-2 px-4 py-3 text-sm font-semibold {{ $tab === 'history' ? 'border-[var(--orange-deep)] text-[var(--orange-deep)]' : 'border-transparent text-[var(--muted)]' }}" href="{{ route('mechanic.tasks', ['tab' => 'history']) }}">Riwayat</a>
    </nav>

    <section class="overflow-hidden border border-[var(--line)] bg-white">
        @if ($pkbs->isEmpty())
            <p class="px-5 py-14 text-center text-sm text-[var(--muted)]">{{ $tab === 'active' ? 'Belum ada tugas aktif.' : 'Belum ada riwayat pekerjaan.' }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px] text-left text-sm">
                    <thead class="bg-[#f3f7f5] text-xs uppercase text-[var(--muted)]"><tr><th class="px-5 py-3">PKB</th><th class="px-5 py-3">Kendaraan</th><th class="px-5 py-3">Keluhan</th><th class="px-5 py-3">Status</th><th class="px-5 py-3"></th></tr></thead>
                    <tbody class="divide-y divide-[#e7eeea]">
                        @foreach ($pkbs as $pkb)
                            <tr>
                                <td class="px-5 py-4"><span class="font-bold">{{ $pkb->number }}</span><p class="mt-1 text-xs text-[var(--muted)]">{{ $pkb->service_date->format('d M Y') }}</p></td>
                                <td class="px-5 py-4"><p class="font-semibold">{{ $pkb->vehicle->plate_number }}</p><p class="text-xs text-[var(--muted)]">{{ $pkb->vehicle->brand }} {{ $pkb->vehicle->model }}</p></td>
                                <td class="max-w-xs px-5 py-4"><p class="line-clamp-2">{{ $pkb->complaint }}</p></td>
                                <td class="px-5 py-4">{{ match ($pkb->status) { 'WAITING_PARTS' => 'Menunggu part', 'WAITING_MECHANIC' => 'Menunggu dikerjakan', 'IN_PROGRESS' => 'Sedang dikerjakan', 'WAITING_PAYMENT' => 'Menunggu pembayaran', 'PAID' => 'Lunas', 'CLOSED' => 'Ditutup', 'CANCELLED' => 'Dibatalkan', default => $pkb->status } }}</td>
                                <td class="px-5 py-4 text-right"><a class="font-semibold text-[var(--orange-deep)]" href="{{ route('admin.pkb.show', $pkb) }}">Lihat detail →</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-[var(--line)] px-5 py-4">{{ $pkbs->links() }}</div>
        @endif
    </section>
@endsection
