<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Documentation;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Pkb;
use App\Models\PkbService;
use App\Models\PkbSparepart;
use App\Models\Reminder;
use App\Models\ServiceHistory;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

/** Coordinates invoice checklist, payment, service history, reminder, and receipt data. */
class PaymentService
{
    public function __construct(private NumberGeneratorService $numberGenerator) {}

    /** @return array{documentation_complete: bool, items: array<int, array<string, mixed>>, missing_items: array<int, array<string, mixed>>} */
    public function checklist(Pkb $pkb): array
    {
        $pkb->loadMissing([
            'services.documentations',
            'spareparts.documentations',
        ]);
        $items = collect();

        foreach ($pkb->services as $service) {
            $items->push($this->checklistItem($service, 'service'));
        }

        foreach ($pkb->spareparts as $sparepart) {
            $items->push($this->checklistItem($sparepart, 'sparepart'));
        }

        $missingItems = $items
            ->filter(fn (array $item): bool => $item['documentation_required'] && ! $item['complete'])
            ->values()
            ->all();

        return [
            'documentation_complete' => $this->documentationComplete($pkb->id),
            'items' => $items->values()->all(),
            'missing_items' => $missingItems,
        ];
    }

    public function ensureInvoice(Pkb $pkb): Invoice
    {
        return DB::transaction(function () use ($pkb): Invoice {
            $lockedPkb = Pkb::query()->lockForUpdate()->findOrFail($pkb->id);

            if ($lockedPkb->status !== 'WAITING_PAYMENT') {
                throw ValidationException::withMessages([
                    'status' => 'Invoice hanya tersedia untuk PKB yang menunggu pembayaran.',
                ]);
            }

            $invoice = Invoice::query()->where('pkb_id', $lockedPkb->id)->lockForUpdate()->first();

            if ($invoice !== null) {
                return $invoice;
            }

            $total = (float) $lockedPkb->final_total;

            return Invoice::create([
                'number' => $this->numberGenerator->next('INVOICE'),
                'pkb_id' => $lockedPkb->id,
                'subtotal' => $total,
                'discount' => 0,
                'tax' => 0,
                'grand_total' => $total,
                'status' => 'UNPAID',
                'issued_at' => now(),
            ]);
        });
    }

