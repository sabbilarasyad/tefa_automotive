<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProcessPaymentRequest;
use App\Models\Invoice;
use App\Models\Pkb;
use App\Services\PaymentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CashierController extends Controller
{
    public function __construct(private PaymentService $payments) {}

    public function index(): View
    {
        $this->authorize('viewAny', Invoice::class);

        $pkbs = Pkb::query()
            ->with(['customer', 'vehicle', 'invoice'])
            ->where('status', 'WAITING_PAYMENT')
            ->whereHas('invoice', fn ($query) => $query->where('status', 'UNPAID'))
            ->orderBy('completed_at')
            ->orderBy('id')
            ->paginate(15);

        return view('cashier.index', compact('pkbs'));
    }

    public function show(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);

        return $this->invoiceView($invoice);
    }

    public function processPayment(ProcessPaymentRequest $request, Invoice $invoice): View|RedirectResponse|Response
    {
        $result = $this->payments->processPayment(
            $invoice,
            $request->validated(),
            $request->user(),
            $request->ip(),
            $request->userAgent(),
        );

        if ($result['needs_confirmation']) {
            return response()->view(
                'cashier.invoices.show',
                $this->invoiceViewData($invoice, $request->validated(), $result['missing_items'] ?? [], true),
                422,
            );
        }

        return redirect()
            ->route('cashier.invoices.receipt', $invoice)
            ->with('success', 'Pembayaran berhasil diproses.');
    }

    public function receipt(Invoice $invoice): View
    {
        $this->authorize('print', $invoice);

        abort_unless($invoice->status === 'PAID', 404);

        return view('cashier.receipt', $this->payments->receiptData($invoice));
    }

    public function pdf(Invoice $invoice): Response
    {
        $this->authorize('print', $invoice);
        abort_unless($invoice->status === 'PAID', 404);

        $pdf = Pdf::loadView('cashier.receipt-pdf', $this->payments->receiptData($invoice))
            ->setPaper([0, 0, 226.77, 720], 'portrait');

        $downloadName = 'struk-'.str_replace('/', '-', $invoice->number).'.pdf';

        return $pdf->download($downloadName);
    }

    /** @param array<string, mixed> $paymentInput
     *  @param array<int, array<string, mixed>> $confirmationItems
     */
    private function invoiceView(Invoice $invoice, array $paymentInput = [], array $confirmationItems = [], bool $needsConfirmation = false): View
    {
        return view('cashier.invoices.show', $this->invoiceViewData($invoice, $paymentInput, $confirmationItems, $needsConfirmation));
    }

    /** @param array<string, mixed> $paymentInput
     *  @param array<int, array<string, mixed>> $confirmationItems
     *  @return array<string, mixed>
     */
    private function invoiceViewData(Invoice $invoice, array $paymentInput = [], array $confirmationItems = [], bool $needsConfirmation = false): array
    {
        $invoice->load(['pkb.customer', 'pkb.vehicle']);

        return [
            'invoice' => $invoice,
            'checklist' => $this->payments->checklist($invoice->pkb),
            'paymentInput' => $paymentInput,
            'confirmationItems' => $confirmationItems,
            'needsConfirmation' => $needsConfirmation,
        ];
    }
}
