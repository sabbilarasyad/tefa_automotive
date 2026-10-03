<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Reminder;
use App\Models\ServiceHistory;
use App\Models\Sparepart;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockServiceHistoryReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_access_stock_page(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();

        $this->actingAs($superAdmin)
            ->get(route('admin.stock.index'))
            ->assertOk()
            ->assertSee('Daftar Stok');
    }

    public function test_admin_cs_can_access_stock_page(): void
    {
        $this->seed();
        $adminCs = User::where('username', 'admincs')->firstOrFail();

        $this->actingAs($adminCs)
            ->get(route('admin.stock.index'))
            ->assertOk()
            ->assertSee('Daftar Stok');
    }

    public function test_kasir_cannot_access_stock_page(): void
    {
        $this->seed();
        $cashier = User::where('username', 'kasir')->firstOrFail();

        $this->actingAs($cashier)
            ->get(route('admin.stock.index'))
            ->assertForbidden();
    }

    public function test_mekanik_cannot_access_stock_page(): void
    {
        $this->seed();
        $mechanic = User::where('username', 'mekanik')->firstOrFail();

        $this->actingAs($mechanic)
            ->get(route('admin.stock.index'))
            ->assertForbidden();
    }

    public function test_stock_page_shows_low_stock_filter(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();

        // Create a low stock sparepart
        Sparepart::create([
            'code' => 'LOW-STOCK',
            'name' => 'Sparepart Menipis',
            'unit' => 'pcs',
            'selling_price' => 10000,
            'cost_price' => 5000,
            'stock_qty' => 2,
            'min_stock' => 5,
            'documentation_required' => false,
            'is_active' => true,
        ]);

        $this->actingAs($superAdmin)
            ->get(route('admin.stock.index', ['filter' => 'low']))
            ->assertOk()
            ->assertSee('Sparepart Menipis');
    }

    public function test_stock_restock_requires_reason(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();
        $sparepart = Sparepart::create([
            'code' => 'TEST-PART',
            'name' => 'Test Part',
            'unit' => 'pcs',
            'selling_price' => 10000,
            'cost_price' => 5000,
            'stock_qty' => 10,
            'min_stock' => 5,
            'documentation_required' => false,
            'is_active' => true,
        ]);

        $this->actingAs($superAdmin)
            ->post(route('admin.stock.restock', $sparepart), [
                'qty' => 5,
                'reason' => '',
            ])
            ->assertSessionHasErrors('reason');

        $this->actingAs($superAdmin)
            ->post(route('admin.stock.restock', $sparepart), [
                'qty' => 5,
                'reason' => 'Pembelian PO-123',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $sparepart->refresh();
        $this->assertEquals(15, (float) $sparepart->stock_qty);
    }

    public function test_stock_history_shows_movements(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();
        $sparepart = Sparepart::create([
            'code' => 'HIST-PART',
            'name' => 'History Part',
            'unit' => 'pcs',
            'selling_price' => 10000,
            'cost_price' => 5000,
            'stock_qty' => 10,
            'min_stock' => 5,
            'documentation_required' => false,
            'is_active' => true,
        ]);

        $this->actingAs($superAdmin)
            ->get(route('admin.stock.history', $sparepart))
            ->assertOk()
            ->assertSee('Riwayat Pergerakan Stok');
    }

    public function test_super_admin_can_access_service_history_page(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();

        $this->actingAs($superAdmin)
            ->get(route('admin.service-history.index'))
            ->assertOk()
            ->assertSee('Riwayat Servis per Kendaraan');
    }

    public function test_admin_cs_can_access_service_history_page(): void
    {
        $this->seed();
        $adminCs = User::where('username', 'admincs')->firstOrFail();

        $this->actingAs($adminCs)
            ->get(route('admin.service-history.index'))
            ->assertOk()
            ->assertSee('Riwayat Servis per Kendaraan');
    }

    public function test_kasir_can_access_service_history_page(): void
    {
        $this->seed();
        $cashier = User::where('username', 'kasir')->firstOrFail();

        $this->actingAs($cashier)
            ->get(route('admin.service-history.index'))
            ->assertOk()
            ->assertSee('Riwayat Servis per Kendaraan');
    }

    public function test_mekanik_can_access_service_history_page(): void
    {
        $this->seed();
        $mechanic = User::where('username', 'mekanik')->firstOrFail();

        $this->actingAs($mechanic)
            ->get(route('admin.service-history.index'))
            ->assertOk()
            ->assertSee('Riwayat Servis per Kendaraan');
    }

    public function test_service_history_search_returns_results(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();

        // Create a vehicle with service history
        $admin = User::where('username', 'admincs')->firstOrFail();
        $customer = Customer::create(['name' => 'Test Customer', 'phone' => '081234567890']);
        $vehicle = Vehicle::create([
            'customer_id' => $customer->id,
            'plate_number' => 'B 1234 TEST',
            'brand' => 'Toyota',
            'model' => 'Avanza',
            'current_km' => 50000,
        ]);
        $pkb = \App\Models\Pkb::create([
            'number' => 'PKB/2026/10/9999',
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'created_by' => $admin->id,
            'mechanic_id' => $admin->id,
            'complaint' => 'Test',
            'inspection_result' => 'Test',
            'status' => 'PAID',
            'service_date' => '2026-04-02 09:30:00',
            'completed_at' => '2026-04-02 10:30:00',
            'final_total' => 100000,
        ]);
        ServiceHistory::create([
            'pkb_id' => $pkb->id,
            'vehicle_id' => $vehicle->id,
            'service_date' => '2026-04-02 09:30:00',
            'km' => 50000,
            'summary' => 'Test service',
            'total_amount' => 100000,
            'created_at' => now(),
        ]);

        $this->actingAs($superAdmin)
            ->get(route('admin.service-history.index', ['search' => 'B 1234 TEST']))
            ->assertOk()
            ->assertSee('B 1234 TEST')
            ->assertSee('Toyota Avanza');
    }

    public function test_super_admin_can_access_reminder_page(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();

        $this->actingAs($superAdmin)
            ->get(route('admin.reminder.index'))
            ->assertOk()
            ->assertSee('Daftar Reminder Jatuh Tempo');
    }

    public function test_admin_cs_can_access_reminder_page(): void
    {
        $this->seed();
        $adminCs = User::where('username', 'admincs')->firstOrFail();

        $this->actingAs($adminCs)
            ->get(route('admin.reminder.index'))
            ->assertOk()
            ->assertSee('Daftar Reminder Jatuh Tempo');
    }

    public function test_kasir_can_access_reminder_page(): void
    {
        $this->seed();
        $cashier = User::where('username', 'kasir')->firstOrFail();

        $this->actingAs($cashier)
            ->get(route('admin.reminder.index'))
            ->assertOk()
            ->assertSee('Daftar Reminder Jatuh Tempo');
    }

    public function test_mekanik_cannot_access_reminder_page(): void
    {
        $this->seed();
        $mechanic = User::where('username', 'mekanik')->firstOrFail();

        $this->actingAs($mechanic)
            ->get(route('admin.reminder.index'))
            ->assertForbidden();
    }

    public function test_reminder_page_shows_due_reminders(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();

        // Create a due reminder
        $admin = User::where('username', 'admincs')->firstOrFail();
        $customer = Customer::create(['name' => 'Reminder Customer', 'phone' => '081234567890']);
        $vehicle = Vehicle::create([
            'customer_id' => $customer->id,
            'plate_number' => 'B 9999 REM',
            'brand' => 'Honda',
            'model' => 'Brio',
            'current_km' => 30000,
        ]);
        $pkb = \App\Models\Pkb::create([
            'number' => 'PKB/2026/10/8888',
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'created_by' => $admin->id,
            'mechanic_id' => $admin->id,
            'complaint' => 'Test',
            'inspection_result' => 'Test',
            'status' => 'PAID',
            'service_date' => '2026-04-02 09:30:00',
            'completed_at' => '2026-04-02 10:30:00',
            'final_total' => 100000,
        ]);
        Reminder::create([
            'pkb_id' => $pkb->id,
            'vehicle_id' => $vehicle->id,
            'customer_id' => $customer->id,
            'reminder_date' => now()->subDays(1)->toDateString(), // past due
            'type' => 'SERVICE_6_MONTHS',
            'status' => 'PENDING',
        ]);

        $this->actingAs($superAdmin)
            ->get(route('admin.reminder.index'))
            ->assertOk()
            ->assertSee('B 9999 REM')
            ->assertSee('Reminder Customer');
    }

    public function test_reminder_send_marks_sent_and_opens_whatsapp(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();

        $admin = User::where('username', 'admincs')->firstOrFail();
        $customer = Customer::create(['name' => 'Send Test', 'phone' => '081234567890']);
        $vehicle = Vehicle::create([
            'customer_id' => $customer->id,
            'plate_number' => 'B 7777 SND',
            'brand' => 'Suzuki',
            'model' => 'Ertiga',
            'current_km' => 20000,
        ]);
        $pkb = \App\Models\Pkb::create([
            'number' => 'PKB/2026/10/7777',
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'created_by' => $admin->id,
            'mechanic_id' => $admin->id,
            'complaint' => 'Test',
            'inspection_result' => 'Test',
            'status' => 'PAID',
            'service_date' => '2026-04-02 09:30:00',
            'completed_at' => '2026-04-02 10:30:00',
            'final_total' => 100000,
        ]);
        $reminder = Reminder::create([
            'pkb_id' => $pkb->id,
            'vehicle_id' => $vehicle->id,
            'customer_id' => $customer->id,
            'reminder_date' => now()->subDays(1)->toDateString(),
            'type' => 'SERVICE_6_MONTHS',
            'status' => 'PENDING',
        ]);

        $this->actingAs($superAdmin)
            ->post(route('admin.reminder.send', $reminder))
            ->assertRedirect(); // redirects to wa.me

        $reminder->refresh();
        $this->assertEquals('SENT', $reminder->status);
        $this->assertNotNull($reminder->sent_at);
        $this->assertNotNull($reminder->message);
    }

    public function test_reminder_route_is_not_404(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();

        // The route should be /admin/reminder (singular) not /admin/reminders
        $this->actingAs($superAdmin)
            ->get('/admin/reminder')
            ->assertOk()
            ->assertSee('Daftar Reminder Jatuh Tempo');
    }

    public function test_stock_route_is_not_404_for_super_admin(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();

        $this->actingAs($superAdmin)
            ->get('/admin/stock')
            ->assertOk()
            ->assertSee('Daftar Stok');
    }

    public function test_service_history_route_is_not_404_for_super_admin(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();

        $this->actingAs($superAdmin)
            ->get('/admin/service-history')
            ->assertOk()
            ->assertSee('Riwayat Servis per Kendaraan');
    }
}