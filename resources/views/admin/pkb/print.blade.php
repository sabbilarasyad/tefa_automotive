@extends('layouts.print')

@section('content')
    <header class="top">
        <div><h1>TEFA Automotive</h1><p>Perintah Kerja Bengkel</p><p>Dokumen kerja mekanik</p></div>
        <div class="number"><p>Nomor PKB</p><p>{{ $document['number'] }}</p><p>{{ $document['service_date'] }}</p></div>
    </header>

    <h2>Customer & kendaraan</h2>
    <section class="grid">
        <div class="block"><p><strong>Nama:</strong> {{ $document['customer']['name'] }}</p><p><strong>Telepon:</strong> {{ $document['customer']['phone'] }}</p><p><strong>Alamat:</strong> {{ $document['customer']['address'] ?: '-' }}</p></div>
        <div class="block"><p><strong>Nomor polisi:</strong> {{ $document['vehicle']['plate_number'] }}</p><p><strong>Kendaraan:</strong> {{ $document['vehicle']['brand'] }} {{ $document['vehicle']['model'] }} {{ $document['vehicle']['year'] }}</p><p><strong>No. Rangka:</strong> {{ $document['vehicle']['vin'] ?? '-' }}</p><p><strong>No. Mesin:</strong> {{ $document['vehicle']['engine_number'] ?? '-' }}</p><p><strong>KM:</strong> {{ number_format($document['vehicle']['current_km'] ?? 0, 0, ',', '.') }}</p></div>
    </section>

    <h2>Keluhan customer</h2>
    <div class="block"><p>{{ $document['complaint'] }}</p></div>
    <h2>Hasil pemeriksaan awal</h2>
    <div class="block"><p>{{ $document['inspection_result'] ?: 'Belum dicatat' }}</p></div>

    <h2>Rencana jasa</h2>
    <table>
        <thead><tr><th>Nama jasa</th><th class="right">Qty</th><th class="right">Harga</th><th class="right">Subtotal</th></tr></thead>
        <tbody>
            @forelse ($document['services'] as $service)
                <tr><td>{{ $service['name'] }}</td><td class="right">{{ $service['qty'] }}</td><td class="right">Rp{{ number_format((float) $service['unit_price'], 0, ',', '.') }}</td><td class="right">Rp{{ number_format((float) $service['subtotal'], 0, ',', '.') }}</td></tr>
            @empty
                <tr><td colspan="4">Tidak ada jasa.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Rencana sparepart</h2>
    <table>
        <thead><tr><th>Nama sparepart</th><th class="right">Qty</th><th class="right">Harga</th><th class="right">Subtotal</th></tr></thead>
        <tbody>
            @forelse ($document['spareparts'] as $part)
                <tr><td>{{ $part['name'] }}</td><td class="right">{{ $part['qty_planned'] }}</td><td class="right">Rp{{ number_format((float) $part['unit_price'], 0, ',', '.') }}</td><td class="right">Rp{{ number_format((float) $part['subtotal'], 0, ',', '.') }}</td></tr>
            @empty
                <tr><td colspan="4">Tidak ada sparepart.</td></tr>
            @endforelse
        </tbody>
    </table>
    <p class="right" style="margin-top: 8px"><strong>Estimasi: Rp{{ number_format((float) $document['estimated_total'], 0, ',', '.') }}</strong></p>

    <h2>Mekanik bertugas</h2>
    <div class="block"><p>{{ $document['mechanic']['name'] ?? '-' }}</p></div>
    <h2>Catatan mekanik / part aktual</h2>
    <div class="notes"></div>
@endsection