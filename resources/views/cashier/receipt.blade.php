<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Struk {{ $invoice['number'] }} · TEFA Automotive</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            .receipt { width: min(100%, 380px); margin-inline: auto; }
            @media print {
                @page { size: 80mm auto; margin: 3mm; }
                body { background: white !important; }
                .receipt-actions { display: none !important; }
                .receipt { width: 72mm; max-width: 72mm; margin: 0; color: #111; font: 11px/1.4 ui-monospace, monospace; }
            }
        </style>
    </head>
    <body class="min-h-screen bg-[#f4f7f5] px-4 py-8 text-[#172321]">
        <div class="receipt-actions mx-auto mb-5 flex w-full max-w-[380px] flex-wrap justify-between gap-2">
            <a class="btn-secondary text-sm" href="{{ route('cashier.index') }}">← Kasir</a>
            <div class="flex gap-2">
                <a class="btn-secondary text-sm" href="{{ route('cashier.invoices.pdf', ['invoice' => request()->route('invoice')]) }}">Unduh PDF</a>
                <button class="btn-primary text-sm" type="button" onclick="window.print()">Cetak Struk</button>
            </div>
        </div>
        <main class="receipt border border-[#dce5e1] bg-white p-6 shadow-sm print:border-0 print:p-0 print:shadow-none">
            <header class="border-b border-dashed border-[#9ba8a2] pb-4 text-center">
                <h1 class="text-lg font-bold tracking-normal">TEFA Automotive</h1>
                <p class="text-xs text-[#687671] print:text-black">Bukti pembayaran servis kendaraan</p>
            </header>
            <section class="space-y-1 border-b border-dashed border-[#9ba8a2] py-4 text-xs">
                <p><strong>Invoice:</strong> {{ $invoice['number'] }}</p>
                <p><strong>PKB:</strong> {{ $pkb['number'] }}</p>
                <p><strong>Tanggal:</strong> {{ $invoice['paid_at'] }}</p>
                <p><strong>Customer:</strong> {{ $customer['name'] }}</p>
                <p><strong>Kendaraan:</strong> {{ $vehicle['plate_number'] }} · {{ $vehicle['brand'] }} {{ $vehicle['model'] }}</p>
            </section>
            <section class="border-b border-dashed border-[#9ba8a2] py-3">
                <div class="mb-2 grid grid-cols-[1fr_auto] gap-2 text-xs font-bold"><span>Item</span><span>Jumlah</span></div>
                @foreach ($items as $item)
                    <div class="grid grid-cols-[1fr_auto] gap-2 py-1 text-xs">
                        <span>{{ $item['name'] }}<br><span class="text-[#687671] print:text-black">{{ $item['type'] }} × {{ $item['qty'] }}</span></span>
                        <span>Rp{{ number_format((float) $item['subtotal'], 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </section>
            <section class="space-y-1 border-b border-dashed border-[#9ba8a2] py-3 text-xs">
                <div class="flex justify-between"><span>Subtotal</span><span>Rp{{ number_format((float) $invoice['subtotal'], 0, ',', '.') }}</span></div>
                @if ((float) $invoice['discount'] > 0)<div class="flex justify-between"><span>Diskon</span><span>- Rp{{ number_format((float) $invoice['discount'], 0, ',', '.') }}</span></div>@endif
                @if ((float) $invoice['tax'] > 0)<div class="flex justify-between"><span>Pajak</span><span>Rp{{ number_format((float) $invoice['tax'], 0, ',', '.') }}</span></div>@endif
                <div class="flex justify-between border-t border-[#dce5e1] pt-2 text-sm font-bold"><span>Total</span><span>Rp{{ number_format((float) $invoice['grand_total'], 0, ',', '.') }}</span></div>
                <p class="pt-1">Metode: {{ $payment['method'] ?? '-' }}</p>
            </section>
            <footer class="pt-4 text-center text-xs">
                <p>Terima kasih telah mempercayakan servis kendaraan Anda kepada kami.</p>
                @if ($whatsapp_url)
                    <a class="btn-primary mt-4 inline-block text-sm receipt-actions" href="{{ $whatsapp_url }}" target="_blank" rel="noopener">Kirim ke WhatsApp</a>
                @endif
            </footer>
        </main>
    </body>
</html>