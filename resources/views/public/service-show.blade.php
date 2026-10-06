@extends('layouts.public')

@section('title', $service->name)

@section('content')
    <section class="py-12 sm:py-16 lg:py-20 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <nav class="mb-8" aria-label="Breadcrumb">
                <ol class="flex items-center gap-2 text-sm text-[var(--muted)]">
                    <li><a href="{{ route('public.home') }}" class="hover:text-[var(--orange)]">Beranda</a></li>
                    <li aria-hidden="true">/</li>
                    <li><a href="{{ route('public.services.index') }}" class="hover:text-[var(--orange)]">Layanan</a></li>
                    <li aria-hidden="true">/</li>
                    <li class="text-[var(--ink)] font-medium" aria-current="page">{{ $service->name }}</li>
                </ol>
            </nav>

            <article class="max-w-3xl mx-auto">
                <header class="mb-8">
                    <h1 class="text-3xl sm:text-4xl font-bold text-[var(--ink)]">{{ $service->name }}</h1>
                    <div class="mt-4 flex flex-wrap items-center gap-4">
                        @if ($service->is_active)
                            <span class="inline-flex items-center px-3 py-1 text-sm font-medium text-[var(--success)] bg-[var(--success)]/10 rounded-full">Aktif</span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 text-sm font-medium text-[var(--red)] bg-[var(--red)]/10 rounded-full">Nonaktif</span>
                        @endif
                    </div>
                </header>

                @if ($service->description)
                    <div class="prose prose-sm max-w-none text-[var(--muted)] mb-8">
                        <p>{{ $service->description }}</p>
                    </div>
                @endif

                <div class="bg-[var(--paper)] rounded-xl p-6 border border-[var(--line)]">
                    <h2 class="text-lg font-semibold text-[var(--ink)] mb-4">Detail Layanan</h2>
                    <dl class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm text-[var(--muted)]">Status</dt>
                            <dd class="font-medium text-[var(--ink)]">{{ $service->is_active ? 'Aktif' : 'Nonaktif' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="mt-8 text-center">
                    <a href="{{ route('public.contact') }}" class="inline-flex items-center px-6 py-3 text-base font-semibold text-white bg-[var(--orange)] rounded-md hover:bg-[var(--orange-deep)] transition-colors">Konsultasi / Booking Layanan</a>
                </div>
            </article>
        </div>
    </section>
@endsection