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
                <div class="hidden md:flex md:items-center md:gap-6">
                    <a href="{{ route('public.home') }}" class="text-sm font-medium text-[var(--ink)] hover:text-[var(--orange)] transition-colors">Beranda</a>
                    <a href="{{ route('public.about') }}" class="text-sm font-medium text-[var(--ink)] hover:text-[var(--orange)] transition-colors">Tentang Kami</a>
                    <a href="{{ route('public.services.index') }}" class="text-sm font-medium text-[var(--ink)] hover:text-[var(--orange)] transition-colors">Layanan</a>
                    <a href="{{ route('public.gallery') }}" class="text-sm font-medium text-[var(--ink)] hover:text-[var(--orange)] transition-colors">Galeri</a>
                    <a href="{{ route('public.contact') }}" class="text-sm font-medium text-[var(--ink)] hover:text-[var(--orange)] transition-colors">Kontak</a>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center px-4 py-2 text-sm font-medium text-[var(--orange)] hover:text-[var(--orange-deep)]">Masuk</a>
                    <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-[var(--orange)] rounded-md hover:bg-[var(--orange-deep)] transition-colors">Login</a>
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
                            <li><a href="{{ route('public.services.index') }}" class="hover:text-[var(--orange)]">Servis Berkala</a></li>
                            <li><a href="{{ route('public.services.index') }}" class="hover:text-[var(--orange)]">Ganti Oli</a></li>
                            <li><a href="{{ route('public.services.index') }}" class="hover:text-[var(--orange)]">Perbaikan Mesin</a></li>
                            <li><a href="{{ route('public.services.index') }}" class="hover:text-[var(--orange)]">Sparepart Original</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold text-[var(--ink)]">Kontak</h4>
                        <ul class="mt-2 space-y-1 text-sm text-[var(--muted)]">
                            <li>Jl. Raya No. 123, Jakarta</li>
                            <li>Telp: 021-XXXX-XXXX</li>
                            <li>Email: info@tefa.co.id</li>
                        </ul>
                    </div>
                </div>
                <div class="mt-8 pt-6 border-t border-[var(--line)] text-center text-sm text-[var(--muted)]">
                    <p>&copy; {{ date('Y') }} TEFA Automotive. Hak Cipta Dilindungi.</p>
                </div>
            </div>
        </footer>
    </body>
</html>