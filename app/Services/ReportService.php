<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Pkb;
use App\Models\PkbSparepart;
use App\Models\Reminder;
use App\Models\ServiceHistory;
use App\Models\Sparepart;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /** @return array{pkb_per_status: array<string, int>, waiting_parts: int, low_stock: int, due_reminders: int, today_revenue: float} */
    public function dashboardData(): array
    {
        $today = now()->startOfDay();
        $tomorrow = $today->copy()->addDay();

        $pkbPerStatus = Pkb::query()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $waitingParts = Pkb::query()
            ->where('status', 'WAITING_PARTS')
            ->count();

        $lowStock = Sparepart::query()
            ->where('is_active', true)
            ->whereRaw('stock_qty <= min_stock')
            ->count();

        $dueReminders = Reminder::query()
            ->where('status', 'PENDING')
            ->whereDate('reminder_date', '<=', now()->toDateString())
            ->count();

        $todayRevenue = Payment::query()
            ->whereBetween('paid_at', [$today, $tomorrow])
            ->sum('amount');

        return [
            'pkb_per_status' => $pkbPerStatus,
            'waiting_parts' => $waitingParts,
            'low_stock' => $lowStock,
            'due_reminders' => $dueReminders,
            'today_revenue' => (float) $todayRevenue,
        ];
    }

    /** @return array{pkb_count: int, transaction_count: int, total_revenue: float, by_method: array<string, float>, details: array<int, array<string, mixed>>} */
    public function dailyRecap(Carbon $date): array
    {
        $start = $date->copy()->startOfDay();
        $end = $date->copy()->endOfDay();

        $payments = Payment::query()
            ->whereBetween('paid_at', [$start, $end])
            ->with(['invoice.pkb.customer', 'invoice.pkb.vehicle', 'receiver'])
            ->get();

        $byMethod = $payments->groupBy('method')
            ->map(fn (Collection $items) => (float) $items->sum('amount'))
            ->toArray();

        $details = $payments->map(fn (Payment $p): array => [
            'time' => $p->paid_at->format('H:i'),
            'invoice' => $p->invoice->number ?? '-',
            'pkb' => $p->invoice->pkb->number ?? '-',
            'customer' => $p->invoice->pkb->customer->name ?? '-',
            'vehicle' => $p->invoice->pkb->vehicle->plate_number ?? '-',
            'method' => $p->method,
            'amount' => (float) $p->amount,
            'received_by' => $p->receiver->name ?? '-',
        ])->values()->all();

        return [
            'pkb_count' => $payments->count(),
            'transaction_count' => $payments->count(),
            'total_revenue' => (float) $payments->sum('amount'),
            'by_method' => $byMethod,
            'details' => $details,
        ];
    }

    /** @return array{total_services: int, total_revenue: float, top_spareparts: array<int, array<string, mixed>>} */
    public function monthlyRecap(int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $totalServices = Pkb::query()
            ->whereBetween('completed_at', [$start, $end])
            ->where('status', 'PAID')
            ->count();

        $totalRevenue = Payment::query()
            ->whereBetween('paid_at', [$start, $end])
            ->sum('amount');

        $topSpareparts = PkbSparepart::query()
            ->whereHas('pkb', function ($q) use ($start, $end) {
                $q->whereBetween('completed_at', [$start, $end])
                    ->where('status', 'PAID');
            })
            ->select('sparepart_id', DB::raw('SUM(qty_used) as total_qty'))
            ->groupBy('sparepart_id')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->with('sparepart')
            ->get()
            ->map(fn ($item): array => [
                'name' => $item->sparepart->name ?? '-',
                'code' => $item->sparepart->code ?? '-',
                'unit' => $item->sparepart->unit ?? '-',
                'total_qty' => (float) $item->total_qty,
            ])
            ->values()
            ->all();

        return [
            'total_services' => $totalServices,
            'total_revenue' => (float) $totalRevenue,
            'top_spareparts' => $topSpareparts,
        ];
    }

    /** @return array{total_spareparts: int, low_stock_count: int, items: array<int, array<string, mixed>>} */
    public function stockReport(): array
    {
        $items = Sparepart::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Sparepart $s): array => [
                'id' => $s->id,
                'code' => $s->code,
                'name' => $s->name,
                'unit' => $s->unit,
                'stock_qty' => (float) $s->stock_qty,
                'min_stock' => (float) $s->min_stock,
                'selling_price' => (float) $s->selling_price,
                'is_low' => (float) $s->stock_qty <= (float) $s->min_stock,
            ])
            ->values()
            ->all();

        return [
            'total_spareparts' => count($items),
            'low_stock_count' => collect($items)->where('is_low', true)->count(),
            'items' => $items,
        ];
    }

    /** @return array{total: int, items: array<int, array<string, mixed>>} */
    public function documentationReport(?int $mechanicId = null, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $query = Pkb::query()
            ->with(['mechanic', 'services', 'spareparts'])
            ->where(fn ($q) => $q->whereNotNull('mechanic_id')->orWhereNotNull('mechanic_name'));

        if ($mechanicId) {
            $query->where('mechanic_id', $mechanicId);
        }

        if ($from) {
            $query->whereDate('service_date', '>=', $from);
        }

        if ($to) {
            $query->whereDate('service_date', '<=', $to);
        }

        $pkbs = $query->get();

        $items = $pkbs->map(function (Pkb $pkb): array {
            $services = $pkb->services;
            $spareparts = $pkb->spareparts;

            $requiredItems = $services->filter(fn ($s) => $s->documentation_required)
                ->concat($spareparts->filter(fn ($s) => $s->documentation_required))
                ->values();

            $completedItems = $requiredItems->filter(function ($item) {
                return $item->documentations->isNotEmpty();
            });

            return [
                'pkb_number' => $pkb->number,
                'mechanic' => $pkb->mechanic_display_name ?? '-',
                'service_date' => $pkb->service_date->format('d/m/Y'),
                'total_required' => $requiredItems->count(),
                'completed' => $completedItems->count(),
                'pending' => $requiredItems->count() - $completedItems->count(),
            ];
        })->values()->all();

        return [
            'total' => count($items),
            'items' => $items,
        ];
    }

    /** @return array{confirmed_incomplete_payments: array<int, array<string, mixed>>} */
    public function incompleteDocumentationPayments(?Carbon $from = null, ?Carbon $to = null): array
    {
        $query = ActivityLog::query()
            ->where('action', 'PAYMENT_WITHOUT_COMPLETE_DOCUMENTATION')
            ->orderByDesc('created_at');

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        $logs = $query->get();

        return [
            'confirmed_incomplete_payments' => $logs->map(fn (ActivityLog $log): array => [
                'id' => $log->id,
                'user' => $log->user->name ?? '-',
                'invoice_id' => $log->entity_id,
                'pkb_id' => $log->new_values['pkb_id'] ?? '-',
                'missing_items' => collect($log->new_values['missing_documentation'] ?? [])
                    ->pluck('name')
                    ->implode(', '),
                'created_at' => $log->created_at->format('d/m/Y H:i'),
            ])->values()->all(),
        ];
    }

    /** @return array{scheduled: int, sent: int, overdue: int, items: array<int, array<string, mixed>>} */
    public function reminderReport(?Carbon $from = null, ?Carbon $to = null): array
    {
        $query = Reminder::query()
            ->with(['pkb.customer', 'pkb.vehicle', 'vehicle.customer']);

        if ($from) {
            $query->whereDate('reminder_date', '>=', $from);
        }

        if ($to) {
            $query->whereDate('reminder_date', '<=', $to);
        }

        $reminders = $query->get();

        $today = now()->toDateString();

        $items = $reminders->map(fn (Reminder $r): array => [
            'id' => $r->id,
            'pkb' => $r->pkb->number ?? '-',
            'customer' => $r->customer->name ?? $r->vehicle->customer->name ?? '-',
            'vehicle' => $r->vehicle->plate_number ?? '-',
            'reminder_date' => $r->reminder_date->format('d/m/Y'),
            'type' => $r->type,
            'status' => $r->status,
            'sent_at' => $r->sent_at?->format('d/m/Y H:i'),
            'is_overdue' => $r->status === 'PENDING' && $r->reminder_date < $today,
        ])->values()->all();

        return [
            'scheduled' => $reminders->where('status', 'PENDING')->count(),
            'sent' => $reminders->where('status', 'SENT')->count(),
            'overdue' => $reminders->where('status', 'PENDING')->where('reminder_date', '<', $today)->count(),
            'items' => $items,
        ];
    }

    /** @return array{filename: string, headers: array<string>, rows: array<int, array<string>>} */
    public function exportCsv(string $reportType, array $data): array
    {
        $headers = [];
        $rows = [];

        switch ($reportType) {
            case 'daily':
                $headers = ['Waktu', 'Invoice', 'PKB', 'Customer', 'Kendaraan', 'Metode', 'Jumlah', 'Diterima Oleh'];
                foreach ($data['details'] as $d) {
                    $rows[] = [$d['time'], $d['invoice'], $d['pkb'], $d['customer'], $d['vehicle'], $d['method'], number_format($d['amount'], 0, ',', '.'), $d['received_by']];
                }
                break;
            case 'monthly':
                $headers = ['Nama Sparepart', 'Kode', 'Satuan', 'Total Qty Terpakai'];
                foreach ($data['top_spareparts'] as $s) {
                    $rows[] = [$s['name'], $s['code'], $s['unit'], number_format($s['total_qty'], 0, ',', '.')];
                }
                break;
            case 'stock':
                $headers = ['Kode', 'Nama', 'Satuan', 'Stok', 'Min Stok', 'Harga Jual', 'Status'];
                foreach ($data['items'] as $s) {
                    $rows[] = [$s['code'], $s['name'], $s['unit'], number_format($s['stock_qty'], 0, ',', '.'), number_format($s['min_stock'], 0, ',', '.'), number_format($s['selling_price'], 0, ',', '.'), $s['is_low'] ? 'MENIPIS' : 'OK'];
                }
                break;
            case 'documentation':
                $headers = ['PKB', 'Mekanik', 'Tgl Servis', 'Total Wajib', 'Lengkap', 'Belum Lengkap'];
                foreach ($data['items'] as $d) {
                    $rows[] = [$d['pkb_number'], $d['mechanic'], $d['service_date'], $d['total_required'], $d['completed'], $d['pending']];
                }
                break;
            case 'reminder':
                $headers = ['PKB', 'Customer', 'Kendaraan', 'Tgl Jatuh Tempo', 'Tipe', 'Status', 'Tgl Kirim', 'Terlambat'];
                foreach ($data['items'] as $r) {
                    $rows[] = [$r['pkb'], $r['customer'], $r['vehicle'], $r['reminder_date'], $r['type'], $r['status'], $r['sent_at'] ?? '-', $r['is_overdue'] ? 'Ya' : 'Tidak'];
                }
                break;
            case 'payments_incomplete':
                $headers = ['ID Log', 'User', 'Invoice ID', 'PKB', 'Item Kurang', 'Waktu'];
                foreach ($data['confirmed_incomplete_payments'] as $p) {
                    $rows[] = [$p['id'], $p['user'], $p['invoice_id'], $p['pkb_id'], $p['missing_items'], $p['created_at']];
                }
                break;
        }

        $filename = $reportType . '_' . now()->format('Ymd_His') . '.csv';

        return compact('filename', 'headers', 'rows');
    }
}