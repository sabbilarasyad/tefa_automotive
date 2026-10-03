@extends('layouts.app')

@section('title', 'Checklist & Pembayaran · '.$invoice->number)

@section('content')
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <a class="mb-3 inline-block text-sm font-semibold text-[#126b56]" href="{{ route('cashier.index') }}">← Menunggu Pembayaran</a>
            <p class="mb-1 text-xs font-bold uppercase tracking-[0.12em] text-[#126b56]">Invoice {{ $invoice->number }}</p>
            <h1 class="text-2xl font-bold">Checklist Dokumentasi</h1>
            <p class="mt-1 text-sm text-[#687671]">{{ $invoice->pkb->number }} · {{ $invoice->pkb->customer->name }} · {{ $invoice->pkb->vehicle->plate_number }}</p>
        </div>
        <span class="rounded-full bg-[#fff2d9] px-3 py-1.5 text-xs font-semibold text-[#8a5513]">{{ $invoice->status === 'UNPAID' ? 'Belum lunas' : $invoice->status }}</span>
    </div>

    <div class="grid gap-5 lg:grid-cols-[minmax(0,1.4fr)_minmax(290px,0.8fr)]">
        <section class="border border-[#dce5e1] bg-white p-5 sm:p-6">
            <div class="mb-4 flex items-start justify-between gap-3">
                <div><h2 class="font-bold">Jasa & sparepart</h2><p class="mt-1 text-sm text-[#687671]">{{ $checklist['documentation_complete'] ? 'Dokumentasi lengkap' : 'Ada dokumentasi wajib yang belum lengkap' }}</p></div>
                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $checklist['documentation_complete'] ? 'bg-[#e6f2ed] text-[#175b49]' : 'bg-[#fff2d9] text-[#8a5513]' }}">{{ $checklist['documentation_complete'] ? 'Lengkap' : 'Perlu konfirmasi' }}</span>
            </div>
            <div class="divide-y divide-[#e7eeea]">
                @foreach ($checklist['items'] as $item)
                    <article class="py-4 first:pt-0 last:pb-0">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div><p class="font-semibold">{{ $item['name'] }}</p><p class="text-xs text-[#687671]">{{ $item['type'] === 'service' ? 'Jasa' : 'Sparepart' }} · {{ $item['documentation_required'] ? 'Foto wajib' : 'Foto opsional' }}</p></div>
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $item['complete'] ? 'bg-[#e6f2ed] text-[#175b49]' : 'bg-[#fff0ed] text-[#9b3d36]' }}">{{ $item['complete'] ? 'Lengkap' : 'Belum ada foto' }}</span>
                        </div>
                        @if ($item['documents'] !== [])
                            <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3">
                                @foreach ($item['documents'] as $document)
                                    <figure class="border border-[#dce5e1] bg-[#f8faf9] p-2">
                                        <a href="{{ $document['url'] }}" target="_blank" rel="noopener" aria-label="Buka {{ $document['file_name'] }}"><img class="h-28 w-full object-cover" src="{{ $document['url'] }}" alt="Dokumentasi {{ $item['name'] }}" loading="lazy"></a>
                                        <figcaption class="mt-2 truncate text-xs text-[#687671]">{{ $document['file_name'] }}</figcaption>
                                    </figure>
                                @endforeach
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>

        <aside class="space-y-5">
            <section class="border border-[#dce5e1] bg-white p-5 sm:p-6">
                <h2 class="font-bold">Ringkasan tagihan</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-[#687671]">Subtotal</dt><dd>Rp{{ number_format((float) $invoice->subtotal, 0, ',', '.') }}</dd></div>
                    @if ((float) $invoice->discount > 0)<div class="flex justify-between gap-3"><dt class="text-[#687671]">Diskon</dt><dd>- Rp{{ number_format((float) $invoice->discount, 0, ',', '.') }}</dd></div>@endif
                    @if ((float) $invoice->tax > 0)<div class="flex justify-between gap-3"><dt class="text-[#687671]">Pajak</dt><dd>Rp{{ number_format((float) $invoice->tax, 0, ',', '.') }}</dd></div>@endif
                    <div class="flex justify-between gap-3 border-t border-[#dce5e1] pt-3 text-base font-bold"><dt>Total</dt><dd>Rp{{ number_format((float) $invoice->grand_total, 0, ',', '.') }}</dd></div>
                </dl>

                @if ($invoice->status === 'UNPAID')
                    <form id="payment-form" class="mt-5 space-y-4 border-t border-[#dce5e1] pt-5" method="POST" action="{{ route('cashier.invoices.payments.store', $invoice) }}">
                        @csrf
                        <input id="confirm-incomplete-documentation" type="hidden" name="confirm_incomplete_documentation" value="{{ old('confirm_incomplete_documentation', $paymentInput['confirm_incomplete_documentation'] ?? false) ? 1 : 0 }}">
                        <div>
                            <label class="form-label" for="method">Metode pembayaran</label>
                            <select class="form-field" id="method" name="method" required>
                                @foreach (['CASH' => 'Tunai', 'TRANSFER' => 'Transfer', 'QRIS' => 'QRIS', 'OTHER' => 'Lainnya'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('method', $paymentInput['method'] ?? 'CASH') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('method')<p class="mt-1 text-sm text-[#b54039]">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="amount">Jumlah diterima</label>
                            <input class="form-field" id="amount" name="amount" type="number" min="0.01" step="0.01" value="{{ old('amount', $paymentInput['amount'] ?? $invoice->grand_total) }}" required>
                            @error('amount')<p class="mt-1 text-sm text-[#b54039]">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="reference_number">Nomor referensi (opsional)</label>
                            <input class="form-field" id="reference_number" name="reference_number" value="{{ old('reference_number', $paymentInput['reference_number'] ?? '') }}" maxlength="100">
                        </div>
                        @error('payment')<p class="text-sm text-[#b54039]">{{ $message }}</p>@enderror
                        <button class="btn-primary w-full" type="submit">Bayar Sekarang</button>
                    </form>
                @else
                    <a class="btn-primary mt-5 block text-center text-sm" href="{{ route('cashier.invoices.receipt', $invoice) }}">Lihat struk</a>
                @endif
            </section>
        </aside>
    </div>

    @if ($needsConfirmation)
        <div class="fixed inset-0 z-30 grid place-items-center bg-[#172321]/55 p-4" role="dialog" aria-modal="true" aria-labelledby="confirmation-title">
            <section class="w-full max-w-lg border border-[#dce5e1] bg-white p-6 shadow-xl">
                <h2 id="confirmation-title" class="text-lg font-bold">Dokumentasi belum lengkap</h2>
                <p class="mt-2 text-sm text-[#687671]">Pembayaran tetap dapat diproses. Konfirmasi ini akan dicatat pada audit transaksi.</p>
                <ul class="mt-4 space-y-2 text-sm">
                    @foreach ($confirmationItems as $item)
                        <li class="flex gap-2"><span class="font-bold text-[#b54039]">!</span><span>{{ $item['type'] === 'service' ? 'Jasa' : 'Sparepart' }}: {{ $item['name'] }}</span></li>
                    @endforeach
                </ul>
                <div class="mt-6 flex flex-wrap justify-end gap-3">
                    <a class="btn-secondary text-sm" href="{{ route('cashier.invoices.show', $invoice) }}">Kembali</a>
                    <button class="btn-primary text-sm" id="confirm-payment" type="button">Tetap Lanjut</button>
                </div>
            </section>
        </div>
    @endif
    <script>
        document.querySelector('#confirm-payment')?.addEventListener('click', () => {
            document.querySelector('#confirm-incomplete-documentation').value = '1';
            document.querySelector('#payment-form').requestSubmit();
        });
    </script>
@endsection