    /** @param array{method: string, amount: int|float|string, reference_number?: string|null, confirm_incomplete_documentation?: bool} $data
     *  @return array{needs_confirmation: bool, message: string, errors?: array<string, array<int, string>>, missing_items?: array<int, array<string, mixed>>, payment?: Payment, invoice?: Invoice, pkb?: Pkb, documentation_complete?: bool}
     */
    public function processPayment(Invoice $invoice, array $data, User $receiver, ?string $ipAddress = null, ?string $userAgent = null): array
    {
        return DB::transaction(function () use ($invoice, $data, $receiver, $ipAddress, $userAgent): array {
            $lockedInvoice = Invoice::query()->lockForUpdate()->findOrFail($invoice->id);

            if ($lockedInvoice->status !== 'UNPAID') {
                throw ValidationException::withMessages([
                    'invoice' => 'Invoice ini tidak berstatus belum lunas.',
                ]);
            }

            $pkb = Pkb::query()->lockForUpdate()->findOrFail($lockedInvoice->pkb_id);

            if ($pkb->status !== 'WAITING_PAYMENT') {
                throw ValidationException::withMessages([
                    'pkb' => 'PKB ini tidak menunggu pembayaran.',
                ]);
            }

            $amount = round((float) $data['amount'], 2);
            $grandTotal = round((float) $lockedInvoice->grand_total, 2);

            if (abs($amount - $grandTotal) > 0.001) {
                throw ValidationException::withMessages([
                    'amount' => 'Jumlah pembayaran harus sama dengan total invoice.',
                ]);
            }

            $checklist = $this->checklist($pkb);
            $confirmedIncomplete = (bool) ($data['confirm_incomplete_documentation'] ?? false);

            if (! $checklist['documentation_complete'] && ! $confirmedIncomplete) {
                return [
                    'needs_confirmation' => true,
                    'message' => 'Dokumentasi belum lengkap. Konfirmasi untuk tetap lanjut memproses pembayaran.',
                    'errors' => [
                        'documentation' => array_map(
                            fn (array $item): string => $this->missingItemMessage($item),
                            $checklist['missing_items'],
                        ),
                    ],
                    'missing_items' => $checklist['missing_items'],
                ];
            }

            if (! $checklist['documentation_complete'] && $confirmedIncomplete) {
                ActivityLog::create([
                    'user_id' => $receiver->id,
                    'action' => 'PAYMENT_WITHOUT_COMPLETE_DOCUMENTATION',
                    'entity_type' => Invoice::class,
                    'entity_id' => $lockedInvoice->id,
                    'new_values' => [
                        'pkb_id' => $pkb->id,
                        'missing_documentation' => $checklist['missing_items'],
                        'confirmed' => true,
                    ],
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent,
                    'created_at' => now(),
                ]);
            }

            $payment = Payment::create([
                'invoice_id' => $lockedInvoice->id,
                'method' => $data['method'],
                'amount' => $amount,
                'reference_number' => $data['reference_number'] ?? null,
                'paid_at' => now(),
                'received_by' => $receiver->id,
            ]);

            $this->setCurrentUser($receiver);

            try {
                $lockedInvoice->update(['status' => 'PAID', 'paid_at' => now()]);
                $pkb->update(['status' => 'PAID']);
            } finally {
                $this->clearCurrentUser();
            }

            $pkb->loadMissing(['customer', 'vehicle', 'services', 'spareparts']);
            $serviceHistorySummary = trim($pkb->complaint."\n".($pkb->inspection_result ?? ''));
            ServiceHistory::create([
                'pkb_id' => $pkb->id,
                'vehicle_id' => $pkb->vehicle_id,
                'service_date' => $pkb->service_date,
                'km' => $pkb->vehicle->current_km,
                'summary' => $serviceHistorySummary,
                'total_amount' => $lockedInvoice->grand_total,
                'created_at' => now(),
            ]);

            $reminderDate = $pkb->service_date->copy()->addMonthsNoOverflow(6)->toDateString();
            Reminder::create([
                'pkb_id' => $pkb->id,
                'vehicle_id' => $pkb->vehicle_id,
                'customer_id' => $pkb->customer_id,
                'reminder_date' => $reminderDate,
                'type' => 'SERVICE_6_MONTHS',
                'status' => 'PENDING',
            ]);

            return [
                'needs_confirmation' => false,
                'message' => 'Pembayaran berhasil.',
                'payment' => $payment,
                'invoice' => $lockedInvoice->load('pkb.customer', 'pkb.vehicle'),
                'pkb' => $pkb->load('customer', 'vehicle'),
                'documentation_complete' => $checklist['documentation_complete'],
            ];
        });
    }

