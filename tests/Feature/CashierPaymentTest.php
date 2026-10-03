<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Documentation;
use App\Models\Invoice;
use App\Models\Pkb;
use App\Models\PkbService;
use App\Models\PkbSparepart;
use App\Models\Service as WorkshopService;
use App\Models\ServiceHistory;
use App\Models\Sparepart;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashierPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_incomplete_documentation_returns_needs_confirmation_without_side_effects(): void
    {
        $this->seed();
        $fixture = $this->fixture(documentationRequired: true);
        $token = $fixture['cashier']->createToken('payment-test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/invoices/'.$fixture['invoice']->id.'/payments', $this->paymentData())
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('needs_confirmation', true)
            ->assertJsonPath('message', 'Dokumentasi belum lengkap. Konfirmasi untuk tetap lanjut memproses pembayaran.')
            ->assertJsonPath('errors.documentation.0', 'Sparepart Filter Oli belum memiliki foto dokumentasi.');

        $this->assertDatabaseHas('invoices', ['id' => $fixture['invoice']->id, 'status' => 'UNPAID']);
        $this->assertDatabaseHas('pkb', ['id' => $fixture['pkb']->id, 'status' => 'WAITING_PAYMENT']);
        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseCount('service_histories', 0);
        $this->assertDatabaseCount('reminders', 0);
        $this->assertDatabaseMissing('activity_logs', ['action' => 'PAYMENT_WITHOUT_COMPLETE_DOCUMENTATION']);
    }

    public function test_web_payment_shows_soft_block_dialog_and_processes_after_cashier_confirms(): void
    {
        $this->seed();
        $fixture = $this->fixture(documentationRequired: true);

        $this->actingAs($fixture['cashier'])
            ->post(route('cashier.invoices.payments.store', $fixture['invoice']), $this->paymentData())
            ->assertUnprocessable()
            ->assertSee('aria-modal="true"', false)
            ->assertSee('Dokumentasi belum lengkap')
            ->assertSee('Tetap Lanjut')
            ->assertSee('Bayar Sekarang');

        $this->assertDatabaseHas('invoices', ['id' => $fixture['invoice']->id, 'status' => 'UNPAID']);

        $this->actingAs($fixture['cashier'])
            ->post(route('cashier.invoices.payments.store', $fixture['invoice']), $this->paymentData(confirmed: true))
            ->assertRedirect(route('cashier.invoices.receipt', $fixture['invoice']));

        $this->assertDatabaseHas('invoices', ['id' => $fixture['invoice']->id, 'status' => 'PAID']);
        $this->assertDatabaseHas('activity_logs', ['action' => 'PAYMENT_WITHOUT_COMPLETE_DOCUMENTATION']);
    }

    public function test_confirmed_incomplete_documentation_payment_records_audit_history_and_reminder(): void
    {
        $this->seed();
        $this->travelTo('2026-10-02 12:00:00');
        $fixture = $this->fixture(documentationRequired: true);
        $token = $fixture['cashier']->createToken('payment-confirm-test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/invoices/'.$fixture['invoice']->id.'/payments', $this->paymentData(confirmed: true))
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.invoice.status', 'PAID')
            ->assertJsonPath('data.pkb.status', 'PAID')
            ->assertJsonPath('data.payment.amount', '250000.00');

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $fixture['invoice']->id,
            'method' => 'CASH',
            'amount' => 250000,
            'received_by' => $fixture['cashier']->id,
        ]);
        $this->assertDatabaseHas('service_histories', [
            'pkb_id' => $fixture['pkb']->id,
            'vehicle_id' => $fixture['vehicle']->id,
            'service_date' => '2026-04-02 09:30:00',
            'km' => 42800,
            'total_amount' => 250000,
        ]);
        $this->assertDatabaseHas('reminders', [
            'pkb_id' => $fixture['pkb']->id,
            'reminder_date' => '2026-10-02 00:00:00',
            'status' => 'PENDING',
        ]);

        $audit = ActivityLog::where('action', 'PAYMENT_WITHOUT_COMPLETE_DOCUMENTATION')->firstOrFail();
        $this->assertSame($fixture['cashier']->id, $audit->user_id);
        $this->assertSame($fixture['invoice']->id, $audit->entity_id);
        $this->assertSame('Filter Oli', $audit->new_values['missing_documentation'][0]['name']);
    }

    public function test_complete_documentation_payment_succeeds_without_confirmation_audit(): void
    {
        $this->seed();
        $fixture = $this->fixture(documentationRequired: true, includeDocumentation: true);
        $token = $fixture['cashier']->createToken('complete-doc-payment')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/invoices/'.$fixture['invoice']->id.'/payments', $this->paymentData())
            ->assertCreated()
            ->assertJsonPath('data.invoice.status', 'PAID')
            ->assertJsonPath('data.pkb.status', 'PAID');

        $this->assertDatabaseMissing('activity_logs', ['action' => 'PAYMENT_WITHOUT_COMPLETE_DOCUMENTATION']);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_admin_and_mechanic_are_forbidden_from_processing_payments(): void
    {
        $this->seed();
        $fixture = $this->fixture();

        foreach ([$fixture['admin'], $fixture['mechanic']] as $user) {
            $this->withToken($user->createToken('forbidden-payment')->plainTextToken)
                ->postJson('/api/invoices/'.$fixture['invoice']->id.'/payments', $this->paymentData())
                ->assertForbidden()
                ->assertJsonPath('success', false);
        }

        $this->assertDatabaseHas('invoices', ['id' => $fixture['invoice']->id, 'status' => 'UNPAID']);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_cashier_can_view_item_checklist_and_invoice_receipt_endpoints(): void
    {
        $this->seed();
        $fixture = $this->fixture(documentationRequired: true);
        $token = $fixture['cashier']->createToken('checklist-test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/pkb/'.$fixture['pkb']->id.'/checklist')
            ->assertOk()
            ->assertJsonPath('data.documentation_complete', false)
            ->assertJsonPath('data.items.1.name', 'Filter Oli')
            ->assertJsonPath('data.items.1.complete', false);

        $this->withToken($token)
            ->getJson('/api/invoices/'.$fixture['invoice']->id)
            ->assertOk()
            ->assertJsonPath('data.invoice.number', 'INV/2026/10/0001')
            ->assertJsonPath('data.customer.name', 'Customer Kasir');

        $this->withToken($token)
            ->getJson('/api/invoices/'.$fixture['invoice']->id.'/print')
            ->assertOk()
            ->assertJsonPath('data.invoice.number', 'INV/2026/10/0001')
            ->assertJsonPath('data.customer.phone', '081234567890');
    }

    public function test_receipt_has_print_download_and_prepopulated_whatsapp_link(): void
    {
        $this->seed();
        $fixture = $this->fixture();
        $fixture['invoice']->update(['status' => 'PAID', 'paid_at' => now()]);
        $fixture['pkb']->update(['status' => 'PAID']);
        $fixture['invoice']->payments()->create([
            'method' => 'CASH',
            'amount' => 250000,
            'paid_at' => now(),
            'received_by' => $fixture['cashier']->id,
        ]);

        $this->actingAs($fixture['cashier'])
            ->get(route('cashier.invoices.receipt', $fixture['invoice']))
            ->assertOk()
            ->assertSee('Cetak Struk')
            ->assertSee('Unduh PDF')
            ->assertSee('Kirim ke WhatsApp')
            ->assertSee('wa.me/6281234567890', false)
            ->assertSee('INV/2026/10/0001');

        $this->actingAs($fixture['cashier'])
            ->get(route('cashier.invoices.pdf', $fixture['invoice']))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    private function fixture(bool $documentationRequired = false, bool $includeDocumentation = false): array
    {
        $admin = User::where('username', 'admincs')->firstOrFail();
        $cashier = User::where('username', 'kasir')->firstOrFail();
        $mechanic = User::where('username', 'mekanik')->firstOrFail();
        $customer = Customer::create(['name' => 'Customer Kasir', 'phone' => '081234567890']);
        $vehicle = Vehicle::create([
            'customer_id' => $customer->id,
            'plate_number' => 'B 5678 KSR',
            'brand' => 'Honda',
            'model' => 'Brio',
            'current_km' => 42800,
        ]);
        $service = WorkshopService::create([
            'code' => 'CASHIER-SRV',
            'name' => 'Servis Berkala',
            'price' => 200000,
            'documentation_required' => false,
            'is_active' => true,
        ]);
        $sparepart = Sparepart::create([
            'code' => 'CASHIER-PART',
            'name' => 'Filter Oli',
            'selling_price' => 50000,
            'stock_qty' => 10,
            'unit' => 'pcs',
            'documentation_required' => $documentationRequired,
            'is_active' => true,
        ]);
        $pkb = Pkb::create([
            'number' => 'PKB/2026/10/0001',
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'created_by' => $admin->id,
            'mechanic_id' => $mechanic->id,
            'complaint' => 'Servis berkala kendaraan.',
            'inspection_result' => 'Kondisi normal.',
            'status' => 'WAITING_PAYMENT',
            'service_date' => '2026-04-02 09:30:00',
            'completed_at' => '2026-04-02 10:30:00',
            'final_total' => 250000,
        ]);
        PkbService::create([
            'pkb_id' => $pkb->id,
            'service_id' => $service->id,
            'name_snapshot' => $service->name,
            'qty' => 1,
            'unit_price' => 200000,
            'subtotal' => 200000,
        ]);
        $pkbSparepart = PkbSparepart::create([
            'pkb_id' => $pkb->id,
            'sparepart_id' => $sparepart->id,
            'name_snapshot' => $sparepart->name,
            'qty_planned' => 1,
            'qty_used' => 1,
            'unit_price' => 50000,
            'subtotal' => 50000,
            'documentation_required' => $documentationRequired,
        ]);
        $invoice = Invoice::create([
            'number' => 'INV/2026/10/0001',
            'pkb_id' => $pkb->id,
            'subtotal' => 250000,
            'discount' => 0,
            'tax' => 0,
            'grand_total' => 250000,
            'status' => 'UNPAID',
            'issued_at' => '2026-04-02 10:30:00',
        ]);

        if ($includeDocumentation) {
            Documentation::create([
                'pkb_id' => $pkb->id,
                'pkb_sparepart_id' => $pkbSparepart->id,
                'file_path' => 'documentations/filter-oli.jpg',
                'file_name' => 'filter-oli.jpg',
                'mime_type' => 'image/jpeg',
                'file_size' => 100,
                'uploaded_by' => $admin->id,
                'uploaded_at' => now(),
            ]);
        }

        return compact('admin', 'cashier', 'mechanic', 'customer', 'vehicle', 'service', 'sparepart', 'pkb', 'pkbSparepart', 'invoice');
    }

    private function paymentData(bool $confirmed = false): array
    {
        return [
            'method' => 'CASH',
            'amount' => 250000,
            'reference_number' => null,
            'confirm_incomplete_documentation' => $confirmed,
        ];
    }
}