@extends('layouts.app')

@section('title', 'Kasir')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="mb-1 text-xs font-bold uppercase tracking-[0.12em] text-[#126b56]">Kasir · Pembayaran</p>
            <h1 class="text-2xl font-bold">Menunggu Pembayaran</h1>
        </div>
        <span class="text-sm text-[#687671]">{{ $pkbs->total() }} invoice</span>
    </div>

    <section class="overflow-hidden border border-[#dce5e1] bg-white">
        @if ($pkbs->isEmpty())
            <p class="px-5 py-14 text-center text-sm text-[#687671]">Belum ada PKB yang menunggu pembayaran.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead class="bg-[#f3f7f5] text-xs uppercase text-[#687671]"><tr><th class="px-5 py-3">Invoice / PKB</th><th class="px-5 py-3">Customer / kendaraan</th><th class="px-5 py-3">Tanggal servis</th><th class="px-5 py-3 text-right">Total</th><th class="px-5 py-3"></th></tr></thead>
                    <tbody class="divide-y divide-[#e7eeea]">
                        @foreach ($pkbs as $pkb)
                            <tr class="hover:bg-[#f9fbfa]">
                                <td class="px-5 py-4"><a class="font-bold text-[#126b56] hover:underline" href="{{ route('cashier.invoices.show', $pkb->invoice) }}">{{ $pkb->invoice->number }}</a><p class="mt-1 text-xs text-[#687671]">{{ $pkb->number }}</p></td>
                                <td class="px-5 py-4"><p class="font-semibold">{{ $pkb->customer->name }}</p><p class="text-xs text-[#687671]">{{ $pkb->vehicle->plate_number }} · {{ $pkb->vehicle->brand }} {{ $pkb->vehicle->model }}</p></td>
                                <td class="px-5 py-4">{{ $pkb->service_date->format('d M Y') }}</td>
                                <td class="px-5 py-4 text-right font-semibold">Rp{{ number_format((float) $pkb->invoice->grand_total, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-right"><a class="font-semibold text-[#126b56]" href="{{ route('cashier.invoices.show', $pkb->invoice) }}">Checklist & Bayar →</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-[#dce5e1] px-5 py-4">{{ $pkbs->links() }}</div>
        @endif
    </section>
@endsection