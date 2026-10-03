<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'PKB') · TEFA Automotive</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen antialiased">
        <header class="border-b border-[#dce5e1] bg-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                <a href="{{ route('admin.pkb.index') }}" class="flex items-center gap-3 text-[#172321] no-underline">
                    <span class="grid size-10 place-items-center rounded-md bg-[#126b56] text-sm font-black tracking-wide text-white">TA</span>
                    <span>
                        <span class="block text-sm font-bold">TEFA Automotive</span>
                        <span class="block text-xs text-[#687671]">Bengkel & layanan servis</span>
                    </span>
                </a>
                <nav class="flex items-center gap-2 sm:gap-5">
                    @if (auth()->user()->hasPermission('pkb.view_own') && ! auth()->user()->hasPermission('pkb.view_all'))
                        <a href="{{ route('mechanic.tasks') }}" class="text-sm font-semibold text-[#34433e] hover:text-[#126b56]">Tugas & Riwayat Saya</a>
                    @else
                        <a href="{{ route('admin.pkb.index') }}" class="text-sm font-semibold text-[#34433e] hover:text-[#126b56]">Daftar PKB</a>
                    @endif
                    @can('viewProgressList', App\Models\Pkb::class)
                        <a href="{{ route('admin.pkb.progress.index') }}" class="hidden text-sm font-semibold text-[#34433e] hover:text-[#126b56] lg:inline">Progres Pekerjaan</a>
                    @endcan
                    @can('viewAny', App\Models\Invoice::class)
                        <a href="{{ route('cashier.index') }}" class="hidden text-sm font-semibold text-[#34433e] hover:text-[#126b56] lg:inline">Kasir</a>
                    @endcan
                    @can('create', App\Models\Pkb::class)
                        <a href="{{ route('admin.pkb.create') }}" class="hidden text-sm font-semibold text-[#34433e] hover:text-[#126b56] sm:inline">Servis Baru</a>
                    @endcan
                    @can('manageSpareparts', App\Models\Sparepart::class)
                        <a href="{{ route('admin.stock.index') }}" class="hidden text-sm font-semibold text-[#34433e] hover:text-[#126b56] lg:inline">Stok</a>
                    @endcan
                    @can('view', App\Models\ServiceHistory::class)
                        <a href="{{ route('admin.service-history.index') }}" class="hidden text-sm font-semibold text-[#34433e] hover:text-[#126b56] lg:inline">Riwayat Servis</a>
                    @endcan
                    @can('manageReminders', App\Models\Reminder::class)
                        <a href="{{ route('admin.reminder.index') }}" class="hidden text-sm font-semibold text-[#34433e] hover:text-[#126b56] lg:inline">Reminder</a>
                    @endcan
                    <span class="hidden text-sm text-[#687671] md:inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button class="btn-secondary px-3 py-2 text-xs" type="submit">Keluar</button>
                    </form>
                </nav>
            </div>
        </header>
        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-5 border-l-4 border-[#126b56] bg-[#e9f4ee] px-4 py-3 text-sm text-[#174e40]" role="status">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-5 border-l-4 border-[#b54039] bg-[#fff1ee] px-4 py-3 text-sm text-[#762c28]" role="alert">
                    <p class="font-semibold">Periksa kembali data yang ditandai.</p>
                    <ul class="mt-2 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            @yield('content')
        </main>
    </body>
</html>