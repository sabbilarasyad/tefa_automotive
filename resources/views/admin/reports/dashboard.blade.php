@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="mb-1 text-xs font-bold uppercase tracking-[0.12em] text-[var(--orange-deep)]">Laporan · Dashboard</p>
            <h1 class="text-2xl font-bold">Ringkasan Hari Ini</h1>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5 mb-6">
        <article class="border border-[var(--line)] bg-white p-5 rounded-xl">
            <p class="text-xs font-semibold uppercase text-[var(--muted)]">Total PKB</p>
            <p class="mt-2 text-3xl font-bold text-[var(--ink)]">{{ array_sum($data['pkb_per_status']) }}</p>
            <div class="mt-2 flex flex-wrap gap-2 text-xs">
                @foreach ($data['pkb_per_status'] as $status => $count)
                    <span class="px-2 py-1 rounded-full bg-[var(--line)]">{{ $status }}: {{ $count }}</span>
                @endforeach
            </div>
        </article>

        <article class="border border-[var(--line)] bg-white p-5 rounded-xl">
            <p class="text-xs font-semibold uppercase text-[var(--muted)]">Menunggu Part</p>
            <p class="mt-2 text-3xl font-bold text-[var(--orange)]">{{ $data['waiting_parts'] }}</p>
        </article>

        <article class="border border-[var(--line)] bg-white p-5 rounded-xl">
            <p class="text-xs font-semibold uppercase text-[var(--muted)]">Stok Menipis</p>
            <p class="mt-2 text-3xl font-bold text-[var(--red)]">{{ $data['low_stock'] }}</p>
        </article>

        <article class="border border-[var(--line)] bg-white p-5 rounded-xl">
            <p class="text-xs font-semibold uppercase text-[var(--muted)]">Reminder Jatuh Tempo</p>
            <p class="mt-2 text-3xl font-bold text-[var(--orange-deep)]">{{ $data['due_reminders'] }}</p>
        </article>

        <article class="border border-[var(--line)] bg-white p-5 rounded-xl">
            <p class="text-xs font-semibold uppercase text-[var(--muted)]">Pendapatan Hari Ini</p>
            <p class="mt-2 text-3xl font-bold text-[var(--success)]">Rp{{ number_format($data['today_revenue'], 0, ',', '.') }}</p>
        </article>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="border border-[var(--line)] bg-white p-5 sm:p-6">
            <h2 class="mb-4 font-bold">Menu Cepat</h2>
            <div class="grid gap-3 sm:grid-cols-2">
                <a href="{{ route('admin.reports.daily') }}" class="btn-secondary text-center py-3">Rekap Harian</a>
                <a href="{{ route('admin.reports.monthly') }}" class="btn-secondary text-center py-3">Rekap Bulanan</a>
                <a href="{{ route('admin.reports.stock') }}" class="btn-secondary text-center py-3">Laporan Stok</a>
                <a href="{{ route('admin.reports.documentation') }}" class="btn-secondary text-center py-3">Dokumentasi</a>
                <a href="{{ route('admin.reports.incomplete-payments') }}" class="btn-secondary text-center py-3">Pembayaran Tanpa Dok. Lengkap</a>
                <a href="{{ route('admin.reports.reminder') }}" class="btn-secondary text-center py-3">Rekap Reminder</a>
            </div>
        </section>

        <section class="border border-[var(--line)] bg-white p-5 sm:p-6">
            <h2 class="mb-4 font-bold">PKB per Status</h2>
            <div class="space-y-3">
                @foreach ($data['pkb_per_status'] as $status => $count)
                    <div class="flex justify-between gap-3">
                        <span class="text-[var(--muted)]">{{ $status }}</span>
                        <span class="font-semibold">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
@endsection