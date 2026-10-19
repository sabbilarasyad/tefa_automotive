@extends('layouts.app')

@section('title', 'Laporan Dokumentasi')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <a class="mb-3 inline-block text-sm font-semibold text-[var(--orange-deep)]" href="{{ route('admin.reports.index') }}">← Dashboard</a>
            <p class="mb-1 text-xs font-bold uppercase tracking-[0.12em] text-[var(--orange-deep)]">Laporan · Dokumentasi</p>
            <h1 class="text-2xl font-bold">Laporan Kelengkapan Dokumentasi</h1>
        </div>
        <a href="{{ route('admin.reports.documentation.export', ['mechanic_id' => $mechanicId, 'from' => $from?->format('Y-m-d'), 'to' => $to?->format('Y-m-d')]) }}" class="btn-primary whitespace-nowrap">Unduh CSV</a>
    </div>

    <section class="mb-6 border border-[var(--line)] bg-white p-5 sm:p-6">
        <form method="GET" action="{{ route('admin.reports.documentation') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[280px]">
                <label class="form-label" for="mechanic_id">Mekanik</label>
                <select class="form-field" id="mechanic_id" name="mechanic_id">
                    <option value="">Semua Mekanik</option>
                    @foreach ($mechanics as $m)
                        <option value="{{ $m->id }}" {{ $mechanicId == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[280px]">
                <label class="form-label" for="from">Dari Tanggal</label>
                <input type="date" id="from" name="from" class="form-field" value="{{ $from?->format('Y-m-d') }}">
            </div>
            <div class="flex-1 min-w-[280px]">
                <label class="form-label" for="to">Sampai Tanggal</label>
                <input type="date" id="to" name="to" class="form-field" value="{{ $to?->format('Y-m-d') }}">
            </div>
            <button class="btn-primary" type="submit">Filter</button>
            <a href="{{ route('admin.reports.documentation') }}" class="btn-secondary text-sm">Reset</a>
        </form>
    </section>

    <section class="overflow-hidden border border-[var(--line)] bg-white">
        @if (empty($data['items']))
            <p class="px-5 py-14 text-center text-sm text-[var(--muted)]">Belum ada data PKB.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <thead class="bg-[var(--paper)] text-xs uppercase text-[var(--muted)]">
                        <tr>
                            <th class="px-5 py-3">PKB</th>
                            <th class="px-5 py-3">Mekanik</th>
                            <th class="px-5 py-3">Tgl Servis</th>
                            <th class="px-5 py-3 text-center">Total Wajib</th>
                            <th class="px-5 py-3 text-center">Lengkap</th>
                            <th class="px-5 py-3 text-center">Belum Lengkap</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--line)]">
                        @foreach ($data['items'] as $d)
                            <tr class="hover:bg-[var(--paper)]">
                                <td class="px-5 py-4">
                                    <a class="font-semibold text-[var(--orange-deep)] hover:underline" href="{{ route('admin.pkb.show', $d['pkb_number']) }}">{{ $d['pkb_number'] }}</a>
                                </td>
                                <td class="px-5 py-4">{{ $d['mechanic'] }}</td>
                                <td class="px-5 py-4">{{ $d['service_date'] }}</td>
                                <td class="px-5 py-4 text-center font-semibold">{{ $d['total_required'] }}</td>
                                <td class="px-5 py-4 text-center text-[var(--success)]">{{ $d['completed'] }}</td>
                                <td class="px-5 py-4 text-center text-[var(--red)]">{{ $d['pending'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection