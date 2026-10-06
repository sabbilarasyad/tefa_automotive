@extends('layouts.public')

@section('title', 'Layanan')

@section('content')
    <section class="py-12 sm:py-16 lg:py-20 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <header class="text-center max-w-3xl mx-auto mb-12">
                <h1 class="text-3xl sm:text-4xl font-bold text-[var(--ink)]">Layanan Kami</h1>
                <p class="mt-4 text-lg text-[var(--muted)]">Berbagai layanan servis kendaraan untuk memenuhi kebutuhan Anda</p>
            </header>

            @if ($services->isEmpty())
                <div class="text-center py-16">
                    <div class="text-6xl mb-4">🔧</div>
                    <h2 class="text-xl font-semibold text-[var(--ink)] mb-2">Belum Ada Layanan</h2>
                    <p class="text-[var(--muted)]">Saat ini belum ada layanan yang tersedia.</p>
                </div>
            @else
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($services as $service)
                        <article class="border border-[var(--line)] rounded-xl p-6 hover:shadow-md transition-shadow bg-white">
                            <div class="flex items-start justify-between gap-4 mb-4">
                                <h3 class="text-lg font-semibold text-[var(--ink)] flex-1">{{ $service->name }}</h3>
                                @if ($service->documentation_required)
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-[var(--orange)] bg-[var(--orange-light)] rounded-full shrink-0">Dok. Wajib</span>
                                @endif
                            </div>
                            @if ($service->description)
                                <p class="text-sm text-[var(--muted)] mb-4 line-clamp-3">{{ $service->description }}</p>
                            @endif
                            <a href="{{ route('public.services.show', $service) }}" class="inline-flex items-center text-sm font-medium text-[var(--orange)] hover:text-[var(--orange-deep)]">Detail <span aria-hidden="true">→</span></a>
                        </article>
                    @endforeach
                </div>

                <div class="mt-8">{{ $services->links() }}</div>
            @endif
        </div>
    </section>
@endsection