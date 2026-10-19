@extends('layouts.app')

@section('title', 'Reminder Servis')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="mb-1 text-xs font-bold uppercase tracking-[0.12em] text-[var(--orange-deep)]">Bengkel · Reminder</p>
            <h1 class="text-2xl font-bold">Daftar Reminder Jatuh Tempo</h1>
        </div>
        <span class="text-sm text-[var(--muted)]">{{ $reminders->total() }} reminder</span>
    </div>

    <section class="overflow-hidden border border-[var(--line)] bg-white">
        @if ($reminders->isEmpty())
            <p class="px-5 py-14 text-center text-sm text-[var(--muted)]">Tidak ada reminder yang jatuh tempo hari ini atau sebelumnya.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <thead class="bg-[#f3f7f5] text-xs uppercase text-[var(--muted)]">
                        <tr>
                            <th class="px-5 py-3">Tgl. Jatuh Tempo</th>
                            <th class="px-5 py-3">Customer / Kendaraan</th>
                            <th class="px-5 py-3">PKB Terkait</th>
                            <th class="px-5 py-3">Tipe</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e7eeea]">
                        @foreach ($reminders as $reminder)
                            <tr class="hover:bg-[#f9fbfa]">
                                <td class="px-5 py-4">
                                    <p class="font-semibold">{{ $reminder->reminder_date->format('d M Y') }}</p>
                                    @if ($reminder->reminder_date->lt($today))
                                        <span class="inline-block mt-1 rounded-full bg-[var(--orange-light)] px-2 py-0.5 text-[10px] font-semibold text-[var(--red)]">Lewat {{ $reminder->reminder_date->diffInDays($today) }} hari</span>
                                    @else
                                        <span class="inline-block mt-1 rounded-full bg-[var(--success)]/10 px-2 py-0.5 text-[10px] font-semibold text-[var(--success)]">Hari ini</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold">{{ $reminder->customer->name ?? $reminder->vehicle->customer->name }}</p>
                                    <p class="text-xs text-[var(--muted)]">{{ $reminder->vehicle->plate_number }} · {{ $reminder->vehicle->brand }} {{ $reminder->vehicle->model }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <a class="font-semibold text-[var(--orange-deep)] hover:underline" href="{{ route('admin.pkb.show', $reminder->pkb) }}">{{ $reminder->pkb->number }}</a>
                                    @if ($reminder->pkb->invoice)
                                        <p class="mt-1 text-xs text-[var(--muted)]">{{ $reminder->pkb->invoice->number }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-[#f3f4f6] px-2 py-1 text-xs font-semibold text-[#374151]">{{ $reminder->type }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-[var(--orange-light)] px-2.5 py-1 text-xs font-semibold text-[var(--orange)]">{{ $reminder->status }}</span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <form method="POST" action="{{ route('admin.reminder.send', $reminder) }}">
                                        @csrf
                                        <button type="submit" class="btn-primary text-sm" onclick="return confirm('Kirim reminder WhatsApp ke customer ini?')">Kirim</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-[var(--line)] px-5 py-4">{{ $reminders->links() }}</div>
        @endif
    </section>
@endsection
