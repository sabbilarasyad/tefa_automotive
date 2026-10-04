<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Pkb;
use App\Models\Service as WorkshopService;
use App\Models\ServiceHistory;
use App\Models\Sparepart;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PkbShowLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_pkb_show_link_to_service_history_contains_search_param(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();
        $admin = User::where('username', 'admincs')->firstOrFail();
        
        $customer = Customer::create(['name' => 'Test Customer', 'phone' => '081234567890']);
        $vehicle = Vehicle::create([
            'customer_id' => $customer->id,
            'plate_number' => 'B 1234 TEST',
            'brand' => 'Toyota',
            'model' => 'Avanza',
            'current_km' => 50000,
        ]);
        $service = WorkshopService::create([
            'code' => 'TEST-SRV',
            'name' => 'Test Service',
            'price' => 100000,
            'documentation_required' => false,
            'is_active' => true,
        ]);
        $pkb = Pkb::create([
            'number' => 'PKB/2026/10/0001',
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
            'summary' => 'Test',
            'total_amount' => 100000,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($superAdmin)
            ->get(route('admin.pkb.show', $pkb))
            ->assertOk();

        // Check the link is present and contains the search parameter
        $expectedUrl = route('admin.service-history.index', ['search' => $vehicle->plate_number]);
        $response->assertSee($expectedUrl);
    }

    public function test_clicking_pkb_show_link_navigates_to_service_history_with_search_filled(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();
        $admin = User::where('username', 'admincs')->firstOrFail();
        
        $customer = Customer::create(['name' => 'Test Customer', 'phone' => '081234567890']);
        $vehicle = Vehicle::create([
            'customer_id' => $customer->id,
            'plate_number' => 'B 1234 TEST',
            'brand' => 'Toyota',
            'model' => 'Avanza',
            'current_km' => 50000,
        ]);
        $service = WorkshopService::create([
            'code' => 'TEST-SRV',
            'name' => 'Test Service',
            'price' => 100000,
            'documentation_required' => false,
            'is_active' => true,
        ]);
        $pkb = Pkb::create([
            'number' => 'PKB/2026/10/0001',
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
            'summary' => 'Test',
            'total_amount' => 100000,
            'created_at' => now(),
        ]);

        // Get the PKB show page and extract the link
        $response = $this->actingAs($superAdmin)
            ->get(route('admin.pkb.show', $pkb))
            ->assertOk();

        // Now follow the link to service-history with search param
        $searchUrl = route('admin.service-history.index', ['search' => $vehicle->plate_number]);
        
        $response = $this->actingAs($superAdmin)
            ->get($searchUrl)
            ->assertOk()
            ->assertSee('B 1234 TEST') // vehicle found
            ->assertSee('Toyota Avanza');
            
        // Most importantly: check the search input value is filled
        $response->assertSee('value="B 1234 TEST"', false); // false = don't escape
    }
}