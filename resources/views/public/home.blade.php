@extends('layouts.public')

@section('title', 'Beranda')

@section('content')
    <!-- Hero Section -->
    <section class="relative bg-[var(--color-primary)] text-white py-16 sm:py-24 lg:py-32 overflow-hidden">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="max-w-2xl">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight">Servis Kendaraan Terpercaya & Profesional</h1>
                <p class="mt-6 text-lg sm:text-xl text-white/90 max-w-xl">TEFA Automotive menyediakan layanan servis berkala, perbaikan mesin, dan sparepart original dengan tenah mekanik berpengalaman dan peralatan modern.</p>
                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="{{ route('public.services.index') }}" class="inline-flex items-center px-6 py-3 text-base font-semibold text-[var(--color-primary)] bg-white rounded-md hover:bg-gray-100 transition-colors">Lihat Layanan</a>
                    <a href="{{ route('public.contact') }}" class="inline-flex items-center px-6 py-3 text-base font-semibold text-white border-2 border-white rounded-md hover:bg-white/10 transition-colors">Hubungi Kami</a>
                </div>
            </div>
        </div>
        <div class="absolute inset-0 bg-gradient-to-r from-[var(--color-primary)]/90 to-transparent" aria-hidden="true"></div>
    </section>

    <!-- Features -->
    <section class="py-12 sm:py-16 lg:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['icon' => '🔧', 'title' => 'Mekanik Berpengalaman', 'desc' => 'Tim mekanik tersertifikasi dengan pengalaman puluhan tahun.'],
                    ['icon' => '⚙️', 'title' => 'Peralatan Modern', 'desc' => 'Menggunakan alat diagnostik dan peralatan bengkel terkini.'],
                    ['icon' => '📦', 'title' => 'Sparepart Original', 'desc' => 'Hanya menggunakan sparepart berkualitas original pabrikan.'],
                    ['icon' => '⏰', 'title' => 'Proses Cepat', 'desc' => 'Sistem kerja efisien untuk meminimalkan waktu tunggu.'],
                ] as $feature)
                    <div class="text-center p-6 bg-white rounded-xl border border-[var(--color-border)] hover:shadow-lg transition-shadow">
                        <div class="text-4xl mb-3">{{ $feature['icon'] }}</div>
                        <h3 class="text-lg font-semibold text-[var(--color-text)]">{{ $feature['title'] }}</h3>
                        <p class="mt-2 text-sm text-[var(--color-muted)]">{{ $feature['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Services Preview -->
    <section class="py-12 sm:py-16 lg:py-20 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-[var(--color-text)]">Layanan Unggulan</h2>
                    <p class="mt-1 text-[var(--color-muted)]">Berbagai layanan servis untuk kebutuhan kendaraan Anda</p>
                </div>
                <a href="{{ route('public.services.index') }}" class="inline-flex items-center text-sm font-medium text-[var(--color-primary)] hover:text-[var(--color-primary-hover)]">Lihat Semua <span aria-hidden="true">→</span></a>
            </div>
            @if ($services->isEmpty())
                <p class="text-center text-[var(--color-muted)] py-12">Belum ada layanan yang tersedia.</p>
            @else
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($services->take(6) as $service)
                        <article class="border border-[var(--color-border)] rounded-xl p-6 hover:shadow-md transition-shadow">
                            <h3 class="font-semibold text-[var(--color-text)]">{{ $service->name }}</h3>
                            @if ($service->description)
                                <p class="mt-2 text-sm text-[var(--color-muted)] line-clamp-2">{{ $service->description }}</p>
                            @endif
                            <a href="{{ route('public.services.show', $service) }}" class="mt-4 inline-flex items-center text-sm font-medium text-[var(--color-primary)] hover:text-[var(--color-primary-hover)]">Selengkapnya <span aria-hidden="true">→</span></a>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <!-- CTA -->
    <section class="py-12 sm:py-16 lg:py-20 bg-[var(--color-primary)] text-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-2xl sm:text-3xl font-bold">Siap Memberi Servis Terbaik untuk Kendaraan Anda?</h2>
            <p class="mt-4 text-lg text-white/90 max-w-2xl mx-auto">Jadwalkan servis berkala atau konsultasikan keluhan kendaraan Anda dengan tim kami.</p>
            <a href="{{ route('public.contact') }}" class="mt-8 inline-flex items-center px-8 py-3 text-base font-semibold text-[var(--color-primary)] bg-white rounded-md hover:bg-gray-100 transition-colors">Hubungi Kami Sekarang</a>
        </div>
    </section>
@endsection