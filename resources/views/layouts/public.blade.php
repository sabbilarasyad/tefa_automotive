<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'TEFA Automotive') · TEFA Automotive</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen antialiased bg-[var(--paper)] text-[var(--ink)]">
        <header class="border-b border-[var(--line)] bg-white sticky top-0 z-40">
            <nav class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8" aria-label="Main navigation">
                <a href="{{ route('public.home') }}" class="flex items-center gap-3 text-[var(--ink)] no-underline" aria-label="TEFA Automotive - Beranda">
                    <span class="grid size-10 place-items-center rounded-md bg-[var(--orange)] text-sm font-black tracking-wide text-white">TA</span>
                    <span>
                        <span class="block text-sm font-bold">TEFA Automotive</span>
                        <span class="block text-xs text-[var(--muted)]">Bengkel & Layanan Servis</span>
                    </span>
                </a>

                <button type="button"
                        class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-[var(--muted)] hover:text-[var(--ink)] hover:bg-[var(--line)]"
                        aria-controls="public-menu"
                        aria-expanded="false"
                        aria-label="Buka menu navigasi"
                        id="public-nav-toggle">
                    <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <div id="public-menu" class="md:flex md:items-center md:gap-6 flex-col md:flex-row md:items-center md:w-auto w-full md:static absolute md:visible hidden md:bg-transparent bg-white md:shadow-none shadow-lg md:border-none border-t border-[var(--line)] md:py-0 py-4 md:px-0 px-4 md:top-auto top-full left-0 z-30" aria-label="Menu publik">
                    <a href="{{ route('public.home') }}" class="nav-link-ink">Beranda</a>
                    <a href="{{ route('public.about') }}" class="nav-link-ink">Tentang Kami</a>
                    <a href="{{ route('public.services.index') }}" class="nav-link-ink">Layanan</a>
                    <a href="{{ route('public.gallery') }}" class="nav-link-ink">Galeri</a>
                    <a href="{{ route('public.contact') }}" class="nav-link-ink">Kontak</a>
                    <a href="{{ route('login') }}" class="nav-link-ink btn-primary text-center">Login</a>
                </div>
            </nav>
        </header>
        <main class="pt-0">
            @yield('content')
        </main>
        <footer class="border-t border-[var(--line)] bg-white">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="grid gap-8 md:grid-cols-4">
                    <div class="md:col-span-2">
                        <h3 class="text-lg font-bold text-[var(--ink)]">TEFA Automotive</h3>
                        <p class="mt-2 text-sm text-[var(--muted)]">Bengkel profesional dengan layanan servis kendaraan terpercaya. Kami berkomitmen memberikan pelayanan terbaik untuk kendaraan Anda.</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-[var(--ink)]">Layanan</h4>
                        <ul class="mt-2 space-y-1 text-sm text-[var(--muted)]">
                            @foreach ($footerServices as $service)
                                <li><a href="{{ route('public.services.show', $service) }}" class="hover:text-[var(--orange)]">{{ $service->name }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold text-[var(--ink)]">Kontak</h4>
                        <ul class="mt-2 space-y-1 text-sm text-[var(--muted)]">
                            <li>{{ config('tefa.address') }}</li>
                            <li>Telp: {{ config('tefa.phone') }}</li>
                            <li>Email: {{ config('tefa.email') }}</li>
                        </ul>
                    </div>
                </div>
                <div class="mt-8 pt-6 border-t border-[var(--line)] text-center text-sm text-[var(--muted)]">
                    <p>&copy; {{ date('Y') }} TEFA Automotive. Hak Cipta Dilindungi.</p>
                </div>
            </div>
        </footer>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var toggleButton = document.getElementById('public-nav-toggle');
                var nav = document.getElementById('public-menu');

                if (toggleButton && nav) {
                    toggleButton.addEventListener('click', function () {
                        var isExpanded = toggleButton.getAttribute('aria-expanded') === 'true';
                        toggleButton.setAttribute('aria-expanded', !isExpanded);
                        nav.classList.toggle('hidden');
                        nav.classList.toggle('flex');
                    });
                }

                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape' && !nav.classList.contains('hidden')) {
                        nav.classList.add('hidden');
                        nav.classList.remove('flex');
                        toggleButton.setAttribute('aria-expanded', 'false');
                    }
                });
            });
        </script>