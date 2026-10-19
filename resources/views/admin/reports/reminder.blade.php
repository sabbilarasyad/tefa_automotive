@extends('layouts.app')

@section('title', 'Laporan Reminder')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <a class="mb-3 inline-block text-sm font-semibold text-[var(--orange-deep)]" href="{{ route('admin.reports.index') }}">← Dashboard</a>
            <p class="mb-1 text-xs font-bold uppercase tracking-[0.12em] text-[var(--orange-deep)]">Laporan · Reminder</p>
            <h1 class="text-2xl font-bold">Laporan Reminder Servis</h1>
        </div>
        <a href="{{ route('admin.reports.reminder.export', ['from' => $from?->format('Y-m-d'), 'to' => $to?->format('Y-m-d')]) }}" class="btn-primary whitespace-nowrap">Unduh CSV</a>
    </div>

    <section class="mb-6 border border-[var(--line)] bg-white p-5 sm:p-6">
        <form method="GET" action="{{ route('admin.reports.reminder') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[280px]">
                <label class="form-label" for="from">Dari Tanggal</label>
                <input type="date" id="from" name="from" class="form-field" value="{{ $from?->format('Y-m-d') }}">
            </div>
            <div class="flex-1 min-w-[280px]">
                <label class="form-label" for="to">Sampai Tanggal</label>
                <input type="date" id="to" name="to" class="form-field" value="{{ $to?->format('Y-m-d') }}">
            </div>
            <button class="btn-primary" type="submit">Filter</button>
            <a href="{{ route('admin.reports.reminder') }}" class="btn-secondary text-sm">Reset</a>
        </form>
    </section>

    <section class="mb-6 border border-[var(--line)] bg-white p-5 sm:p-6">
        <div class="grid gap-4 sm:grid-cols-4">
            <div class="p-4 bg-[var(--orange-light)] rounded-xl">
                <p class="text-xs font-semibold uppercase text-[var(--orange-deep)]">Terjadwal</p>
                <p class="mt-1 text-2xl font-bold text-[var(--orange-deep)]">{{ $data['scheduled'] }}</p>
            </div>
            <div class="p-4 bg-[var(--success)]/10 rounded-xl">
                <p class="text-xs font-semibold uppercase text-[var(--success)]">Terkirim</p>
                <p class="mt-1 text-2xl font-bold text-[var(--success)]">{{ $data['sent'] }}</p>
            </div>
            <div class="p-4 bg-[var(--red)]/10 rounded-xl">
                <p class="text-xs font-semibold uppercase text-[var(--red)]">Terlambat</p>
                <p class="mt-1 text-2xl font-bold text-[var(--red)]">{{ $data['overdue'] }}</p>
            </div>
        </div>
    </section>

    <section class="overflow-hidden border border-[var(--line)] bg-white">
        @if (empty($data['items']))
            <p class="px-5 py-14 text-center text-sm text-[var(--muted)]">Belum ada data reminder.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1000px] text-left text-sm">
                    <thead class="bg-[var(--paper)] text-xs uppercase text-[var(--muted)]">
                        <tr>
                            <th class="px-5 py-3">PKB</th>
                            <th class="px-5 py-3">Customer</th>
                            <th class="px-5 py-3">Kendaraan</th>
                            <th class="px-5 py-3">Tgl Jatuh Tempo</th>
                            <th class="px-5 py-3">Tipe</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Tgl Kirim</th>
                            <th class="px-5 py-3">Terlambat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--line)]">
                        @foreach ($data['items'] as $r)
                            <tr class="hover:bg-[var(--paper)] {{ $r['is_overdue'] ? 'bg-red-50' : '' }}">
                                <td class="px-5 py-4">
                                    <a class="font-semibold text-[var(--orange-deep)] hover:underline" href="{{ route('admin.pkb.show', $r['pkb']) }}">{{ $r['pkb'] }}</a>
                                </td>
                                <td class="px-5 py-4">{{ $r['customer'] }}</td>
                                <td class="px-5 py-4">{{ $r['vehicle'] }}</td>
                                <td class="px-5 py-4">{{ $r['reminder_date'] }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-2 py-1 text-xs font-semibold bg-[var(--orange-light)] text-[var(--orange-deep)]">{{ $r['type'] }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    @if ($r['status'] === 'PENDING')
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold bg-[var(--orange-light)] text-[var(--orange-deep)]">{{ $r['status'] }}</span>
                                    @elseif ($r['status'] === 'SENT')
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold bg-[var(--success)]/10 text-[var(--success)]">{{ $r['status'] }}</span>
                                    @else
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold bg-[var(--muted)]/10 text-[var(--muted)]">{{ $r['status'] }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">{{ $r['sent_at'] ?? '-' }}</td>
                                <td class="px-5 py-4 text-center">
                                    @if ($r['is_overdue'])
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[var(--red)]/10 px-2.5 py-1 text-xs font-semibold text-[var(--red)]">
                                            <span class="size-1.5 rounded-full bg-[var(--red)]"></span> Ya
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[var(--success)]/10 px-2.5 py-1 text-xs font-semibold text-[var(--success)]">
                                            <span class="size-1.5 rounded-full bg-[var(--success)]"></span> Tidak
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection