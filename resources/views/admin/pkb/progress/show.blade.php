@extends('layouts.app')

@section('title', 'Input Progres · '.$pkb->number)

@section('content')
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <a class="mb-3 inline-block text-sm font-semibold text-[var(--orange-deep)]" href="{{ route('admin.pkb.progress.index') }}">← Antrean progres</a>
            <h1 class="text-2xl font-bold">Input Progres Pekerjaan</h1>
            <p class="mt-1 text-sm text-[var(--muted)]">{{ $pkb->number }} · {{ $pkb->customer->name }} · {{ $pkb->vehicle->plate_number }}</p>
        </div>
        <a class="btn-secondary text-sm" href="{{ route('admin.pkb.show', $pkb) }}">Detail PKB</a>
    </div>

    <div class="grid gap-5 lg:grid-cols-[minmax(0,1.5fr)_minmax(270px,0.75fr)]">
        <div class="space-y-5">
            <section class="border border-[var(--line)] bg-white p-5 sm:p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div><p class="text-xs font-semibold uppercase text-[var(--muted)]">Keluhan</p><p class="mt-1 font-semibold">{{ $pkb->complaint }}</p></div>
                    <div class="text-right"><p class="text-xs font-semibold uppercase text-[var(--muted)]">Mekanik</p><p class="mt-1 font-semibold">{{ $pkb->mechanic?->name ?? 'Belum ditugaskan' }}</p></div>
                </div>
                @if ($pkb->status === 'WAITING_MECHANIC')
                    @can('startWork', $pkb)
                        <form class="mt-5 border-t border-[var(--line)] pt-5" method="POST" action="{{ route('admin.pkb.progress.start', $pkb) }}" onsubmit="return confirm('Mulai pekerjaan untuk PKB ini?')">
                            @csrf
                            <button class="btn-primary" type="submit">Mulai Dikerjakan</button>
                        </form>
                    @endcan
                @elseif ($pkb->status !== 'IN_PROGRESS')
                    <p class="mt-5 border-t border-[var(--line)] pt-5 text-sm text-[var(--muted)]">PKB ini sudah melewati tahap input progres.</p>
                @endif
            </section>

            @if ($pkb->status === 'IN_PROGRESS')
                <section class="border border-[var(--line)] bg-white p-5 sm:p-6">
                    <div class="mb-4">
                        <h2 class="font-bold">Pemakaian part aktual</h2>
                        <p class="mt-1 text-sm text-[var(--muted)]">Pencatatan ini belum mengurangi stok. Stok dipotong saat pekerjaan divalidasi selesai.</p>
                    </div>
                    <form method="POST" action="{{ route('admin.pkb.progress.spareparts', $pkb) }}" class="space-y-3">
                        @csrf
                        @method('PATCH')
                        @forelse ($pkb->spareparts as $index => $part)
                            <div class="grid gap-3 border border-[#e3eae6] bg-[#f8faf9] p-3 sm:grid-cols-[minmax(0,1fr)_130px_auto] sm:items-center">
                                <div><p class="font-semibold">{{ $part->name_snapshot }}</p><p class="text-xs text-[var(--muted)]">Rencana {{ $part->qty_planned }} · Stok kini {{ $part->sparepart->stock_qty }} {{ $part->sparepart->unit }}</p></div>
                                <input type="hidden" name="spareparts[{{ $index }}][pkb_sparepart_id]" value="{{ $part->id }}">
                                <div><label class="form-label" for="qty_used_{{ $part->id }}">Qty aktual</label><input class="form-field" id="qty_used_{{ $part->id }}" name="spareparts[{{ $index }}][qty_used]" type="number" min="0" step="0.001" value="{{ old('spareparts.'.$index.'.qty_used', $part->qty_used ?? $part->qty_planned) }}" required></div>
                                <span class="text-xs font-semibold {{ $part->documentation_required ? 'text-[#9a5b10]' : 'text-[var(--muted)]' }}">{{ $part->documentation_required ? 'Foto wajib' : 'Foto opsional' }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-[var(--muted)]">Tidak ada sparepart pada PKB ini.</p>
                        @endforelse
                        @if ($pkb->spareparts->isNotEmpty())
                            <button class="btn-secondary text-sm" type="submit">Simpan qty aktual</button>
                        @endif
                    </form>
                </section>

                <section class="border border-[var(--line)] bg-white p-5 sm:p-6">
                    <h2 class="mb-4 font-bold">Dokumentasi pekerjaan</h2>
                    <div class="space-y-5">
                        @foreach ($pkb->services as $service)
                            @include('admin.pkb.progress.part-documentation', ['item' => $service, 'itemType' => 'service'])
                        @endforeach
                        @foreach ($pkb->spareparts as $part)
                            @include('admin.pkb.progress.part-documentation', ['item' => $part, 'itemType' => 'sparepart'])
                        @endforeach
                    </div>
                </section>

                <section class="border border-[var(--line)] bg-white p-5 sm:p-6">
                    <h2 class="font-bold">Ringkasan sebelum validasi</h2>
                    <div class="mt-4 divide-y divide-[#e7eeea]">
                        @foreach ($pkb->services as $service)
                            <div class="flex justify-between gap-3 py-2 text-sm"><span>{{ $service->name_snapshot }} × {{ $service->qty }}</span><strong>Rp{{ number_format((float) $service->subtotal, 0, ',', '.') }}</strong></div>
                        @endforeach
                        @foreach ($pkb->spareparts as $part)
                            @php($actualQty = (float) ($part->qty_used ?? $part->qty_planned))
                            <div class="flex justify-between gap-3 py-2 text-sm"><span>{{ $part->name_snapshot }} × {{ number_format($actualQty, 3, ',', '.') }} aktual</span><strong>Rp{{ number_format($actualQty * (float) $part->unit_price, 0, ',', '.') }}</strong></div>
                        @endforeach
                    </div>
                    <form class="mt-5 border-t border-[var(--line)] pt-5" method="POST" action="{{ route('admin.pkb.progress.complete', $pkb) }}" onsubmit="return confirm('Validasi pekerjaan selesai? Stok akan dipotong sesuai qty aktual dan invoice dibuat.')">
                        @csrf
                        <p class="mb-3 text-sm text-[var(--muted)]">Dokumentasi wajib yang belum lengkap ditampilkan sebagai informasi dan tidak mengunci validasi.</p>
                        @can('validateComplete', $pkb)
                            <button class="btn-primary" type="submit">Validasi Selesai & Buat Invoice</button>
                        @endcan
                    </form>
                </section>
            @endif
        </div>

        <aside class="space-y-5">
            <section class="border border-[var(--line)] bg-white p-5">
                <h2 class="font-bold">Status pekerjaan</h2>
                <p class="mt-2 text-sm">{{ $pkb->status === 'IN_PROGRESS' ? 'Sedang dikerjakan' : ($pkb->status === 'WAITING_MECHANIC' ? 'Menunggu dikerjakan' : str_replace('_', ' ', $pkb->status)) }}</p>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-[var(--muted)]">Mulai</dt><dd>{{ $pkb->started_at?->format('d/m/Y H:i') ?? '-' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-[var(--muted)]">Ditugaskan</dt><dd class="text-right">{{ $pkb->mechanic?->name ?? '-' }}</dd></div>
                </dl>
            </section>
            @if ($pkb->invoice)
                <section class="border border-[var(--line)] bg-white p-5">
                    <h2 class="font-bold">Invoice dibuat</h2>
                    <p class="mt-2 text-sm font-semibold">{{ $pkb->invoice->number }}</p>
                    <p class="text-sm text-[var(--muted)]">Rp{{ number_format((float) $pkb->invoice->grand_total, 0, ',', '.') }}</p>
                </section>
            @endif
        </aside>
    </div>
    <script>
        document.querySelectorAll('[data-image-preview]').forEach(input => {
            input.addEventListener('change', () => {
                const preview = document.getElementById(input.dataset.imagePreview);
                const file = input.files?.[0];
                if (!file || !preview) return;
                preview.src = URL.createObjectURL(file);
                preview.classList.remove('hidden');
            });
        });
    </script>
@endsection
