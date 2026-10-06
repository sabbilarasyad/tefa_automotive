<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'PKB') · TEFA Automotive</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen antialiased bg-[var(--paper)] text-[var(--ink)]">
        <header class="border-b border-[var(--line)] bg-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                <a href="{{ route('admin.pkb.index') }}" class="flex items-center gap-3 text-[var(--ink)] no-underline">
                    <span class="grid size-10 place-items-center rounded-md bg-[var(--orange)] text-sm font-black tracking-wide text-white">TA</span>
                    <span>
                        <span class="block text-sm font-bold">TEFA Automotive</span>
                        <span class="block text-xs text-[var(--muted)]">Bengkel & layanan servis</span>
                    </span>
                </a>
                <nav class="flex items-center gap-2 sm:gap-5">
                    @if (auth()->user()->hasPermission('pkb.view_own') && ! auth()->user()->hasPermission('pkb.view_all'))
                        <a href="{{ route('mechanic.tasks') }}" class="text-sm font-semibold text-[var(--muted)] hover:text-[var(--orange)]">Tugas & Riwayat Saya</a>
                    @else
                        <a href="{{ route('admin.pkb.index') }}" class="text-sm font-semibold text-[var(--muted)] hover:text-[var(--orange)]">Daftar PKB</a>
                    @endif
                    @can('viewProgressList', App\Models\Pkb::class)
                        <a href="{{ route('admin.pkb.progress.index') }}" class="hidden text-sm font-semibold text-[var(--muted)] hover:text-[var(--orange)] lg:inline">Progres Pekerjaan</a>
                    @endcan
                    @can('viewAny', App\Models\Invoice::class)
                        <a href="{{ route('cashier.index') }}" class="hidden text-sm font-semibold text-[var(--muted)] hover:text-[var(--orange)] lg:inline">Kasir</a>
                    @endcan
                    @can('create', App\Models\Pkb::class)
                        <a href="{{ route('admin.pkb.create') }}" class="hidden text-sm font-semibold text-[var(--muted)] hover:text-[var(--orange)] sm:inline">Servis Baru</a>
                    @endcan
                    @can('manageUsers', App\Models\User::class)
                        <a href="{{ route('admin.users.index') }}" class="hidden text-sm font-semibold text-[var(--muted)] hover:text-[var(--orange)] lg:inline">Pengguna</a>
                    @endcan
                    @can('manageSpareparts', App\Models\Sparepart::class)
                        <a href="{{ route('admin.stock.index') }}" class="hidden text-sm font-semibold text-[var(--muted)] hover:text-[var(--orange)] lg:inline">Stok</a>
                    @endcan
                    @can('manageServices', App\Models\Service::class)
                        <a href="{{ route('admin.services.index') }}" class="hidden text-sm font-semibold text-[var(--muted)] hover:text-[var(--orange)] lg:inline">Jasa</a>
                    @endcan
                    @can('view', App\Models\ServiceHistory::class)
                        <a href="{{ route('admin.service-history.index') }}" class="hidden text-sm font-semibold text-[var(--muted)] hover:text-[var(--orange)] lg:inline">Riwayat Servis</a>
                    @endcan
                    @can('manageReminders', App\Models\Reminder::class)
                        <a href="{{ route('admin.reminder.index') }}" class="hidden text-sm font-semibold text-[var(--muted)] hover:text-[var(--orange)] lg:inline">Reminder</a>
                    @endcan
                    <span class="hidden text-sm text-[var(--muted)] md:inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button class="btn-secondary px-3 py-2 text-xs" type="submit">Keluar</button>
                    </form>
                </nav>
            </div>
        </header>
        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-5 border-l-4 border-[var(--orange)] bg-[var(--orange-light)] px-4 py-3 text-sm text-[var(--orange-deep)]" role="status">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-5 border-l-4 border-[var(--red)] bg-[var(--red)]/50 px-4 py-3 text-sm text-[var(--red)]" role="alert">
                    <p class="font-semibold">Periksa kembali data yang ditandai.</p>
                    <ul class="mt-2 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            @yield('content')
        </main>
    </body>
</html>