<?php

namespace Tests\Verify;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Pkb;
use App\Models\Reminder;
use App\Models\Service;
use App\Models\Sparepart;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifyApiRoleMatrixTest extends TestCase
{
    use RefreshDatabase;

    private array $apiEndpoints = [
        // Sparepart routes
        ['GET', 'spareparts.index', 'master.manage_spareparts', 'Sparepart Index', []],
        ['GET', 'spareparts.show', 'master.manage_spareparts', 'Sparepart Show', ['sparepart' => 'sparepart']],
        ['POST', 'spareparts.stock-adjustments', 'master.adjust_stock', 'Stock Adjustment', ['sparepart' => 'sparepart']],
        
        // Vehicle routes
        ['GET', 'vehicles.service-histories', null, 'Vehicle Service Histories', ['vehicle' => 'vehicle']],
        
        // Reminder routes
        ['GET', 'reminders.index', 'reminder.manage', 'Reminder Index', []],
        ['POST', 'reminders.send', 'reminder.manage', 'Reminder Send', ['reminder' => 'reminder']],
        
        // User routes (Super Admin only)
        ['GET', 'users.index', 'user.manage', 'User Index', []],
        ['POST', 'users.store', 'user.manage', 'User Store', []],
        ['GET', 'users.show', 'user.manage', 'User Show', ['user' => 'superadmin']],
        ['PATCH', 'users.update', 'user.manage', 'User Update', ['user' => 'superadmin']],
        ['DELETE', 'users.destroy', 'user.manage', 'User Destroy', ['user' => 'superadmin']],
        ['PATCH', 'users.reset-password', 'user.reset_password', 'User Reset Password', ['user' => 'superadmin']],
        
        // Master Data - Services
        ['GET', 'master.services.index', 'master.manage_services', 'Master Service Index', []],
        ['POST', 'master.services.store', 'master.manage_services', 'Master Service Store', []],
        ['GET', 'master.services.show', 'master.manage_services', 'Master Service Show', ['service' => 'service']],
        ['PATCH', 'master.services.update', 'master.manage_services', 'Master Service Update', ['service' => 'service']],
        ['DELETE', 'master.services.destroy', 'master.manage_services', 'Master Service Destroy', ['service' => 'service']],
        
        // Master Data - Spareparts
        ['GET', 'master.spareparts.index', 'master.manage_spareparts', 'Master Sparepart Index', []],
        ['POST', 'master.spareparts.store', 'master.manage_spareparts', 'Master Sparepart Store', []],
        ['GET', 'master.spareparts.show', 'master.manage_spareparts', 'Master Sparepart Show', ['sparepart' => 'sparepart']],
        ['PATCH', 'master.spareparts.update', 'master.manage_spareparts', 'Master Sparepart Update', ['sparepart' => 'sparepart']],
        ['DELETE', 'master.spareparts.destroy', 'master.manage_spareparts', 'Master Sparepart Destroy', ['sparepart' => 'sparepart']],
    ];

    public function test_api_role_matrix(): void
    {
        $this->seed();
        
        $roles = [
            'SUPER_ADMIN' => User::where('username', 'superadmin')->firstOrFail(),
            'HEAD_TEFA' => User::where('username', 'headtefa')->firstOrFail(),
            'ADMIN_CS' => User::where('username', 'admincs')->firstOrFail(),
            'MEKANIK' => User::where('username', 'mekanik')->firstOrFail(),
            'KASIR' => User::where('username', 'kasir')->firstOrFail(),
        ];

        // Create test data
        $customer = Customer::factory()->create();
        $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);
        $mechanic = User::factory()->create(['role_id' => $roles['MEKANIK']->role_id]);
        $sparepart = Sparepart::factory()->create();
        $service = Service::factory()->create();
        
        $pkb = Pkb::factory()->create([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'mechanic_id' => $mechanic->id,
            'status' => 'WAITING_MECHANIC',
            'service_date' => now(),
            'created_by' => $roles['SUPER_ADMIN']->id,
        ]);
        
        $invoice = Invoice::factory()->create([
            'pkb_id' => $pkb->id,
            'status' => 'UNPAID',
        ]);
        
        $reminder = Reminder::factory()->create([
            'vehicle_id' => $vehicle->id,
            'reminder_date' => now()->toDateString(),
        ]);

        foreach ($this->apiEndpoints as $endpoint) {
            [$method, $routeName, $requiredPermission, $description, $paramKeys] = $endpoint;
            
            // Build base route parameters (will be overridden per role for DELETE)
            $baseParams = [];
            foreach ($paramKeys as $key => $modelKey) {
                switch ($modelKey) {
                    case 'sparepart':
                        $baseParams[$key] = 'sparepart';
                        break;
                    case 'vehicle':
                        $baseParams[$key] = 'vehicle';
                        break;
                    case 'reminder':
                        $baseParams[$key] = 'reminder';
                        break;
                    case 'superadmin':
                        $baseParams[$key] = 'superadmin';
                        break;
                    case 'service':
                        $baseParams[$key] = 'service';
                        break;
                }
            }

            foreach ($roles as $roleName => $user) {
                // Create fresh test data for each role iteration (especially important for DELETE)
                $customer = Customer::factory()->create();
                $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);
                $mechanic = User::factory()->create(['role_id' => $roles['MEKANIK']->role_id]);
                $sparepart = Sparepart::factory()->create();
                $service = Service::factory()->create();
                
                $pkb = Pkb::factory()->create([
                    'customer_id' => $customer->id,
                    'vehicle_id' => $vehicle->id,
                    'mechanic_id' => $mechanic->id,
                    'status' => 'WAITING_MECHANIC',
                    'service_date' => now(),
                    'created_by' => $roles['SUPER_ADMIN']->id,
                ]);
                
                $invoice = Invoice::factory()->create([
                    'pkb_id' => $pkb->id,
                    'status' => 'UNPAID',
                ]);
                
                $reminder = Reminder::factory()->create([
                    'vehicle_id' => $vehicle->id,
                    'reminder_date' => now()->toDateString(),
                ]);
                
                // Build route parameters with actual IDs
                $params = [];
                foreach ($paramKeys as $key => $modelKey) {
                    switch ($modelKey) {
                        case 'sparepart':
                            $params[$key] = $sparepart->id;
                            break;
                        case 'vehicle':
                            $params[$key] = $vehicle->id;
                            break;
                        case 'reminder':
                            $params[$key] = $reminder->id;
                            break;
                        case 'superadmin':
                            $params[$key] = $roles['SUPER_ADMIN']->id;
                            break;
                        case 'service':
                            $params[$key] = $service->id;
                            break;
                    }
                }

                $testUri = route("api.{$routeName}", $params);

                $hasPermission = $requiredPermission === null || $user->hasPermission($requiredPermission);
                $token = $user->createToken('test')->plainTextToken;
                
                $headers = ['Authorization' => 'Bearer ' . $token];
                
                if ($method === 'GET') {
                    $response = $this->getJson($testUri, $headers);
                } elseif ($method === 'POST') {
                    $response = $this->postJson($testUri, [], $headers);
                } elseif ($method === 'PATCH') {
                    $response = $this->patchJson($testUri, [], $headers);
                } elseif ($method === 'DELETE') {
                    $response = $this->call('DELETE', $testUri, [], [], [], ['HTTP_Authorization' => 'Bearer ' . $token]);
                } else {
                    $response = $this->call($method, $testUri, [], [], [], ['HTTP_Authorization' => 'Bearer ' . $token]);
                }

                $status = $response->getStatusCode();
                
                if ($hasPermission) {
                    // Role has permission - should NOT be 403 (may be 200, 201, 422 for validation, 404 for not found)
                    // But SUPER_ADMIN deleting themselves should be 422
                    if ($routeName === 'users.destroy' && $roleName === 'SUPER_ADMIN') {
                        $this->assertEquals(422, $status, "SUPER_ADMIN deleting self should return 422");
                    } else {
                        $this->assertNotEquals(403, $status, 
                            "{$roleName} should have access to {$description} ({$method} {$testUri}) but got 403");
                    }
                } else {
                    // Role does NOT have permission - MUST be 403 (not 422, 200, 404, etc.)
                    $this->assertEquals(403, $status,
                        "{$roleName} should be forbidden (403) for {$description} ({$method} {$testUri}) but got {$status}");
                }
            }
        }
    }
}