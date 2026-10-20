<?php

namespace Tests\Verify;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Pkb;
use App\Models\PkbService;
use App\Models\PkbSparepart;
use App\Models\Reminder;
use App\Models\Service;
use App\Models\ServiceHistory;
use App\Models\Sparepart;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifyEndToEndTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_end_to_end_pkb_flow_with_complete_documentation(): void
    {
        $this->seed();
        $admin = User::where('username', 'admincs')->firstOrFail();
        $mechanic = User::where('username', 'mekanik')->firstOrFail();
        $cashier = User::where('username', 'kasir')->firstOrFail();

        // 1. Admin buat PKB (stok cukup)
        $customer = Customer::create(['name' => 'Test Customer', 'phone' => '081234567890']);
        $vehicle = Vehicle::create([
            'customer_id' => $customer->id,
            'plate_number' => 'B 1234 ABC',
            'brand' => 'Honda',
            'model' => 'Civic',
            'current_km' => 50000,
        ]);
        $service = Service::create([
            'code' => 'SRV-001',
            'name' => 'Servis Berkala',
            'price' => 200000,
            'documentation_required' => true,
            'is_active' => true,
        ]);
        $sparepart = Sparepart::create([
            'code' => 'PRT-001',
            'name' => 'Oli Mesin',
            'unit' => 'pcs',
            'selling_price' => 150000,
            'cost_price' => 100000,
            'stock_qty' => 10,
            'min_stock' => 2,
            'documentation_required' => true,
            'is_active' => true,
        ]);

        $pkb = Pkb::create([
            'number' => 'PKB/2026/10/0001',
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'created_by' => $admin->id,
            'mechanic_id' => $mechanic->id,
            'complaint' => 'Servis berkala',
            'inspection_result' => 'Normal',
            'status' => 'WAITING_MECHANIC',
            'service_date' => now(),
            'final_total' => 350000,
        ]);
        PkbService::create([
            'pkb_id' => $pkb->id,
            'service_id' => $service->id,
            'name_snapshot' => $service->name,
            'qty' => 1,
            'unit_price' => 200000,
            'subtotal' => 200000,
            'documentation_required' => true,
        ]);
        PkbSparepart::create([
            'pkb_id' => $pkb->id,
            'sparepart_id' => $sparepart->id,
            'name_snapshot' => $sparepart->name,
            'qty_planned' => 1,
            'qty_used' => 1,
            'unit_price' => 150000,
            'subtotal' => 150000,
            'documentation_required' => true,
        ]);

        $sparepart->refresh();
        $this->assertEquals(10, (float)$sparepart->stock_qty);

        // 2. Assign mekanik (already assigned)
        // 3. Cetak PKB
        $this->actingAs($admin)
            ->get(route('admin.pkb.print', $pkb))
            ->assertOk();

        // 4. Kasir mulai kerja
        $this->actingAs($cashier)
            ->post(route('admin.pkb.progress.start', $pkb))
            ->assertRedirect();
        $pkb->refresh();
        $this->assertEquals('IN_PROGRESS', $pkb->status);

        // 4b. Koreksi qty_used (already set)
        // 5. Upload foto wajib
        // Simulate upload by creating documentation records
        $pkbService = $pkb->services->first();
        $pkbSparepart = $pkb->spareparts->first();
        \App\Models\Documentation::create([
            'pkb_id' => $pkb->id,
            'pkb_service_id' => $pkbService->id,
            'file_path' => 'documentations/service.jpg',
            'file_name' => 'service.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 100,
            'uploaded_by' => $mechanic->id,
            'uploaded_at' => now(),
        ]);
        \App\Models\Documentation::create([
            'pkb_id' => $pkb->id,
            'pkb_sparepart_id' => $pkbSparepart->id,
            'file_path' => 'documentations/sparepart.jpg',
            'file_name' => 'sparepart.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 100,
            'uploaded_by' => $mechanic->id,
            'uploaded_at' => now(),
        ]);

        // 6. Validasi selesai (stok berkurang sesuai qty_used)
        $sparepartBefore = Sparepart::find($sparepart->id);
        $stockBefore = (float)$sparepartBefore->stock_qty;

        $this->actingAs($cashier)
            ->post(route('admin.pkb.progress.complete', $pkb))
            ->assertRedirect();

        $pkb->refresh();
        $this->assertEquals('WAITING_PAYMENT', $pkb->status);

        $sparepartAfter = Sparepart::find($sparepart->id);
        $stockAfter = (float)$sparepartAfter->stock_qty;
        // Stok berkurang 1 (qty_used = 1)
        $this->assertEquals($stockBefore - 1, $stockAfter);

        // 7. Invoice WAITING_PAYMENT
        $invoice = $pkb->invoice;
        $this->assertNotNull($invoice);
        $this->assertEquals('UNPAID', $invoice->status);
        $this->assertEquals('WAITING_PAYMENT', $pkb->status);

        // 8. Bayar - variasi A: dokumentasi lengkap
        $this->actingAs($cashier)
            ->post(route('cashier.invoices.payments.store', $invoice), [
                'method' => 'CASH',
                'amount' => 350000,
                'reference_number' => 'REF-001',
            ])
            ->assertRedirect(route('cashier.invoices.receipt', $invoice));

        $invoice->refresh();
        $this->assertEquals('PAID', $invoice->status);
        $this->assertEquals('PAID', $pkb->fresh()->status);

        // Check service_histories terbentuk
        $this->assertDatabaseHas('service_histories', [
            'pkb_id' => $pkb->id,
            'vehicle_id' => $vehicle->id,
        ]);

        // Check reminders +6 bulan terbentuk
        $this->assertDatabaseHas('reminders', [
            'pkb_id' => $pkb->id,
            'vehicle_id' => $vehicle->id,
            'status' => 'PENDING',
        ]);
    }

    /** @test */
    public function test_end_to_end_pkb_flow_with_incomplete_documentation(): void
    {
        $this->seed();
        $admin = User::where('username', 'admincs')->firstOrFail();
        $mechanic = User::where('username', 'mekanik')->firstOrFail();
        $cashier = User::where('username', 'kasir')->firstOrFail();

        $customer = Customer::create(['name' => 'Test Customer 2', 'phone' => '081234567891']);
        $vehicle = Vehicle::create([
            'customer_id' => $customer->id,
            'plate_number' => 'B 5678 DEF',
            'brand' => 'Toyota',
            'model' => 'Avanza',
            'current_km' => 30000,
        ]);
        $service = Service::create([
            'code' => 'SRV-002',
            'name' => 'Ganti Oli',
            'price' => 150000,
            'documentation_required' => true,
            'is_active' => true,
        ]);
        $sparepart = Sparepart::create([
            'code' => 'PRT-002',
            'name' => 'Filter Oli',
            'unit' => 'pcs',
            'selling_price' => 50000,
            'cost_price' => 30000,
            'stock_qty' => 5,
            'min_stock' => 1,
            'documentation_required' => true,
            'is_active' => true,
        ]);

        $pkb = Pkb::create([
            'number' => 'PKB/2026/10/0002',
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'created_by' => $admin->id,
            'mechanic_id' => $mechanic->id,
            'complaint' => 'Ganti oli',
            'inspection_result' => 'Oli hitam',
            'status' => 'WAITING_MECHANIC',
            'service_date' => now(),
            'final_total' => 200000,
        ]);
        PkbService::create([
            'pkb_id' => $pkb->id,
            'service_id' => $service->id,
            'name_snapshot' => $service->name,
            'qty' => 1,
            'unit_price' => 150000,
            'subtotal' => 150000,
            'documentation_required' => true,
        ]);
        PkbSparepart::create([
            'pkb_id' => $pkb->id,
            'sparepart_id' => $sparepart->id,
            'name_snapshot' => $sparepart->name,
            'qty_planned' => 1,
            'qty_used' => 1,
            'unit_price' => 50000,
            'subtotal' => 50000,
            'documentation_required' => true,
        ]);

        // Skip foto upload - dokumentasi tidak lengkap
        $this->actingAs($cashier)
            ->post(route('admin.pkb.progress.start', $pkb))
            ->assertRedirect();

        $this->actingAs($cashier)
            ->post(route('admin.pkb.progress.complete', $pkb))
            ->assertRedirect();

        $pkb->refresh();
        $this->assertEquals('WAITING_PAYMENT', $pkb->status);

        $invoice = $pkb->fresh()->invoice;
        $this->assertNotNull($invoice);
        $this->assertEquals('UNPAID', $invoice->status);

        // Bayar variasi B: dokumentasi tidak lengkap tanpa konfirmasi -> needs_confirmation
        $response = $this->actingAs($cashier)
            ->post(route('cashier.invoices.payments.store', $pkb->invoice), [
                'method' => 'CASH',
                'amount' => 200000,
                'reference_number' => 'REF-002',
            ]);

        // Should show needs_confirmation (422)
        $response->assertUnprocessable()
            ->assertSee('Dokumentasi belum lengkap')
            ->assertSee('Tetap Lanjut');

        // Check activity_logs NOT created yet (no confirmation)
        $this->assertDatabaseMissing('activity_logs', [
            'action' => 'PAYMENT_WITHOUT_COMPLETE_DOCUMENTATION',
        ]);

        // Lalu dengan konfirmasi -> sukses
        $this->actingAs($cashier)
            ->post(route('cashier.invoices.payments.store', $pkb->invoice), [
                'method' => 'CASH',
                'amount' => 200000,
                'reference_number' => 'REF-002',
                'confirm_incomplete_documentation' => true,
            ])
            ->assertRedirect(route('cashier.invoices.receipt', $pkb->invoice));

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'PAYMENT_WITHOUT_COMPLETE_DOCUMENTATION',
            'entity_id' => $pkb->invoice->id,
        ]);

        // Check service_histories dan reminders +6 bulan
        $this->assertDatabaseHas('service_histories', [
            'pkb_id' => $pkb->id,
        ]);
        $this->assertDatabaseHas('reminders', [
            'pkb_id' => $pkb->id,
            'status' => 'PENDING',
        ]);
    }
}