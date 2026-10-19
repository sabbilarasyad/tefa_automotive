@extends('layouts.app')

@section('title', 'Stok Sparepart')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="mb-1 text-xs font-bold uppercase tracking-[0.12em] text-[var(--orange-deep)]">Gudang · Sparepart</p>
            <h1 class="text-2xl font-bold">Daftar Stok</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.stock.index', ['filter' => 'low']) }}" class="btn-secondary text-sm {{ $filter === 'low' ? 'bg-[var(--orange)] text-white' : '' }}">Menipis ({{ $lowStockCount }})</a>
            <a href="{{ route('admin.stock.index', ['filter' => 'ok']) }}" class="btn-secondary text-sm {{ $filter === 'ok' ? 'bg-[var(--orange)] text-white' : '' }}">Aman</a>
            <a href="{{ route('admin.stock.index') }}" class="btn-secondary text-sm {{ $filter === null ? 'bg-[var(--orange)] text-white' : '' }}">Semua</a>
        </div>
    </div>

    <section class="overflow-hidden border border-[var(--line)] bg-white">
        @if ($spareparts->isEmpty())
            <p class="px-5 py-14 text-center text-sm text-[var(--muted)]">Tidak ada sparepart aktif.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px] text-left text-sm">
                    <thead class="bg-[#f3f7f5] text-xs uppercase text-[var(--muted)]">
                        <tr>
                            <th class="px-5 py-3">Kode / Nama</th>
                            <th class="px-5 py-3">Satuan</th>
                            <th class="px-5 py-3 text-right">Harga Jual</th>
                            <th class="px-5 py-3 text-right">Stok / Min</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e7eeea]">
                        @foreach ($spareparts as $part)
                            <tr class="hover:bg-[#f9fbfa]">
                                <td class="px-5 py-4">
                                    <p class="font-semibold">{{ $part->code }}</p>
                                    <p class="text-xs text-[var(--muted)]">{{ $part->name }}</p>
                                </td>
                                <td class="px-5 py-4">{{ $part->unit }}</td>
                                <td class="px-5 py-4 text-right">Rp{{ number_format((float) $part->selling_price, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-right font-mono">
                                    {{ number_format((float) $part->stock_qty, 0, ',', '.') }} / {{ number_format((float) $part->min_stock, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-4">
                                    @if ((float) $part->stock_qty <= (float) $part->min_stock)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[var(--orange-light)] px-2.5 py-1 text-xs font-semibold text-[var(--red)]">
                                            <svg class="size-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                                            Menipis
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[var(--success)]/10 px-2.5 py-1 text-xs font-semibold text-[var(--success)]">
                                            <svg class="size-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                            Aman
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.stock.history', $part) }}" class="btn-secondary text-xs">Riwayat</a>
                                        <button type="button"
                                            class="btn-primary text-xs"
                                            data-restock-target="#restock-modal"
                                            data-restock-part-id="{{ $part->id }}"
                                            data-restock-part-name="{{ $part->name }}"
                                            data-restock-part-code="{{ $part->code }}"
                                            data-restock-current-stock="{{ number_format((float) $part->stock_qty, 0, ',', '.') }}"
                                            data-restock-min-stock="{{ number_format((float) $part->min_stock, 0, ',', '.') }}">
                                            Restock
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-[var(--line)] px-5 py-4">{{ $spareparts->links() }}</div>
        @endif
    </section>

    <!-- Restock Modal -->
    <div id="restock-modal" class="hidden fixed inset-0 z-30 grid place-items-center bg-[#172321]/55 p-4" role="dialog" aria-modal="true" aria-labelledby="restock-title">
        <section class="w-full max-w-md border border-[var(--line)] bg-white p-6 shadow-xl">
            <h2 id="restock-title" class="text-lg font-bold">Restock Sparepart</h2>
            <p class="mt-1 text-sm text-[var(--muted)]" id="restock-part-info"></p>
            <form id="restock-form" class="mt-4 space-y-4" method="POST">
                @csrf
                <input type="hidden" name="sparepart_id" id="restock-sparepart-id">
                <div>
                    <label class="form-label" for="restock-qty">Jumlah Restock</label>
                    <input class="form-field" id="restock-qty" name="qty" type="number" step="0.001" min="0.001" required autocomplete="off">
                    @error('qty')<p class="mt-1 text-sm text-[var(--red)]">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label" for="restock-reason">Alasan <span class="text-[var(--red)]">*</span></label>
                    <textarea class="form-field" id="restock-reason" name="reason" rows="3" required placeholder="Contoh: Pembelian PO-12345 / Koreksi stok fisik / Retur dari bengkel"></textarea>
                    @error('reason')<p class="mt-1 text-sm text-[var(--red)]">{{ $message }}</p>@enderror
                </div>
                <div class="flex flex-wrap justify-end gap-3 pt-2">
                    <button type="button" class="btn-secondary text-sm" data-restock-close>Batal</button>
                    <button type="submit" class="btn-primary text-sm">Simpan Restock</button>
                </div>
            </form>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('restock-modal');
            const form = document.getElementById('restock-form');
            const closeBtns = document.querySelectorAll('[data-restock-close]');
            const openBtns = document.querySelectorAll('[data-restock-target="#restock-modal"]');

            const open = () => {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            };
            const close = () => {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
                form.reset();
            };

            openBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    document.getElementById('restock-sparepart-id').value = btn.dataset.restockPartId;
                    document.getElementById('restock-part-info').textContent = `${btn.dataset.restockPartCode} · ${btn.dataset.restockPartName} (Stok: ${btn.dataset.restockCurrentStock}, Min: ${btn.dataset.restockMinStock})`;
                    form.action = `{{ route('admin.stock.restock', ':id') }}`.replace(':id', btn.dataset.restockPartId);
                    open();
                });
            });

            closeBtns.forEach(btn => btn.addEventListener('click', close));
            modal.addEventListener('click', (e) => { if (e.target === modal) close(); });
        });
    </script>
@endsection
