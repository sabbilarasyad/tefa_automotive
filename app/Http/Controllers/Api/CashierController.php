<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProcessPaymentRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Invoice;
use App\Models\Pkb;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CashierController extends Controller
{
    public function __construct(private PaymentService $payments) {}

    public function checklist(Pkb $pkb): JsonResponse
    {
        $this->authorize('viewChecklist', $pkb);

        return ApiResponse::success($this->payments->checklist($pkb), 'Checklist dokumentasi berhasil diambil.');
    }

    public function generateInvoice(Pkb $pkb): JsonResponse
    {
        $this->authorize('generateInvoice', $pkb);
        $invoice = $this->payments->ensureInvoice($pkb);

        return ApiResponse::success($invoice->load('pkb.customer', 'pkb.vehicle'), 'Invoice berhasil diambil atau dibuat.');
    }

    public function show(Invoice $invoice): JsonResponse
    {
        $this->authorize('view', $invoice);

        return ApiResponse::success($this->payments->receiptData($invoice), 'Detail invoice berhasil diambil.');
    }

    public function processPayment(ProcessPaymentRequest $request, Invoice $invoice): JsonResponse
    {
        $result = $this->payments->processPayment(
            $invoice,
            $request->validated(),
            $request->user(),
            $request->ip(),
            $request->userAgent(),
        );

        if ($result['needs_confirmation']) {
            return ApiResponse::error(
                $result['message'],
                $result['errors'] ?? [],
                422,
                ['needs_confirmation' => true],
            );
        }

        return ApiResponse::success($result, 'Pembayaran berhasil diproses.', 201);
    }

    public function print(Invoice $invoice): JsonResponse
    {
        $this->authorize('print', $invoice);

        return ApiResponse::success($this->payments->receiptData($invoice), 'Data struk berhasil diambil.');
    }
}
