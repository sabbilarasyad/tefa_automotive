<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <style>
            @page { size: A4; margin: 15mm; }
            * { box-sizing: border-box; }
            body { margin: 0; color: #111; font: 11px/1.5 DejaVu Sans, sans-serif; }
            h1 { margin: 0 0 4px; font-size: 20px; letter-spacing: 0; }
            p { margin: 4px 0; }
            .center { text-align: center; }
            .muted { color: #555; }
            .rule { border-top: 1px dashed #555; margin: 10px 0; }
            table { width: 100%; border-collapse: collapse; }
            td, th { padding: 5px 0; vertical-align: top; }
            .right { text-align: right; white-space: nowrap; }
            .total { border-top: 1px solid #111; font-weight: bold; font-size: 13px; }
            .section-title { font-weight: bold; font-size: 12px; margin: 12px 0 6px; border-bottom: 1px solid #ddd; padding-bottom: 3px; }
        </style>
    </head>
    <body>
        <header class="center mb-6">
            <h1>TEFA Automotive</h1>
            <p>Bukti Pembayaran Servis Kendaraan</p>
        </header>
        <div class="rule"></div>
        <table>
            <tr><td style="width: 40%;"><strong>Invoice:</strong></td><td>{{ $invoice['number'] }}</td></tr>
            <tr><td><strong>PKB:</strong></td><td>{{ $pkb['number'] }}</td></tr>
            <tr><td><strong>Tanggal Bayar:</strong></td><td>{{ $invoice['paid_at'] }}</td></tr>
            <tr><td><strong>Customer:</strong></td><td>{{ $customer['name'] }}</td></tr>
            <tr><td><strong>Telepon:</strong></td><td>{{ $customer['phone'] }}</td></tr>
            <tr><td><strong>Kendaraan:</strong></td><td>{{ $vehicle['plate_number'] }} · {{ $vehicle['brand'] }} {{ $vehicle['model'] }}</td></tr>
            @if ($vehicle['current_km'])
                <tr><td><strong>KM:</strong></td><td>{{ number_format($vehicle['current_km'], 0, ',', '.') }}</td></tr>
            @endif
        </table>
        <div class="rule"></div>

        <div class="section-title">Jasa</div>
        <table>
            @foreach ($items as $item)
                @if ($item['type'] === 'Jasa')
                    <tr><td>{{ $item['name'] }}<br><span class="muted">{{ $item['type'] }} × {{ $item['qty'] }}</span></td><td class="right">Rp{{ number_format((float) $item['subtotal'], 0, ',', '.') }}</td></tr>
                @endif
            @endforeach
        </table>

        <div class="section-title">Sparepart</div>
        <table>
            @foreach ($items as $item)
                @if ($item['type'] === 'Sparepart')
                    <tr><td>{{ $item['name'] }}<br><span class="muted">{{ $item['type'] }} × {{ $item['qty'] }}</span></td><td class="right">Rp{{ number_format((float) $item['subtotal'], 0, ',', '.') }}</td></tr>
                @endif
            @endforeach
        </table>

        <div class="rule"></div>
        <table>
            <tr><td>Subtotal</td><td class="right">Rp{{ number_format((float) $invoice['subtotal'], 0, ',', '.') }}</td></tr>
            @if ((float) $invoice['discount'] > 0)<tr><td>Diskon</td><td class="right">- Rp{{ number_format((float) $invoice['discount'], 0, ',', '.') }}</td></tr>@endif
            @if ((float) $invoice['tax'] > 0)<tr><td>Pajak</td><td class="right">Rp{{ number_format((float) $invoice['tax'], 0, ',', '.') }}</td></tr>@endif
            <tr class="total"><td>Total</td><td class="right">Rp{{ number_format((float) $invoice['grand_total'], 0, ',', '.') }}</td></tr>
        </table>
        <p style="margin-top: 8px;"><strong>Metode:</strong> {{ $payment['method'] ?? '-' }}</p>
        @if ($payment['received_by'])
            <p><strong>Diterima oleh:</strong> {{ $payment['received_by'] }}</p>
        @endif
        <div class="rule"></div>
        <p class="center">Terima kasih telah mempercayakan servis kendaraan Anda kepada kami.</p>
        <p class="center muted" style="font-size: 9px; margin-top: 20px;">TEFA Automotive · Dicetak pada {{ now()->format('d/m/Y H:i') }}</p>
    </body>
</html>