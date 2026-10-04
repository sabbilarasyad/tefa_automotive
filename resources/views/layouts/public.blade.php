<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'TEFA Automotive') · TEFA Automotive</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            :root {
                --color-primary: #126b56;
                --color-primary-hover: #0e5644;
                --color-primary-light: #e6f2ed;
                --color-secondary: #34433e;
                --color-accent: #8a5513;
                --color-accent-bg: #fff2d9;
                --color-danger: #b54039;
                --color-danger-bg: #fff1ee;
                --color-success: #126b56;
                --color-success-bg: #e9f4ee;
                --color-muted: #687671;
                --color-border: #dce5e1;
                --color-bg: #f4f7f5;
                --color-white: #ffffff;
                --color-text: #172321;
            }
        </style>
    </head>
    <body class="min-h-screen antialiased bg-[var(--color-bg)] text-[var(--color-text)]">
        <header class="border-b border-[var(--color-border)] bg-[var(--color-white)] sticky top-0 z-40">
            <nav class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8" aria-label="Main navigation">
                <a href="{{ route('public.home') }}" class="flex items-center gap-3 text-[var(--color-text)] no-underline" aria-label="TEFA Automotive - Beranda">
                    <span class="grid size-10 place-items-center rounded-md bg-[var(--color-primary)] text-sm font-black tracking-wide text-white">TA</span>
                    <span>
                        <span class="block text-sm font-bold">TEFA Automotive</span>
                        <span class="block text-xs text-[var(--color-muted)]">Bengkel & Layanan Servis</span>
                    </span>
                </a>
                <div class="hidden md:flex md:items-center md:gap-6">
                    <a href="{{ route('public.home') }}" class="text-sm font-medium text-[var(--color-text)] hover:text-[var(--color-primary)] transition-colors">Beranda</a>
                    <a href="{{ route('public.about') }}" class="text-sm font-medium text-[var(--color-text)] hover:text-[var(--color-primary)] transition-colors">Tentang Kami</a>
                    <a href="{{ route('public.services.index') }}" class="text-sm font-medium text-[var(--color-text)] hover:text-[var(--color-primary)] transition-colors">Layanan</a>
                    <a href="{{ route('public.gallery') }}" class="text-sm font-medium text-[var(--color-text)] hover:text-[var(--color-primary)] transition-colors">Galeri</a>
                    <a href="{{ route('public.contact') }}" class="text-sm font-medium text-[var(--color-text)] hover:text-[var(--color-primary)] transition-colors">Kontak</a>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center px-4 py-2 text-sm font-medium text-[var(--color-primary)] hover:text-[var(--color-primary-hover)]">Masuk</a>
                    <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-[var(--color-primary)] rounded-md hover:bg-[var(--color-primary-hover)] transition-colors">Login</a>
                </div>
            </nav>
        </header>
        <main class="pt-0">
            @yield('content')
        </main>
        <footer class="border-t border-[var(--color-border)] bg-[var(--color-white)]">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="grid gap-8 md:grid-cols-4">
                    <div class="md:col-span-2">
                        <h3 class="text-lg font-bold text-[var(--color-text)]">TEFA Automotive</h3>
                        <p class="mt-2 text-sm text-[var(--color-muted)]">Bengkel profesional dengan layanan servis kendaraan terpercaya. Kami berkomitmen memberikan pelayanan terbaik untuk kendaraan Anda.</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-[var(--color-text)]">Layanan</h4>
                        <ul class="mt-2 space-y-1 text-sm text-[var(--color-muted)]">
                            <li><a href="{{ route('public.services.index') }}" class="hover:text-[var(--color-primary)]">Servis Berkala</a></li>
                            <li><a href="{{ route('public.services.index') }}" class="hover:text-[var(--color-primary)]">Ganti Oli</a></li>
                            <li><a href="{{ route('public.services.index') }}" class="hover:text-[var(--color-primary)]">Perbaikan Mesin</a></li>
                            <li><a href="{{ route('public.services.index') }}" class="hover:text-[var(--color-primary)]">Sparepart Original</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold text-[var(--color-text)]">Kontak</h4>
                        <ul class="mt-2 space-y-1 text-sm text-[var(--color-muted)]">
                            <li>Jl. Raya No. 123, Jakarta</li>
                            <li>Telp: 021-XXXX-XXXX</li>
                            <li>Email: info@tefa.co.id</li>
                        </ul>
                    </div>
                </div>
                <div class="mt-8 pt-6 border-t border-[var(--color-border)] text-center text-sm text-[var(--color-muted)]">
                    <p>&copy; {{ date('Y') }} TEFA Automotive. Hak Cipta Dilindungi.</p>
                </div>
            </div>
        </footer>
    </body>
</html>