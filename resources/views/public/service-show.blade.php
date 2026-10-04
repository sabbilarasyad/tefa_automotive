@extends('layouts.public')

@section('title', $service->name)

@section('content')
    <section class="py-12 sm:py-16 lg:py-20 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <nav class="mb-8" aria-label="Breadcrumb">
                <ol class="flex items-center gap-2 text-sm text-[var(--color-muted)]">
                    <li><a href="{{ route('public.home') }}" class="hover:text-[var(--color-primary)]">Beranda</a></li>
                    <li aria-hidden="true">/</li>
                    <li><a href="{{ route('public.services.index') }}" class="hover:text-[var(--color-primary)]">Layanan</a></li>
                    <li aria-hidden="true">/</li>
                    <li class="text-[var(--color-text)] font-medium" aria-current="page">{{ $service->name }}</li>
                </ol>
            </nav>

            <article class="max-w-3xl mx-auto">
                <header class="mb-8">
                    <h1 class="text-3xl sm:text-4xl font-bold text-[var(--color-text)]">{{ $service->name }}</h1>
                    <div class="mt-4 flex flex-wrap items-center gap-4">
                        <span class="inline-flex items-center px-3 py-1 text-sm font-medium text-[var(--color-primary)] bg-[var(--color-primary-light)] rounded-full">Kode: {{ $service->code }}</span>
                        @if ($service->documentation_required)
                            <span class="inline-flex items-center px-3 py-1 text-sm font-medium text-[var(--color-accent)] bg-[var(--color-accent-bg)] rounded-full">Dokumentasi Wajib</span>
                        @endif
                        @if ($service->is_active)
                            <span class="inline-flex items-center px-3 py-1 text-sm font-medium text-[var(--color-success)] bg-[var(--color-success-bg)] rounded-full">Aktif</span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 text-sm font-medium text-[var(--color-danger)] bg-[var(--color-danger-bg)] rounded-full">Nonaktif</span>
                        @endif
                    </div>
                </header>

                @if ($service->description)
                    <div class="prose prose-sm max-w-none text-[var(--color-muted)] mb-8">
                        <p>{{ $service->description }}</p>
                    </div>
                @endif

                <div class="bg-[var(--color-bg)] rounded-xl p-6 border border-[var(--color-border)]">
                    <h2 class="text-lg font-semibold text-[var(--color-text)] mb-4">Detail Layanan</h2>
                    <dl class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm text-[var(--color-muted)]">Kode Layanan</dt>
                            <dd class="font-medium text-[var(--color-text)]">{{ $service->code }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-[var(--color-muted)]">Status</dt>
                            <dd class="font-medium text-[var(--color-text)]">{{ $service->is_active ? 'Aktif' : 'Nonaktif' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-[var(--color-muted)]">Dokumentasi Foto</dt>
                            <dd class="font-medium text-[var(--color-text)]">{{ $service->documentation_required ? 'Wajib' : 'Tidak Wajib' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="mt-8 text-center">
                    <a href="{{ route('public.contact') }}" class="inline-flex items-center px-6 py-3 text-base font-semibold text-white bg-[var(--color-primary)] rounded-md hover:bg-[var(--color-primary-hover)] transition-colors">Konsultasi / Booking Layanan</a>
                </div>
            </article>
        </div>
    </section>
@endsection