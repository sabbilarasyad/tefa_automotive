@extends('layouts.public')

@section('title', 'Galeri')

@section('content')
    <section class="py-12 sm:py-16 lg:py-20 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <header class="text-center max-w-3xl mx-auto mb-12">
                <h1 class="text-3xl sm:text-4xl font-bold text-[var(--color-text)]">Galeri TEFA Automotive</h1>
                <p class="mt-4 text-lg text-[var(--color-muted)]">Momen-momen dari bengkel dan hasil pekerjaan kami</p>
            </header>

            @if (empty($images))
                <p class="text-center text-[var(--color-muted)] py-12">Belum ada foto galeri.</p>
            @else
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($images as $image)
                        <figure class="group relative overflow-hidden rounded-xl border border-[var(--color-border)] bg-[var(--color-bg)]">
                            <img
                                src="{{ $image['url'] }}"
                                alt="{{ $image['alt'] }}"
                                class="w-full h-64 object-cover transition-transform duration-300 group-hover:scale-105"
                                loading="lazy"
                            >
                            <figcaption class="absolute inset-0 bg-black/50 text-white flex items-end p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <p class="text-sm font-medium">{{ $image['alt'] }}</p>
                            </figcaption>
                        </figure>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection