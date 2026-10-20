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

class VerifyWebRoleMatrixTest extends TestCase
{
    use RefreshDatabase;

    private array $webEndpoints = [
        // [method, route_name, required_permission, description, params]
        ['GET', 'admin.pkb.index', 'pkb.view_all', 'PKB Index', []],
        ['GET', 'admin.pkb.create', 'pkb.create', 'PKB Create', []],
        ['POST', 'admin.pkb.store', 'pkb.create', 'PKB Store', []],
        ['GET', 'admin.pkb.show', 'pkb.start_work', 'PKB Show', ['pkb' => 1]],
        ['POST', 'admin.pkb.cancel', 'pkb.cancel', 'PKB Cancel', ['pkb' => 1]],
        ['POST', 'admin.pkb.force-cancel', 'pkb.force_cancel', 'PKB Force Cancel', ['pkb' => 1]],
        ['POST', 'admin.pkb.assign-mechanic', 'pkb.assign_mechanic', 'PKB Assign Mechanic', ['pkb' => 1]],
        ['POST', 'admin.pkb.progress.start', 'pkb.start_work', 'PKB Progress Start', ['pkb' => 1]],
        ['PATCH', 'admin.pkb.progress.spareparts', 'pkb.update_work_progress', 'PKB Progress Spareparts', ['pkb' => 1]],
        ['POST', 'admin.pkb.progress.documentations.store', 'pkb.upload_documentation', 'PKB Doc Store', ['pkb' => 1]],
        ['DELETE', 'admin.pkb.progress.documentations.destroy', 'pkb.delete_documentation', 'PKB Doc Delete', ['pkb' => 1]],
        ['POST', 'admin.pkb.progress.complete', 'pkb.validate_complete', 'PKB Complete', ['pkb' => 1]],
        ['GET', 'admin.pkb.print', 'pkb.print', 'PKB Print', ['pkb' => 1]],
        
        ['GET', 'cashier.index', 'kasir.view_checklist', 'Cashier Index', []],
        ['GET', 'cashier.invoices.show', 'kasir.view_checklist', 'Cashier Show', ['invoice' => 1]],
        ['POST', 'cashier.invoices.payments.store', 'kasir.process_payment', 'Cashier Payment', ['invoice' => 1]],
        
        ['GET', 'admin.reports.index', 'report.view', 'Reports Index', []],
        ['GET', 'admin.reports.daily', 'report.view', 'Reports Daily', []],
        ['GET', 'admin.reports.monthly', 'report.view', 'Reports Monthly', []],
        ['GET', 'admin.reports.stock', 'report.view', 'Reports Stock', []],
        ['GET', 'admin.reports.documentation', 'report.view', 'Reports Documentation', []],
        ['GET', 'admin.reports.incomplete-payments', 'report.view', 'Reports Incomplete', []],
        ['GET', 'admin.reports.reminder', 'report.view', 'Reports Reminder', []],
        
        ['GET', 'admin.users.index', 'user.manage', 'Users Index', []],
        ['GET', 'admin.users.create', 'user.manage', 'Users Create', []],
        ['POST', 'admin.users.store', 'user.manage', 'Users Store', []],
        ['GET', 'admin.users.edit', 'user.manage', 'Users Edit', ['user' => 1]],
        ['PUT', 'admin.users.update', 'user.manage', 'Users Update', ['user' => 1]],
        ['DELETE', 'admin.users.destroy', 'user.manage', 'Users Destroy', ['user' => 1]],
        ['POST', 'admin.users.reset-password', 'user.reset_password', 'Users Reset Password', ['user' => 1]],
        
        ['GET', 'admin.services.index', 'master.manage_services', 'Services Index', []],
        ['GET', 'admin.services.create', 'master.manage_services', 'Services Create', []],
        ['POST', 'admin.services.store', 'master.manage_services', 'Services Store', []],
        ['GET', 'admin.services.edit', 'master.manage_services', 'Services Edit', ['service' => 1]],
        ['PUT', 'admin.services.update', 'master.manage_services', 'Services Update', ['service' => 1]],
        ['DELETE', 'admin.services.destroy', 'master.manage_services', 'Services Destroy', ['service' => 1]],
        
        ['GET', 'admin.spareparts.index', 'master.manage_spareparts', 'Spareparts Index', []],
        ['GET', 'admin.spareparts.create', 'master.manage_spareparts', 'Spareparts Create', []],
        ['POST', 'admin.spareparts.store', 'master.manage_spareparts', 'Spareparts Store', []],
        ['GET', 'admin.spareparts.edit', 'master.manage_spareparts', 'Spareparts Edit', ['sparepart' => 1]],
        ['PUT', 'admin.spareparts.update', 'master.manage_spareparts', 'Spareparts Update', ['sparepart' => 1]],
        ['DELETE', 'admin.spareparts.destroy', 'master.manage_spareparts', 'Spareparts Destroy', ['sparepart' => 1]],
        
        ['GET', 'admin.stock.index', 'master.manage_spareparts', 'Stock Index', []],
        ['POST', 'admin.stock.restock', 'master.adjust_stock', 'Stock Restock', ['sparepart' => 1]],
        ['GET', 'admin.stock.history', 'master.manage_spareparts', 'Stock History', ['sparepart' => 1]],
        
        ['GET', 'admin.service-history.index', null, 'Service History Index', []],
        ['GET', 'admin.service-history.show', null, 'Service History Show', ['serviceHistory' => 1]],
        
        ['GET', 'admin.reminder.index', 'reminder.manage', 'Reminder Index', []],
        ['POST', 'admin.reminder.send', 'reminder.manage', 'Reminder Send', ['reminder' => 1]],
    ];

