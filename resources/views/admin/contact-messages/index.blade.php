@extends('layouts.app')

@section('title', 'Pesan Kontak')

@section('content')
<div class="mb-6 flex items-center justify-between gap-4">
    <h1 class="text-2xl font-bold">Pesan Kontak</h1>
</div>

@if (session('success'))
    <div class="mb-5 border-l-4 border-[var(--orange)] bg-[var(--orange-light)] px-4 py-3 text-sm text-[var(--orange-deep)]" role="status">
        {{ session('success') }}
    </div>
@endif

<div class="mb-4 flex items-center gap-2">
    <form method="GET" class="flex items-center gap-2">
        <label for="status" class="sr-only">Filter status</label>
        <select name="status" id="status" class="form-field w-auto" onchange="this.form.submit()">
            <option value="">Semua</option>
            <option value="unread" {{ request()->status === 'unread' ? 'selected' : '' }}>Belum Dibaca</option>
            <option value="read" {{ request()->status === 'read' ? 'selected' : '' }}>Sudah Dibaca</option>
        </select>
    </form>
</div>

<div class="overflow-x-auto">
    <table class="w-full border-collapse">
        <thead>
            <tr class="border-b border-[var(--line)] text-left text-sm text-[var(--muted)]">
                <th class="pb-3 pr-4 font-semibold">Nama</th>
                <th class="pb-3 pr-4 font-semibold">Email</th>
                <th class="pb-3 pr-4 font-semibold">Subjek</th>
                <th class="pb-3 pr-4 font-semibold">Tanggal</th>
                <th class="pb-3 pr-4 font-semibold">Status</th>
                <th class="pb-3 pr-4 font-semibold w-32">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($messages as $message)
                <tr class="border-b border-[var(--line)] {{ $message->read_at ? '' : 'bg-[var(--orange-light)]' }}">
                    <td class="py-3 pr-4">{{ $message->name }}</td>
                    <td class="py-3 pr-4">{{ $message->email }}</td>
                    <td class="py-3 pr-4 max-w-xs truncate">{{ $message->subject }}</td>
                    <td class="py-3 pr-4 whitespace-nowrap">{{ $message->created_at->format('d/m/Y H:i') }}</td>
                    <td class="py-3 pr-4">
                        @if ($message->read_at)
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <svg class="size-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                Dibaca
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-[var(--orange-light)] text-[var(--orange-deep)]">
                                <svg class="size-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                                Belum Dibaca
                            </span>
                        @endif
                    </td>
                    <td class="py-3 pr-4">
                        <div class="flex items-center gap-2">
                            @if ($message->read_at)
                                <form method="POST" action="{{ route('admin.contact-messages.mark-unread', $message) }}" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn-secondary text-sm px-3 py-1" title="Tandai belum dibaca">
                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.contact-messages.mark-read', $message) }}" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn-primary text-sm px-3 py-1" title="Tandai sudah dibaca">
                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </button>
                                </form>
                            @endif
                            
                            {{-- Reply via email button --}}
                            <a href="mailto:{{ $message->email }}?subject={{ urlencode('Re: ' . $message->subject) }}&body={{ urlencode('Halo ' . $message->name . ',\n\nTerima kasih telah menghubungi TEFA Automotive. Kami telah menerima pesan Anda dengan subjek: ' . $message->subject . '\n\nKami akan segera merespons pesan Anda.\n\nSalam,\nTim TEFA Automotive') }}" class="btn-secondary text-sm px-3 py-1" title="Balas via email">
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                Balas
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-[var(--muted)]">Tidak ada pesan kontak.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $messages->links() }}
@endsection