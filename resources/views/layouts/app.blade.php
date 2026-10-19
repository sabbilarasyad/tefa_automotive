<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'PKB') · TEFA Automotive</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen antialiased bg-[var(--paper)] text-[var(--ink)]">
        <header class="border-b border-[var(--line)] bg-white sticky top-0 z-40">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                <a href="{{ route('admin.pkb.index') }}" class="flex items-center gap-3 text-[var(--ink)] no-underline">
                    <span class="grid size-10 place-items-center rounded-md bg-[var(--orange)] text-sm font-black tracking-wide text-white">TA</span>
                    <span>
                        <span class="block text-sm font-bold">TEFA Automotive</span>
                        <span class="block text-xs text-[var(--muted)]">Bengkel & layanan servis</span>
                    </span>
                </a>

                <button type="button"
                        class="xl:hidden inline-flex items-center justify-center p-2 rounded-md text-[var(--muted)] hover:text-[var(--ink)] hover:bg-[var(--line)]"
                        aria-controls="main-nav"
                        aria-expanded="false"
                        aria-label="Buka menu navigasi"
                        id="nav-toggle">
                    <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <nav id="main-nav" class="flex flex-col xl:flex-row xl:items-center xl:gap-2 sm:gap-5 w-full xl:w-auto xl:bg-transparent bg-white xl:shadow-none shadow-lg xl:border-none border-t border-[var(--line)] xl:py-0 py-4 xl:px-0 px-4 xl:static absolute xl:visible hidden xl:bg-transparent xl:top-auto top-full left-0 z-30" aria-label="Menu utama">
                    @php
                        $adminLinks = [];
                        if (auth()->user()->hasPermission('pkb.view_own') && ! auth()->user()->hasPermission('pkb.view_all')) {
                            $adminLinks[] = ['route' => route('mechanic.tasks'), 'text' => 'Tugas & Riwayat Saya'];
                        } else {
                            $adminLinks[] = ['route' => route('admin.pkb.index'), 'text' => 'Daftar PKB'];
                        }
                        if (auth()->user()->can('viewProgressList', App\Models\Pkb::class)) {
                            $adminLinks[] = ['route' => route('admin.pkb.progress.index'), 'text' => 'Progres Pekerjaan'];
                        }
                        if (auth()->user()->can('viewAny', App\Models\Invoice::class)) {
                            $adminLinks[] = ['route' => route('cashier.index'), 'text' => 'Kasir'];
                        }
                        if (auth()->user()->can('create', App\Models\Pkb::class)) {
                            $adminLinks[] = ['route' => route('admin.pkb.create'), 'text' => 'Servis Baru'];
                        }
                        if (auth()->user()->can('manageUsers', App\Models\User::class)) {
                            $adminLinks[] = ['route' => route('admin.users.index'), 'text' => 'Pengguna'];
                        }
                        if (auth()->user()->can('manageSpareparts', App\Models\Sparepart::class)) {
                            $adminLinks[] = ['route' => route('admin.stock.index'), 'text' => 'Stok'];
                        }
                        if (auth()->user()->can('manageServices', App\Models\Service::class)) {
                            $adminLinks[] = ['route' => route('admin.services.index'), 'text' => 'Jasa'];
                        }
                        if (auth()->user()->can('view', App\Models\ServiceHistory::class)) {
                            $adminLinks[] = ['route' => route('admin.service-history.index'), 'text' => 'Riwayat Servis'];
                        }
                        if (auth()->user()->can('manageReminders', App\Models\Reminder::class)) {
                            $adminLinks[] = ['route' => route('admin.reminder.index'), 'text' => 'Reminder'];
                        }
                        if (auth()->user()->can('view', App\Models\Report::class)) {
                            $adminLinks[] = ['route' => route('admin.reports.index'), 'text' => 'Laporan'];
                        }
                    @endphp
                    @foreach ($adminLinks as $link)
                        <a href="{{ $link['route'] }}" class="nav-link">{{ $link['text'] }}</a>
                    @endforeach
                    <span class="xl:inline-block hidden px-3 py-2 text-sm text-[var(--muted)]">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('admin.logout') }}" class="xl:inline w-full xl:w-auto">
                        @csrf
                        <button type="submit" class="nav-link btn-secondary text-center">Keluar</button>
                    </form>
                </nav>

                <button type="button"
                        class="xl:hidden inline-flex items-center justify-center p-2 rounded-md text-[var(--muted)] hover:text-[var(--ink)] hover:bg-[var(--line)]"
                        aria-controls="main-nav"
                        aria-expanded="false"
                        aria-label="Buka menu navigasi"
                        id="nav-toggle">
                    <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </header>
        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-5 border-l-4 border-[var(--orange)] bg-[var(--orange-light)] px-4 py-3 text-sm text-[var(--orange-deep)]" role="status">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-5 border-l-4 border-[var(--red)] bg-red-50 px-4 py-3 text-sm text-[var(--red)]" role="alert">
                    <p class="font-semibold">Periksa kembali data yang ditandai.</p>
                    <ul class="mt-2 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            @yield('content')
        </main>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var toggleButton = document.getElementById('nav-toggle');
                var nav = document.getElementById('main-nav');

                toggleButton.addEventListener('click', function () {
                    var isExpanded = toggleButton.getAttribute('aria-expanded') === 'true';
                    toggleButton.setAttribute('aria-expanded', !isExpanded);
                    nav.classList.toggle('hidden');
                    nav.classList.toggle('flex');
                });

                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape' && !nav.classList.contains('hidden')) {
                        nav.classList.add('hidden');
                        nav.classList.remove('flex');
                        toggleButton.setAttribute('aria-expanded', 'false');
                    }
                });
            });
        </script>