    /** @return array<string, mixed> */
    public function receiptData(Invoice $invoice): array
    {
        $invoice->loadMissing([
            'pkb.customer',
            'pkb.vehicle',
            'pkb.services',
            'pkb.spareparts',
            'payments.receiver',
        ]);
        $pkb = $invoice->pkb;
        $latestPayment = $invoice->payments->sortByDesc('paid_at')->first();
        $phone = $this->whatsappPhone($pkb->customer->phone);
        $message = sprintf(
            'Halo %s, pembayaran servis kendaraan %s untuk PKB %s telah kami terima. Invoice %s sebesar Rp%s. Terima kasih telah mempercayakan servis kepada TEFA Automotive.',
            $pkb->customer->name,
            $pkb->vehicle->plate_number,
            $pkb->number,
            $invoice->number,
            number_format((float) $invoice->grand_total, 0, ',', '.'),
        );

        return [
            'invoice' => [
                'number' => $invoice->number,
                'status' => $invoice->status,
                'issued_at' => $invoice->issued_at?->format('d/m/Y H:i'),
                'paid_at' => $invoice->paid_at?->format('d/m/Y H:i'),
                'subtotal' => $invoice->subtotal,
                'discount' => $invoice->discount,
                'tax' => $invoice->tax,
                'grand_total' => $invoice->grand_total,
            ],
            'pkb' => [
                'number' => $pkb->number,
                'service_date' => $pkb->service_date?->format('d/m/Y H:i'),
                'complaint' => $pkb->complaint,
            ],
            'customer' => $pkb->customer->only(['name', 'phone']),
            'vehicle' => $pkb->vehicle->only(['plate_number', 'brand', 'model', 'current_km']),
            'items' => $pkb->services->map(fn (PkbService $item): array => [
                'type' => 'Jasa',
                'name' => $item->name_snapshot,
                'qty' => $item->qty,
                'subtotal' => $item->subtotal,
            ])->concat($pkb->spareparts->map(fn (PkbSparepart $item): array => [
                'type' => 'Sparepart',
                'name' => $item->name_snapshot,
                'qty' => $item->qty_used ?? $item->qty_planned,
                'subtotal' => $item->subtotal,
            ]))->values()->all(),
            'payment' => $latestPayment === null ? null : [
                'method' => $latestPayment->method,
                'amount' => $latestPayment->amount,
                'received_by' => $latestPayment->receiver?->name,
            ],
            'whatsapp_url' => $phone === '' ? null : 'https://wa.me/'.$phone.'?text='.rawurlencode($message),
            'whatsapp_message' => $message,
        ];
    }

    /** @return array<string, mixed> */
    private function checklistItem(PkbService|PkbSparepart $item, string $type): array
    {
        $documents = $item->documentations->map(fn (Documentation $documentation): array => [
            'id' => $documentation->id,
            'file_name' => $documentation->file_name,
            'file_path' => $documentation->file_path,
            'url' => \Illuminate\Support\Facades\Storage::disk('public')->url($documentation->file_path),
            'mime_type' => $documentation->mime_type,
        ])->values()->all();
        $required = (bool) $item->documentation_required;

        return [
            'type' => $type,
            'id' => $item->id,
            'name' => $item->name_snapshot,
            'documentation_required' => $required,
            'complete' => ! $required || $documents !== [],
            'documents' => $documents,
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function missingDocumentationItems(Pkb $pkb): array
    {
        $services = $pkb->services()
            ->where('documentation_required', true)
            ->whereDoesntHave('documentations')
            ->get()
            ->map(fn (PkbService $item): array => [
                'type' => 'service',
                'item_id' => $item->id,
                'name' => $item->name_snapshot,
            ]);
        $spareparts = $pkb->spareparts()
            ->where('documentation_required', true)
            ->whereDoesntHave('documentations')
            ->get()
            ->map(fn (PkbSparepart $item): array => [
                'type' => 'sparepart',
                'item_id' => $item->id,
                'name' => $item->name_snapshot,
            ]);

        return $services->concat($spareparts)->values()->all();
    }

    private function documentationComplete(int $pkbId): bool
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            return (bool) DB::selectOne('SELECT fn_documentation_complete(?) AS complete', [$pkbId])->complete;
        }

        $missingServices = PkbService::query()
            ->where('pkb_id', $pkbId)
            ->where('documentation_required', true)
            ->whereDoesntHave('documentations')
            ->exists();
        $missingSpareparts = PkbSparepart::query()
            ->where('pkb_id', $pkbId)
            ->where('documentation_required', true)
            ->whereDoesntHave('documentations')
            ->exists();

        return ! $missingServices && ! $missingSpareparts;
    }

    /** @param array<string, mixed> $item */
    private function missingItemMessage(array $item): string
    {
        $label = $item['type'] === 'service' ? 'Jasa' : 'Sparepart';

        return $label.' '.$item['name'].' belum memiliki foto dokumentasi.';
    }

    private function whatsappPhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone ?? '') ?? '';

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        return $digits;
    }

    private function setCurrentUser(User $actor): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('SET @current_user_id = ?', [$actor->id]);
        }
    }

    private function clearCurrentUser(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('SET @current_user_id = NULL');
        }
    }
}
