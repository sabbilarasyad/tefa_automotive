<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Documentation;
use App\Models\Pkb;
use App\Models\PkbService;
use App\Models\PkbSparepart;
use App\Models\Service as WorkshopService;
use App\Models\Sparepart;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkProgressTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_start_update_actual_usage_and_complete_any_pkb(): void
    {
        $this->seed();
        $fixture = $this->fixture(stock: 10);
        $token = $fixture['admin']->createToken('progress-test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/pkb/'.$fixture['pkb']->id.'/start')
            ->assertOk()
            ->assertJsonPath('data.status', 'IN_PROGRESS');

        $this->withToken($token)
            ->patchJson('/api/pkb/'.$fixture['pkb']->id.'/spareparts', [
                'spareparts' => [[
                    'pkb_sparepart_id' => $fixture['pkbSparepart']->id,
                    'qty_used' => 1.25,
                ]],
            ])
            ->assertOk()
            ->assertJsonPath('data.spareparts.0.qty_used', '1.250');

        $this->assertDatabaseHas('spareparts', ['id' => $fixture['sparepart']->id, 'stock_qty' => 10]);
        $this->assertDatabaseHas('pkb_spareparts', ['id' => $fixture['pkbSparepart']->id, 'qty_used' => 1.25]);

        $this->withToken($token)->postJson('/api/pkb/'.$fixture['pkb']->id.'/complete')
            ->assertOk()
            ->assertJsonPath('data.pkb.status', 'WAITING_PAYMENT')
            ->assertJsonPath('data.documentation_complete', true);

        $this->assertDatabaseHas('spareparts', ['id' => $fixture['sparepart']->id, 'stock_qty' => 8.75]);
        $this->assertDatabaseHas('stock_movements', [
            'sparepart_id' => $fixture['sparepart']->id,
            'type' => 'OUT',
            'reference_type' => 'PKB',
            'reference_id' => $fixture['pkb']->id,
            'qty_out' => 1.25,
            'stock_before' => 10,
            'stock_after' => 8.75,
        ]);
        $this->assertDatabaseHas('pkb', ['id' => $fixture['pkb']->id, 'status' => 'WAITING_PAYMENT', 'final_total' => 262.50]);
        $this->assertDatabaseHas('invoices', ['pkb_id' => $fixture['pkb']->id, 'status' => 'UNPAID', 'grand_total' => 262.50]);
        $this->assertDatabaseHas('invoices', ['pkb_id' => $fixture['pkb']->id, 'number' => 'INV/2026/10/0001']);
        $this->assertDatabaseHas('pkb_status_histories', [
            'pkb_id' => $fixture['pkb']->id,
            'from_status' => 'IN_PROGRESS',
            'to_status' => 'WAITING_PAYMENT',
        ]);
    }

    public function test_cashier_can_upload_required_documentation_for_an_unassigned_pkb(): void
    {
        $this->seed();
        $fixture = $this->fixture(stock: 10, sparepartRequiresDocumentation: true);
        $token = $fixture['cashier']->createToken('documentation-test')->plainTextToken;
        Storage::fake('public');

        $this->withToken($token)->postJson('/api/pkb/'.$fixture['pkb']->id.'/start')->assertOk();

        $this->withToken($token)
            ->post('/api/pkb/'.$fixture['pkb']->id.'/documentations', [
                'pkb_sparepart_id' => $fixture['pkbSparepart']->id,
                'file' => UploadedFile::fake()->image('part.jpg'),
            ])
            ->assertCreated()
            ->assertJsonPath('success', true);

        $documentation = Documentation::firstOrFail();
        Storage::disk('public')->assertExists($documentation->file_path);
        $this->assertSame($fixture['pkb']->id, $documentation->pkb_id);
        $this->assertSame($fixture['cashier']->id, $documentation->uploaded_by);
    }

    public function test_missing_required_documentation_is_informational_and_does_not_block_completion(): void
    {
        $this->seed();
        $fixture = $this->fixture(stock: 10, sparepartRequiresDocumentation: true);
        $fixture['pkb']->update(['status' => 'IN_PROGRESS']);
        $fixture['pkbSparepart']->update(['qty_used' => 1]);
        $token = $fixture['cashier']->createToken('incomplete-doc-test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/pkb/'.$fixture['pkb']->id.'/complete')
            ->assertOk()
            ->assertJsonPath('data.documentation_complete', false)
            ->assertJsonPath('data.pkb.status', 'WAITING_PAYMENT');

        $this->assertDatabaseHas('invoices', ['pkb_id' => $fixture['pkb']->id, 'status' => 'UNPAID']);
    }

    public function test_documentation_can_be_deleted_before_completion_but_not_afterward(): void
    {
        $this->seed();
        $fixture = $this->fixture(stock: 10);
        $fixture['pkb']->update(['status' => 'IN_PROGRESS']);
        $documentation = Documentation::create([
            'pkb_id' => $fixture['pkb']->id,
            'pkb_sparepart_id' => $fixture['pkbSparepart']->id,
            'file_path' => 'documentations/lifecycle.jpg',
            'file_name' => 'lifecycle.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 100,
            'uploaded_by' => $fixture['admin']->id,
            'uploaded_at' => now(),
        ]);
        $token = $fixture['admin']->createToken('delete-documentation-test')->plainTextToken;
        Storage::fake('public');
        Storage::disk('public')->put($documentation->file_path, 'image-content');

        $this->withToken($token)
            ->deleteJson('/api/pkb/'.$fixture['pkb']->id.'/documentations', ['documentation_id' => $documentation->id])
            ->assertOk();

        $this->assertDatabaseMissing('documentations', ['id' => $documentation->id]);
        Storage::disk('public')->assertMissing($documentation->file_path);

        $secondDocumentation = Documentation::create([
            'pkb_id' => $fixture['pkb']->id,
            'pkb_sparepart_id' => $fixture['pkbSparepart']->id,
            'file_path' => 'documentations/keep.jpg',
            'file_name' => 'keep.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 100,
            'uploaded_by' => $fixture['admin']->id,
            'uploaded_at' => now(),
        ]);
        Storage::disk('public')->put($secondDocumentation->file_path, 'image-content');
        $fixture['pkb']->update(['status' => 'WAITING_PAYMENT']);

        $this->withToken($token)
            ->deleteJson('/api/pkb/'.$fixture['pkb']->id.'/documentations', ['documentation_id' => $secondDocumentation->id])
            ->assertUnprocessable();

        $this->assertDatabaseHas('documentations', ['id' => $secondDocumentation->id]);
        Storage::disk('public')->assertExists($secondDocumentation->file_path);
    }

    public function test_overspending_stock_rolls_back_completion_and_returns_clear_error(): void
    {
        $this->seed();
        $fixture = $this->fixture(stock: 3);
        $fixture['pkb']->update(['status' => 'IN_PROGRESS']);
        $fixture['pkbSparepart']->update(['qty_used' => 4.5]);
        $token = $fixture['admin']->createToken('overspend-test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/pkb/'.$fixture['pkb']->id.'/complete')
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('errors.stock.0', 'Stok Oli Test tidak mencukupi. Tersedia 3, dibutuhkan 4.5.');

        $this->assertDatabaseHas('spareparts', ['id' => $fixture['sparepart']->id, 'stock_qty' => 3]);
        $this->assertDatabaseHas('pkb', ['id' => $fixture['pkb']->id, 'status' => 'IN_PROGRESS']);
        $this->assertDatabaseHas('pkb_spareparts', ['id' => $fixture['pkbSparepart']->id, 'qty_used' => 4.5]);
        $this->assertDatabaseCount('stock_movements', 0);
        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_mechanic_receives_403_from_every_progress_write_api_endpoint(): void
    {
        $this->seed();
        $fixture = $this->fixture(stock: 10);
        $token = $fixture['mechanic']->createToken('forbidden-progress-test')->plainTextToken;
        $documentation = Documentation::create([
            'pkb_id' => $fixture['pkb']->id,
            'pkb_sparepart_id' => $fixture['pkbSparepart']->id,
            'file_path' => 'documentations/existing.jpg',
            'file_name' => 'existing.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 100,
            'uploaded_by' => $fixture['admin']->id,
            'uploaded_at' => now(),
        ]);

        $this->withToken($token)->postJson('/api/pkb/'.$fixture['pkb']->id.'/start')->assertForbidden();
        $this->withToken($token)->patchJson('/api/pkb/'.$fixture['pkb']->id.'/spareparts', [
            'spareparts' => [['pkb_sparepart_id' => $fixture['pkbSparepart']->id, 'qty_used' => 1]],
        ])->assertForbidden();
        $this->withToken($token)->post('/api/pkb/'.$fixture['pkb']->id.'/documentations', [
            'pkb_sparepart_id' => $fixture['pkbSparepart']->id,
            'file' => UploadedFile::fake()->image('forbidden.jpg'),
        ])->assertForbidden();
        $this->withToken($token)->deleteJson('/api/pkb/'.$fixture['pkb']->id.'/documentations', [
            'documentation_id' => $documentation->id,
        ])->assertForbidden();
        $this->withToken($token)->postJson('/api/pkb/'.$fixture['pkb']->id.'/complete')->assertForbidden();

        $this->assertDatabaseHas('pkb', ['id' => $fixture['pkb']->id, 'status' => 'WAITING_MECHANIC']);
        $this->assertDatabaseCount('stock_movements', 0);
        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_mechanic_tasks_page_scopes_active_and_history_without_progress_controls(): void
    {
        $this->seed();
        $fixture = $this->fixture(stock: 10);
        $ownHistory = $this->makePkb($fixture, $fixture['mechanic'], 'CLOSED', 'PKB/2026/10/0002');
        $otherActive = $this->makePkb($fixture, $fixture['otherMechanic'], 'IN_PROGRESS', 'PKB/2026/10/0003');

        $this->actingAs($fixture['mechanic'])->get('/mechanic/tasks')
            ->assertOk()
            ->assertSee('Tugas & Riwayat Saya')
            ->assertSee($fixture['pkb']->number)
            ->assertDontSee($ownHistory->number)
            ->assertDontSee($otherActive->number)
            ->assertDontSee('Mulai Dikerjakan')
            ->assertDontSee('Unggah Dokumentasi')
            ->assertDontSee('Validasi Selesai')
            ->assertSee('Aktif')
            ->assertSee('Riwayat');

        $this->actingAs($fixture['mechanic'])->get('/mechanic/tasks?tab=history')
            ->assertOk()
            ->assertSee($ownHistory->number)
            ->assertDontSee($fixture['pkb']->number)
            ->assertDontSee($otherActive->number)
            ->assertDontSee('Unggah Dokumentasi')
            ->assertDontSee('Validasi Selesai');

        $this->actingAs($fixture['mechanic'])->get(route('admin.pkb.show', $fixture['pkb']))
            ->assertOk()
            ->assertDontSee('Input Progres')
            ->assertDontSee('Mulai Dikerjakan')
            ->assertDontSee('Batalkan')
            ->assertDontSee('Force-cancel')
            ->assertDontSee('Cetak PKB');
    }

    public function test_mechanic_web_login_and_pkb_list_redirect_to_own_tasks(): void
    {
        $this->seed();
        $mechanic = User::where('username', 'mekanik')->firstOrFail();

        $this->post(route('login.store'), ['username' => 'mekanik', 'password' => 'password'])
            ->assertRedirect(route('mechanic.tasks'));

        $this->assertAuthenticatedAs($mechanic);
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->get(route('admin.pkb.index'))->assertRedirect(route('mechanic.tasks'));
    }

    public function test_admin_can_open_progress_page_but_mechanic_cannot(): void
    {
        $this->seed();
        $fixture = $this->fixture(stock: 10);
        $fixture['pkb']->update(['status' => 'IN_PROGRESS']);

        $this->actingAs($fixture['admin'])
            ->get(route('admin.pkb.progress.show', $fixture['pkb']))
            ->assertOk()
            ->assertSee('Input Progres Pekerjaan')
            ->assertSee('Pemakaian part aktual')
            ->assertSee('Dokumentasi pekerjaan')
            ->assertSee('Validasi Selesai');

        $this->actingAs($fixture['mechanic'])
            ->get(route('admin.pkb.progress.show', $fixture['pkb']))
            ->assertForbidden();
    }

    private function fixture(float $stock, bool $sparepartRequiresDocumentation = false): array
    {
        $admin = User::where('username', 'admincs')->firstOrFail();
        $cashier = User::where('username', 'kasir')->firstOrFail();
        $mechanic = User::where('username', 'mekanik')->firstOrFail();
        $customer = Customer::create(['name' => 'Progress Customer', 'phone' => '081234567890']);
        $vehicle = Vehicle::create([
            'customer_id' => $customer->id,
            'plate_number' => 'B 1234 PRO',
            'brand' => 'Toyota',
            'model' => 'Avanza',
        ]);
        $service = WorkshopService::create([
            'code' => 'PROGRESS-SRV',
            'name' => 'Jasa Uji',
            'price' => 200,
            'documentation_required' => false,
            'is_active' => true,
        ]);
        $sparepart = Sparepart::create([
            'code' => 'PROGRESS-PART',
            'name' => 'Oli Test',
            'selling_price' => 50,
            'stock_qty' => $stock,
            'unit' => 'liter',
            'documentation_required' => $sparepartRequiresDocumentation,
            'is_active' => true,
        ]);
        $fixture = compact('admin', 'cashier', 'mechanic', 'customer', 'vehicle', 'service', 'sparepart');
        $pkb = $this->makePkb($fixture, $mechanic, 'WAITING_MECHANIC', 'PKB/2026/10/0001');
        $pkbService = PkbService::create([
            'pkb_id' => $pkb->id,
            'service_id' => $service->id,
            'name_snapshot' => $service->name,
            'qty' => 1,
            'unit_price' => $service->price,
            'subtotal' => $service->price,
        ]);
        $pkbSparepart = PkbSparepart::create([
            'pkb_id' => $pkb->id,
            'sparepart_id' => $sparepart->id,
            'name_snapshot' => $sparepart->name,
            'qty_planned' => 4,
            'unit_price' => $sparepart->selling_price,
            'subtotal' => $sparepart->selling_price * 4,
            'documentation_required' => $sparepartRequiresDocumentation,
        ]);
        $otherMechanic = User::create([
            'role_id' => $mechanic->role_id,
            'name' => 'Mekanik Lain',
            'username' => 'mekanik-progress-2',
            'email' => 'mekanik-progress-2@tefa.local',
            'password' => 'password',
            'is_active' => true,
        ]);

        return compact('admin', 'cashier', 'mechanic', 'otherMechanic', 'customer', 'vehicle', 'service', 'sparepart', 'pkb', 'pkbService', 'pkbSparepart');
    }

    private function makePkb(array $fixture, User $mechanic, string $status, string $number): Pkb
    {
        return Pkb::create([
            'number' => $number,
            'customer_id' => $fixture['customer']->id,
            'vehicle_id' => $fixture['vehicle']->id,
            'created_by' => $fixture['admin']->id,
            'mechanic_id' => $mechanic->id,
            'complaint' => 'Keluhan untuk test progres',
            'inspection_result' => 'Pemeriksaan untuk test progres',
            'status' => $status,
            'service_date' => now(),
        ]);
    }
}
