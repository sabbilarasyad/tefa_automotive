<?php

namespace Tests\Verify;

use App\Models\Reminder;
use App\Models\Service;
use App\Models\Sparepart;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifyApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_api_spareparts_index_with_low_stock_filter(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();
        $token = $superAdmin->createToken('api-test')->plainTextToken;

        Sparepart::create([
            'code' => 'LOW-001',
            'name' => 'Low Stock Part',
            'unit' => 'pcs',
            'selling_price' => 10000,
            'cost_price' => 5000,
            'stock_qty' => 1,
            'min_stock' => 5,
            'documentation_required' => false,
            'is_active' => true,
        ]);

        Sparepart::create([
            'code' => 'OK-001',
            'name' => 'OK Part',
            'unit' => 'pcs',
            'selling_price' => 20000,
            'cost_price' => 10000,
            'stock_qty' => 100,
            'min_stock' => 5,
            'documentation_required' => false,
            'is_active' => true,
        ]);

        $this->withToken($token)
            ->getJson('/api/spareparts?low_stock=true')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.data.0.name', 'Low Stock Part')
            ->assertJsonCount(1, 'data.data');
    }

    /** @test */
    public function test_api_sparepart_stock_adjustment_manual_in(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();
        $token = $superAdmin->createToken('api-test')->plainTextToken;

        $sparepart = Sparepart::create([
            'code' => 'ADJ-001',
            'name' => 'Adjust Part',
            'unit' => 'pcs',
            'selling_price' => 10000,
            'cost_price' => 5000,
            'stock_qty' => 10,
            'min_stock' => 5,
            'documentation_required' => false,
            'is_active' => true,
        ]);

        $this->withToken($token)
            ->postJson("/api/spareparts/{$sparepart->id}/stock-adjustments", [
                'qty' => 5,
                'type' => 'MANUAL_IN',
                'reason' => 'Pembelian baru PO-123',
            ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.type', 'MANUAL_IN')
            ->assertJsonPath('data.qty_in', '5.000');

        $sparepart->refresh();
        $this->assertEquals(15, (float)$sparepart->stock_qty);
    }

    /** @test */
    public function test_api_sparepart_stock_adjustment_validation(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();
        $token = $superAdmin->createToken('api-test')->plainTextToken;

        $sparepart = Sparepart::create([
            'code' => 'VAL-001',
            'name' => 'Validation Part',
            'unit' => 'pcs',
            'selling_price' => 10000,
            'cost_price' => 5000,
            'stock_qty' => 10,
            'min_stock' => 5,
            'documentation_required' => false,
            'is_active' => true,
        ]);

        // Missing reason
        $this->withToken($token)
            ->postJson("/api/spareparts/{$sparepart->id}/stock-adjustments", [
                'qty' => 5,
                'type' => 'MANUAL_IN',
                'reason' => '',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.reason.0', 'Alasan wajib diisi.');

        // Invalid type
        $this->withToken($token)
            ->postJson("/api/spareparts/{$sparepart->id}/stock-adjustments", [
                'qty' => 5,
                'type' => 'INVALID_TYPE',
                'reason' => 'Test',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.type.0', 'Tipe penyesuaian tidak valid.');
    }

    /** @test */
    public function test_api_vehicle_service_histories(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();
        $token = $superAdmin->createToken('api-test')->plainTextToken;

        $admin = User::where('username', 'admincs')->firstOrFail();
        $customer = \App\Models\Customer::create(['name' => 'API Customer', 'phone' => '081234567890']);
        $vehicle = Vehicle::create([
            'customer_id' => $customer->id,
            'plate_number' => 'B 9999 API',
            'brand' => 'Honda',
            'model' => 'Jazz',
            'current_km' => 40000,
        ]);
        $pkb = \App\Models\Pkb::create([
            'number' => 'PKB/2026/10/API01',
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
        \App\Models\ServiceHistory::create([
            'pkb_id' => $pkb->id,
            'vehicle_id' => $vehicle->id,
            'service_date' => '2026-04-02 09:30:00',
            'km' => 40000,
            'summary' => 'Test service',
            'total_amount' => 100000,
            'created_at' => now(),
        ]);

        $this->withToken($token)
            ->getJson("/api/vehicles/{$vehicle->id}/service-histories")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.data.0.pkb.number', 'PKB/2026/10/API01');
    }

    /** @test */
    public function test_api_reminders_index_with_filters(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();
        $token = $superAdmin->createToken('api-test')->plainTextToken;

        $admin = User::where('username', 'admincs')->firstOrFail();
        $customer = \App\Models\Customer::create(['name' => 'Reminder Customer', 'phone' => '081234567890']);
        $vehicle = Vehicle::create([
            'customer_id' => $customer->id,
            'plate_number' => 'B 1111 REM',
            'brand' => 'Toyota',
            'model' => 'Yaris',
            'current_km' => 20000,
        ]);
        $pkb = \App\Models\Pkb::create([
            'number' => 'PKB/2026/10/REM1',
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
            'reminder_date' => now()->subDays(1)->toDateString(),
            'type' => 'SERVICE_6_MONTHS',
            'status' => 'PENDING',
        ]);
        Reminder::create([
            'pkb_id' => $pkb->id,
            'vehicle_id' => $vehicle->id,
            'customer_id' => $customer->id,
            'reminder_date' => now()->addDays(10)->toDateString(),
            'type' => 'SERVICE_6_MONTHS',
            'status' => 'SENT',
        ]);

        // Filter status=PENDING
        $this->withToken($token)
            ->getJson('/api/reminders?status=PENDING')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.data.0.status', 'PENDING')
            ->assertJsonCount(1, 'data.data'); // Only 1 pending

        // Filter due_date
        $this->withToken($token)
            ->getJson('/api/reminders?due_date=' . now()->toDateString())
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function test_api_reminder_send(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();
        $token = $superAdmin->createToken('api-test')->plainTextToken;

        $admin = User::where('username', 'admincs')->firstOrFail();
        $customer = \App\Models\Customer::create(['name' => 'Send Customer', 'phone' => '081234567890']);
        $vehicle = Vehicle::create([
            'customer_id' => $customer->id,
            'plate_number' => 'B 2222 SND',
            'brand' => 'Suzuki',
            'model' => 'Swift',
            'current_km' => 15000,
        ]);
        $pkb = \App\Models\Pkb::create([
            'number' => 'PKB/2026/10/SND1',
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

        $this->withToken($token)
            ->postJson("/api/reminders/{$reminder->id}/send")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('whatsapp_url', function ($url) {
                return str_starts_with($url, 'https://wa.me/');
            });

        $reminder->refresh();
        $this->assertEquals('SENT', $reminder->status);
        $this->assertNotNull($reminder->sent_at);
        $this->assertNotNull($reminder->message);
    }

    /** @test */
    public function test_api_users_crud_and_reset_password(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();
        $token = $superAdmin->createToken('api-test')->plainTextToken;

        // Create
        $response = $this->withToken($token)
            ->postJson('/api/users', [
                'role_id' => \App\Models\Role::where('name', 'KASIR')->first()->id,
                'name' => 'New API User',
                'username' => 'apiuser',
                'email' => 'apiuser@tefa.local',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.username', 'apiuser');

        $newUser = \App\Models\User::where('username', 'apiuser')->firstOrFail();

        // Show
        $this->withToken($token)
            ->getJson("/api/users/{$newUser->id}")
            ->assertOk()
            ->assertJsonPath('data.username', 'apiuser');

        // Update
        $this->withToken($token)
            ->patchJson("/api/users/{$newUser->id}", [
                'name' => 'Updated Name',
                'username' => 'apiuser',
                'email' => 'apiuser@tefa.local',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Updated Name');

        // Reset password
        $this->withToken($token)
            ->patchJson("/api/users/{$newUser->id}/reset-password")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('temporary_password', function ($pwd) {
                return is_string($pwd) && strlen($pwd) > 0;
            });

        // Delete (deactivate)
        $this->withToken($token)
            ->deleteJson("/api/users/{$newUser->id}")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Pengguna berhasil dinonaktifkan.');

        $newUser->refresh();
        $this->assertFalse($newUser->is_active);
        $this->assertDatabaseHas('users', ['id' => $newUser->id, 'is_active' => false]);
    }

    /** @test */
    public function test_api_master_services_crud(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();
        $token = $superAdmin->createToken('api-test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/master/services', [
                'code' => 'API-SRV',
                'name' => 'API Service',
                'price' => 100000,
                'documentation_required' => true,
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('success', true);

        $service = Service::where('code', 'API-SRV')->firstOrFail();

        $this->withToken($token)
            ->getJson("/api/master/services/{$service->id}")
            ->assertOk()
            ->assertJsonPath('data.code', 'API-SRV');

        $this->withToken($token)
            ->patchJson("/api/master/services/{$service->id}", [
                'name' => 'Updated API Service',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated API Service');

        $this->withToken($token)
            ->deleteJson("/api/master/services/{$service->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }

    /** @test */
    public function test_api_master_spareparts_crud(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();
        $token = $superAdmin->createToken('api-test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/master/spareparts', [
                'code' => 'API-PRT',
                'name' => 'API Part',
                'unit' => 'pcs',
                'selling_price' => 20000,
                'cost_price' => 10000,
                'stock_qty' => 50,
                'min_stock' => 5,
                'documentation_required' => false,
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('success', true);

        $part = Sparepart::where('code', 'API-PRT')->firstOrFail();

        $this->withToken($token)
            ->getJson("/api/master/spareparts/{$part->id}")
            ->assertOk()
            ->assertJsonPath('data.code', 'API-PRT');

        $this->withToken($token)
            ->patchJson("/api/master/spareparts/{$part->id}", [
                'name' => 'Updated API Part',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated API Part');

        $this->withToken($token)
            ->deleteJson("/api/master/spareparts/{$part->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('spareparts', ['id' => $part->id]);
    }

    /** @test */
    public function test_api_contact_form_public_throttle(): void
    {
        // Public endpoint with throttle 10/min
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/contact-messages', [
                'name' => "Test User $i",
                'email' => "test$i@example.com",
                'phone' => '081234567890',
                'subject' => 'Test Subject',
                'message' => 'Test message content',
            ])->assertCreated();
        }

        // 11th request should be throttled
        $this->postJson('/api/contact-messages', [
            'name' => 'Test User 11',
            'email' => 'test11@example.com',
            'subject' => 'Test Subject',
            'message' => 'Test message content',
        ])->assertStatus(429);
    }

    /** @test */
    public function test_api_401_without_token(): void
    {
        $this->getJson('/api/spareparts')
            ->assertUnauthorized();

        // contact-messages is public (no auth required), so it returns 422 for validation errors, not 401
        $this->postJson('/api/contact-messages', [])
            ->assertStatus(422);
    }

    /** @test */
    public function test_api_403_for_wrong_role(): void
    {
        $this->seed();
        $mekanik = User::where('username', 'mekanik')->firstOrFail();
        $token = $mekanik->createToken('api-test')->plainTextToken;

        // Mekanik cannot access master services
        $this->withToken($token)
            ->getJson('/api/master/services')
            ->assertForbidden();

        // Mekanik cannot access users
        $this->withToken($token)
            ->getJson('/api/users')
            ->assertForbidden();
    }

    /** @test */
    public function test_api_422_validation_errors(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();
        $token = $superAdmin->createToken('api-test')->plainTextToken;

        // Sparepart stock adjustment validation
        $sparepart = Sparepart::create([
            'code' => 'VAL-001',
            'name' => 'Validation Part',
            'unit' => 'pcs',
            'selling_price' => 10000,
            'cost_price' => 5000,
            'stock_qty' => 10,
            'min_stock' => 5,
            'documentation_required' => false,
            'is_active' => true,
        ]);

        $this->withToken($token)
            ->postJson("/api/spareparts/{$sparepart->id}/stock-adjustments", [
                'qty' => -5, // Invalid
                'type' => 'MANUAL_IN',
                'reason' => 'Test',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.qty.0', 'Jumlah minimal 0.001.');

        // User creation validation
        $this->withToken($token)
            ->postJson('/api/users', [
                'role_id' => 999, // Not exists
                'name' => '',
                'username' => '',
                'email' => 'invalid-email',
                'password' => 'short',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.role_id.0', 'Peran tidak valid.')
            ->assertJsonPath('errors.name.0', 'Nama wajib diisi.')
            ->assertJsonPath('errors.username.0', 'Username wajib diisi.')
            ->assertJsonPath('errors.email.0', 'Format email tidak valid.')
            ->assertJsonPath('errors.password.0', 'Password minimal 8 karakter.');
    }

    /** @test */
    public function test_no_duplicate_routes_in_api(): void
    {
        $routes = collect(\Illuminate\Support\Facades\Route::getRoutes())
            ->filter(fn ($r) => str_starts_with($r->uri(), 'api/'))
            ->map(fn ($r) => $r->methods()[0] . ' ' . $r->uri())
            ->toArray();

        $duplicates = array_diff_assoc($routes, array_unique($routes));
        $this->assertEmpty($duplicates, 'Duplicate API routes found: ' . implode(', ', $duplicates));
    }

    /** @test */
    public function test_403_error_page_renders_custom_view(): void
    {
        $this->seed();
        $mekanik = User::where('username', 'mekanik')->firstOrFail();

        $response = $this->actingAs($mekanik)
            ->get(route('admin.services.index'))
            ->assertForbidden();

        $response->assertSee('Akses Ditolak')
            ->assertSee('403')
            ->assertDontSee('Stack trace')
            ->assertDontSee('at ')
            ->assertDontSee('File:');
    }

    /** @test */
    public function test_404_error_page_renders_custom_view(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();

        $response = $this->actingAs($superAdmin)
            ->get('/admin/non-existent-route')
            ->assertNotFound();

        $response->assertSee('Halaman Tidak Ditemukan')
            ->assertSee('404')
            ->assertDontSee('Stack trace')
            ->assertDontSee('at ')
            ->assertDontSee('File:');
    }

    /** @test */
    public function test_403_error_page_api_returns_json_without_stack_trace(): void
    {
        $this->seed();
        $mekanik = User::where('username', 'mekanik')->firstOrFail();
        $token = $mekanik->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/master/services')
            ->assertForbidden();

        $response->assertJsonStructure([
            'success',
            'message',
        ])
        ->assertJsonMissing(['trace'])
        ->assertJsonMissing(['exception']);
    }

    /** @test */
    public function test_404_error_page_api_returns_json_without_stack_trace(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();
        $token = $superAdmin->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/non-existent-route')
            ->assertNotFound();

        $response->assertJsonStructure([
            'success',
            'message',
        ])
        ->assertJsonMissing(['trace'])
        ->assertJsonMissing(['exception']);
    }
}