    public function test_web_role_matrix(): void
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
        
        // PKB for progress routes (IN_PROGRESS)
        $pkb = Pkb::factory()->create([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'mechanic_id' => $mechanic->id,
            'status' => 'IN_PROGRESS',
            'service_date' => now(),
            'created_by' => $roles['SUPER_ADMIN']->id,
        ]);
        
        // PKB for cancel routes (WAITING_MECHANIC)
        $pkbCancel = Pkb::factory()->create([
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

        foreach ($this->webEndpoints as $endpoint) {
            [$method, $routeName, $requiredPermission, $description, $params] = $endpoint;

            foreach ($roles as $roleName => $user) {
                // Create fresh test data for each role iteration (especially important for DELETE)
                $customer = Customer::factory()->create();
                $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);
                $mechanic = User::factory()->create(['role_id' => $roles['MEKANIK']->role_id]);
                $sparepart = Sparepart::factory()->create();
                $service = Service::factory()->create();
                
                // PKB for progress routes (IN_PROGRESS)
                $pkb = Pkb::factory()->create([
                    'customer_id' => $customer->id,
                    'vehicle_id' => $vehicle->id,
                    'mechanic_id' => $mechanic->id,
                    'status' => 'IN_PROGRESS',
                    'service_date' => now(),
                    'created_by' => $roles['SUPER_ADMIN']->id,
                ]);
                
                // PKB for cancel/show/print routes (WAITING_MECHANIC)
                $pkbCancel = Pkb::factory()->create([
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

                // Build route parameters
                $routeParams = [];
                foreach ($params as $key => $value) {
                    if ($key === 'pkb' && str_contains($routeName, 'progress')) {
                        $routeParams[$key] = $pkb->id;
                    } elseif ($key === 'pkb' && (str_contains($routeName, 'cancel') || str_contains($routeName, 'force-cancel') || str_contains($routeName, 'assign-mechanic') || str_contains($routeName, 'start') || str_contains($routeName, 'show') || str_contains($routeName, 'print'))) {
                        $routeParams[$key] = $pkbCancel->id;
                    } elseif ($key === 'pkb') {
                        $routeParams[$key] = $pkb->id;
                    } elseif ($key === 'invoice') {
                        $routeParams[$key] = $invoice->id;
                    } elseif ($key === 'sparepart') {
                        $routeParams[$key] = $sparepart->id;
                    } elseif ($key === 'service') {
                        $routeParams[$key] = $service->id;
                    } elseif ($key === 'user') {
                        $routeParams[$key] = $roles['SUPER_ADMIN']->id;
                    } elseif ($key === 'serviceHistory') {
                        $routeParams[$key] = $pkb->id;
                    } elseif ($key === 'reminder') {
                        $routeParams[$key] = $reminder->id;
                    }
                }

                // Special handling for endpoints where policy allows access via multiple permission paths
                $hasPermission = $requiredPermission === null || $user->hasPermission($requiredPermission);
                
                // Exceptions: pkb.show allows access via pkb.view_all (HEAD_TEFA) or progress permissions (KASIR)
                if ($routeName === 'admin.pkb.show') {
                    $hasPermission = $hasPermission || $user->hasPermission('pkb.view_all') || $user->hasPermission('pkb.start_work');
                }
                
                $response = $this->actingAs($user)
                    ->call($method, route($routeName, $routeParams));

                $status = $response->getStatusCode();
                
                if ($hasPermission) {
                    // Role has permission - should NOT be 403 (may be 200, 302 redirect, 422 for validation, 404 for not found)
                    // For MEKANIK on pkb.index, expect 302 redirect to mechanic.tasks
                    if ($roleName === 'MEKANIK' && $routeName === 'admin.pkb.index') {
                        $this->assertEquals(302, $status, 
                            "{$roleName} on {$description} ({$method} {$routeName}) should redirect (302) but got {$status}");
                    } else {
                        $this->assertNotEquals(403, $status, 
                            "{$roleName} should have access to {$description} ({$method} {$routeName}) but got 403");
                    }
                } else {
                    // Role does NOT have permission - MUST be 403 (not 422, 200, 404, etc.)
                    // Exception: MEKANIK on admin.pkb.index gets 302 redirect, not 403
                    // Exception: KASIR on admin.pkb.index has access via pkb.view_waiting_payment (filtered)
                    if ($roleName === 'MEKANIK' && $routeName === 'admin.pkb.index') {
                        $this->assertEquals(302, $status,
                            "{$roleName} on {$description} ({$method} {$routeName}) should redirect (302) but got {$status}");
                    } elseif ($roleName === 'KASIR' && $routeName === 'admin.pkb.index') {
                        $this->assertNotEquals(403, $status,
                            "{$roleName} should have access to {$description} ({$method} {$routeName}) but got 403");
                    } else {
                        $this->assertEquals(403, $status,
                            "{$roleName} should be forbidden (403) for {$description} ({$method} {$routeName}) but got {$status}");
                    }
                }
            }
        }
    }
}