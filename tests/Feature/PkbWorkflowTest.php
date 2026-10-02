<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Pkb;
use App\Models\PkbService;
use App\Models\PkbSparepart;
use App\Models\Service as WorkshopService;
use App\Models\Sparepart;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PkbWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_pkb_with_server_estimate_and_monthly_number_even_with_json_accept_header(): void
    {
        $this->seed();
        $this->travelTo(now()->setDate(2026, 9, 29)->setTime(10, 0));
        $fixture = $this->fixture(stock: 5);

        $response = $this->actingAs($fixture['admin'])
            ->withHeader('Accept', 'application/json')
            ->post(route('admin.pkb.store'), $this->intakeData($fixture));

        $pkb = Pkb::firstOrFail();

        $response->assertRedirect(route('admin.pkb.show', $pkb));
        $this->assertSame('PKB/2026/09/0001', $pkb->number);
        $this->assertSame('WAITING_MECHANIC', $pkb->status);
        $this->assertSame($fixture['mechanic']->id, $pkb->mechanic_id);
        $this->assertSame('475000.00', $pkb->estimated_total);
        $this->assertDatabaseCount('pkb_services', 1);
        $this->assertDatabaseCount('pkb_spareparts', 1);
        $this->assertDatabaseHas('spareparts', ['id' => $fixture['sparepart']->id, 'stock_qty' => 5]);
    }

    public function test_pkb_starts_waiting_for_parts_when_planned_quantity_exceeds_stock(): void
    {
        $this->seed();
        $fixture = $this->fixture(stock: 0.5);

        $this->actingAs($fixture['admin'])
            ->post(route('admin.pkb.store'), $this->intakeData($fixture))
            ->assertRedirect();

        $this->assertDatabaseHas('pkb', ['number' => 'PKB/'.now()->format('Y/m').'/0001', 'status' => 'WAITING_PARTS']);
    }

    public function test_mid_create_database_failure_rolls_back_customer_vehicle_counter_and_pkb_rows(): void
    {
        $this->seed();
        $fixture = $this->fixture(stock: 5);
        DB::unprepared("CREATE TRIGGER reject_pkb_service BEFORE INSERT ON pkb_services BEGIN SELECT RAISE(ABORT, 'forced insert failure'); END");
        $intakeData = $this->intakeData($fixture);
        unset($intakeData['customer_id'], $intakeData['vehicle_id']);
        $intakeData['customer'] = ['name' => 'Customer Rollback', 'phone' => '081111111111'];
        $intakeData['vehicle'] = ['plate_number' => 'B 9999 ROL', 'brand' => 'Honda', 'model' => 'Brio'];

        try {
            $this->actingAs($fixture['admin'])
                ->post(route('admin.pkb.store'), $intakeData)
                ->assertServerError();

            $this->assertDatabaseCount('pkb', 0);
            $this->assertDatabaseCount('pkb_services', 0);
            $this->assertDatabaseCount('pkb_spareparts', 0);
            $this->assertDatabaseCount('document_number_counters', 0);
            $this->assertDatabaseCount('customers', 1);
            $this->assertDatabaseCount('vehicles', 1);
        } finally {
            DB::unprepared('DROP TRIGGER IF EXISTS reject_pkb_service');
        }
    }

    public function test_reassigning_mechanic_does_not_reset_work_progress_or_status(): void
    {
        $this->seed();
        $fixture = $this->fixture(stock: 5);
        $otherMechanic = User::create([
            'role_id' => $fixture['mechanic']->role_id,
            'name' => 'Mekanik Pengganti',
            'username' => 'mekanik2',
            'email' => 'mekanik2@tefa.local',
            'password' => 'password',
            'is_active' => true,
        ]);
        $pkb = $this->existingPkb($fixture, $otherMechanic, 'IN_PROGRESS');
        $pkbService = PkbService::create([
            'pkb_id' => $pkb->id,
            'service_id' => $fixture['service']->id,
            'name_snapshot' => $fixture['service']->name,
            'qty' => 1,
            'unit_price' => $fixture['service']->price,
            'subtotal' => $fixture['service']->price,
            'is_done' => true,
        ]);
        $pkbSparepart = PkbSparepart::create([
            'pkb_id' => $pkb->id,
            'sparepart_id' => $fixture['sparepart']->id,
            'name_snapshot' => $fixture['sparepart']->name,
            'qty_planned' => 2,
            'qty_used' => 1.5,
            'unit_price' => $fixture['sparepart']->selling_price,
            'subtotal' => $fixture['sparepart']->selling_price * 2,
        ]);

        $this->withToken($fixture['admin']->createToken('workflow-test')->plainTextToken)
            ->postJson('/api/pkb/'.$pkb->id.'/assign-mechanic', ['mechanic_id' => $fixture['mechanic']->id])
            ->assertOk()
            ->assertJsonPath('data.mechanic_id', $fixture['mechanic']->id);

        $this->assertDatabaseHas('pkb', ['id' => $pkb->id, 'status' => 'IN_PROGRESS', 'mechanic_id' => $fixture['mechanic']->id]);
        $this->assertDatabaseHas('pkb_services', ['id' => $pkbService->id, 'is_done' => true]);
        $this->assertDatabaseHas('pkb_spareparts', ['id' => $pkbSparepart->id, 'qty_used' => 1.5]);
    }

    public function test_super_admin_force_cancel_returns_used_stock_and_records_status_history(): void
    {
        $this->seed();
        $fixture = $this->fixture(stock: 3);
        $pkb = $this->existingPkb($fixture, $fixture['mechanic'], 'IN_PROGRESS');
        PkbSparepart::create([
            'pkb_id' => $pkb->id,
            'sparepart_id' => $fixture['sparepart']->id,
            'name_snapshot' => $fixture['sparepart']->name,
            'qty_planned' => 2,
            'qty_used' => 2,
            'unit_price' => $fixture['sparepart']->selling_price,
            'subtotal' => $fixture['sparepart']->selling_price * 2,
        ]);
        PkbSparepart::create([
            'pkb_id' => $pkb->id,
            'sparepart_id' => $fixture['sparepart']->id,
            'name_snapshot' => $fixture['sparepart']->name,
            'qty_planned' => 1,
            'qty_used' => 1,
            'unit_price' => $fixture['sparepart']->selling_price,
            'subtotal' => $fixture['sparepart']->selling_price,
        ]);

        $this->withToken($fixture['superAdmin']->createToken('workflow-test')->plainTextToken)
            ->postJson('/api/pkb/'.$pkb->id.'/force-cancel', ['cancel_reason' => 'Kesalahan pekerjaan'])
            ->assertOk()
            ->assertJsonPath('data.status', 'CANCELLED');

        $this->assertDatabaseHas('stock_movements', [
            'sparepart_id' => $fixture['sparepart']->id,
            'type' => 'RETURN',
            'reference_type' => 'PKB',
            'reference_id' => $pkb->id,
            'qty_in' => 3,
        ]);
        $this->assertDatabaseCount('stock_movements', 1);
        $this->assertDatabaseHas('spareparts', ['id' => $fixture['sparepart']->id, 'stock_qty' => 6]);
        $this->assertDatabaseHas('pkb_status_histories', [
            'pkb_id' => $pkb->id,
            'from_status' => 'IN_PROGRESS',
            'to_status' => 'CANCELLED',
        ]);
    }

    public function test_mechanic_cannot_create_pkb_even_if_the_browser_requests_json(): void
    {
        $this->seed();
        $mechanic = User::where('username', 'mekanik')->firstOrFail();

        $this->actingAs($mechanic)
            ->withHeader('Accept', 'application/json')
            ->get('/admin/pkb/create')
            ->assertForbidden()
            ->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function test_admin_can_render_new_pkb_form_with_service_and_sparepart_options(): void
    {
        $this->seed();
        $fixture = $this->fixture(stock: 5);

        $this->actingAs($fixture['admin'])
            ->get(route('admin.pkb.create'))
            ->assertOk()
            ->assertSee('Servis Baru')
            ->assertSee($fixture['service']->name)
            ->assertSee($fixture['sparepart']->name);
    }

    public function test_web_login_uses_session_authentication_without_creating_an_api_token(): void
    {
        $this->seed();
        $admin = User::where('username', 'admincs')->firstOrFail();

        $this->post(route('login.store'), ['username' => 'admincs', 'password' => 'password'])
            ->assertRedirect(route('admin.pkb.index'));

        $this->assertAuthenticatedAs($admin);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_mechanic_and_cashier_are_forbidden_from_api_create_and_cancel(): void
    {
        $this->seed();
        $fixture = $this->fixture(stock: 5);
        $pkb = $this->existingPkb($fixture, $fixture['mechanic'], 'WAITING_MECHANIC');

        foreach (['mekanik', 'kasir'] as $username) {
            $user = User::where('username', $username)->firstOrFail();
            $token = $user->createToken('authorization-test')->plainTextToken;

            $this->withToken($token)
                ->postJson('/api/pkb', $this->intakeData($fixture))
                ->assertForbidden()
                ->assertJsonPath('success', false);

            $this->withToken($token)
                ->postJson('/api/pkb/'.$pkb->id.'/cancel', ['cancel_reason' => 'Tidak berwenang'])
                ->assertForbidden()
                ->assertJsonPath('success', false);
        }

        $this->assertDatabaseHas('pkb', ['id' => $pkb->id, 'status' => 'WAITING_MECHANIC']);
        $this->assertDatabaseCount('pkb', 1);
    }

    public function test_admin_can_cancel_before_work_but_cannot_cancel_after_work_starts(): void
    {
        $this->seed();
        $fixture = $this->fixture(stock: 5);
        $beforeWork = $this->existingPkb($fixture, $fixture['mechanic'], 'WAITING_MECHANIC');
        $afterWork = $this->existingPkb($fixture, $fixture['mechanic'], 'IN_PROGRESS');
        $token = $fixture['admin']->createToken('cancel-test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/pkb/'.$beforeWork->id.'/cancel', ['cancel_reason' => 'Customer membatalkan'])
            ->assertOk()
            ->assertJsonPath('data.status', 'CANCELLED');

        $this->withToken($token)
            ->postJson('/api/pkb/'.$afterWork->id.'/cancel', ['cancel_reason' => 'Terlambat'])
            ->assertForbidden();

        $this->assertDatabaseHas('pkb_status_histories', [
            'pkb_id' => $beforeWork->id,
            'from_status' => 'WAITING_MECHANIC',
            'to_status' => 'CANCELLED',
        ]);
        $this->assertDatabaseHas('pkb', ['id' => $afterWork->id, 'status' => 'IN_PROGRESS']);

        $this->withToken($token)
            ->getJson('/api/pkb/'.$beforeWork->id.'/status-histories')
            ->assertOk()
            ->assertJsonPath('data.0.to_status', 'CANCELLED');
    }

    public function test_print_api_payload_matches_the_web_print_document(): void
    {
        $this->seed();
        $fixture = $this->fixture(stock: 5);
        $pkb = $this->existingPkb($fixture, $fixture['mechanic'], 'WAITING_MECHANIC');
        PkbService::create([
            'pkb_id' => $pkb->id,
            'service_id' => $fixture['service']->id,
            'name_snapshot' => $fixture['service']->name,
            'qty' => 1,
            'unit_price' => $fixture['service']->price,
            'subtotal' => $fixture['service']->price,
        ]);
        $token = $fixture['admin']->createToken('print-test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/pkb/'.$pkb->id.'/print')
            ->assertOk()
            ->assertJsonPath('data.number', $pkb->number)
            ->assertJsonPath('data.customer.name', $fixture['customer']->name)
            ->assertJsonPath('data.vehicle.plate_number', $fixture['vehicle']->plate_number)
            ->assertJsonPath('data.mechanic.name', $fixture['mechanic']->name)
            ->assertJsonPath('data.services.0.name', $fixture['service']->name);

        $this->actingAs($fixture['admin'])
            ->get(route('admin.pkb.print', $pkb))
            ->assertOk()
            ->assertSee($pkb->number)
            ->assertSee('Catatan mekanik / part aktual')
            ->assertSee($fixture['mechanic']->name);
    }

    /** @return array{admin: User, superAdmin: User, mechanic: User, customer: Customer, vehicle: Vehicle, service: WorkshopService, sparepart: Sparepart} */
    private function fixture(float $stock): array
    {
        $customer = Customer::create(['name' => 'Rina Test', 'phone' => '081234567890']);

        return [
            'admin' => User::where('username', 'admincs')->firstOrFail(),
            'superAdmin' => User::where('username', 'superadmin')->firstOrFail(),
            'mechanic' => User::where('username', 'mekanik')->firstOrFail(),
            'customer' => $customer,
            'vehicle' => Vehicle::create([
                'customer_id' => $customer->id,
                'plate_number' => 'B 1234 TEF',
                'brand' => 'Toyota',
                'model' => 'Avanza',
                'current_km' => 45000,
            ]),
            'service' => WorkshopService::create(['code' => 'SRV-TEST', 'name' => 'Tune Up', 'price' => 250000, 'is_active' => true]),
            'sparepart' => Sparepart::create(['code' => 'OIL-TEST', 'name' => 'Oli Mesin', 'selling_price' => 50000, 'stock_qty' => $stock, 'unit' => 'pcs', 'is_active' => true]),
        ];
    }

    /** @param array{admin: User, superAdmin: User, mechanic: User, customer: Customer, vehicle: Vehicle, service: WorkshopService, sparepart: Sparepart} $fixture
     * @return array<string, mixed>
     */
    private function intakeData(array $fixture): array
    {
        return [
            'customer_id' => $fixture['customer']->id,
            'vehicle_id' => $fixture['vehicle']->id,
            'complaint' => 'Mesin terdengar kasar saat langsam.',
            'inspection_result' => 'Perlu tune up dan ganti oli.',
            'mechanic_id' => $fixture['mechanic']->id,
            'services' => [['service_id' => $fixture['service']->id, 'qty' => 1.5]],
            'spareparts' => [['sparepart_id' => $fixture['sparepart']->id, 'qty_planned' => 2]],
        ];
    }

    /** @param array{admin: User, superAdmin: User, mechanic: User, customer: Customer, vehicle: Vehicle, service: WorkshopService, sparepart: Sparepart} $fixture */
    private function existingPkb(array $fixture, User $mechanic, string $status): Pkb
    {
        return Pkb::create([
            'number' => sprintf('PKB/%s/%04d', now()->format('Y/m'), Pkb::count() + 1),
            'customer_id' => $fixture['customer']->id,
            'vehicle_id' => $fixture['vehicle']->id,
            'created_by' => $fixture['admin']->id,
            'mechanic_id' => $mechanic->id,
            'complaint' => 'Mesin berisik',
            'status' => $status,
            'service_date' => now(),
        ]);
    }
}
