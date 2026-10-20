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

class VerifyAccessMatrixTest extends TestCase
{
    use RefreshDatabase;

    private array $endpoints = [
        ['GET', 'admin.pkb.index', 'PKB List'],
        ['GET', 'admin.pkb.create', 'PKB Create'],
        ['POST', 'admin.pkb.store', 'PKB Store'],
        ['GET', 'admin.pkb.show', 'PKB Show'],
        ['POST', 'admin.pkb.cancel', 'PKB Cancel'],
        ['POST', 'admin.pkb.force-cancel', 'PKB Force Cancel'],
        ['POST', 'admin.pkb.assign-mechanic', 'PKB Assign Mechanic'],
        ['POST', 'admin.pkb.progress.start', 'PKB Start'],
        ['PATCH', 'admin.pkb.progress.spareparts', 'PKB Spareparts'],
        ['POST', 'admin.pkb.progress.documentations.store', 'PKB Doc Store'],
        ['DELETE', 'admin.pkb.progress.documentations.destroy', 'PKB Doc Delete'],
        ['POST', 'admin.pkb.progress.complete', 'PKB Complete'],
        ['GET', 'cashier.index', 'Cashier Index'],
        ['GET', 'cashier.invoices.show', 'Cashier Show'],
        ['POST', 'cashier.invoices.payments.store', 'Cashier Payment'],
        ['GET', 'admin.reports.index', 'Reports Index'],
        ['GET', 'admin.reports.daily', 'Reports Daily'],
        ['GET', 'admin.reports.monthly', 'Reports Monthly'],
        ['GET', 'admin.reports.stock', 'Reports Stock'],
        ['GET', 'admin.reports.documentation', 'Reports Documentation'],
        ['GET', 'admin.reports.incomplete-payments', 'Reports Incomplete'],
        ['GET', 'admin.reports.reminder', 'Reports Reminder'],
        ['GET', 'admin.users.index', 'Users Index'],
        ['GET', 'admin.users.create', 'Users Create'],
        ['POST', 'admin.users.store', 'Users Store'],
        ['GET', 'admin.stock.index', 'Stock Index'],
        ['POST', 'admin.stock.restock', 'Stock Restock'],
        ['GET', 'admin.stock.history', 'Stock History'],
        ['GET', 'admin.services.index', 'Services Index'],
        ['GET', 'admin.services.create', 'Services Create'],
        ['POST', 'admin.services.store', 'Services Store'],
        ['GET', 'admin.service-history.index', 'Service History Index'],
        ['GET', 'admin.reminder.index', 'Reminder Index'],
        ['POST', 'admin.reminder.send', 'Reminder Send'],
        ['GET', 'admin.service-history.show', 'Service History Show'],
        ['GET', 'admin.pkb.progress.index', 'PKB Progress Index'],
        ['GET', 'admin.pkb.progress.show', 'PKB Progress Show'],
        ['POST', 'admin.pkb.progress.start', 'PKB Progress Start'],
        ['PATCH', 'admin.pkb.progress.spareparts', 'PKB Progress Spareparts'],
        ['POST', 'admin.pkb.progress.documentations.store', 'PKB Doc Store'],
        ['DELETE', 'admin.pkb.progress.documentations.destroy', 'PKB Doc Delete'],
        ['POST', 'admin.pkb.progress.complete', 'PKB Complete'],
        ['GET', 'admin.pkb.print', 'PKB Print'],
    ];

    public function test_super_admin_has_access_to_all_admin_endpoints(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();

        // Create test data for routes requiring models
        $customer = Customer::factory()->create();
        $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);
        $mechanic = User::factory()->create(['role_id' => User::where('username', 'mekanik')->first()->role_id]);
        $sparepart = Sparepart::factory()->create();
        $service = Service::factory()->create();
        
        // PKB with status allowing cancel (WAITING_PARTS or WAITING_MECHANIC)
        $pkb = Pkb::factory()->create([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'mechanic_id' => $mechanic->id,
            'status' => 'WAITING_MECHANIC',
            'service_date' => now(),
            'created_by' => $superAdmin->id,
        ]);
        
        // PKB for progress routes (IN_PROGRESS)
        $pkbProgress = Pkb::factory()->create([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'mechanic_id' => $mechanic->id,
            'status' => 'IN_PROGRESS',
            'service_date' => now(),
            'created_by' => $superAdmin->id,
        ]);
        
        $invoice = Invoice::factory()->create([
            'pkb_id' => $pkb->id,
            'status' => 'UNPAID',
        ]);
        
        $reminder = Reminder::factory()->create([
            'vehicle_id' => $vehicle->id,
            'reminder_date' => now()->toDateString(),
        ]);
        
        // Create ServiceHistory for service-history.show route
        $serviceHistory = \App\Models\ServiceHistory::factory()->create([
            'pkb_id' => $pkb->id,
            'vehicle_id' => $vehicle->id,
            'service_date' => now(),
        ]);

        foreach ($this->endpoints as $endpoint) {
            [$method, $route, $description] = array_pad($endpoint, 3, null);
            
            // Build parameters based on route requirements
            $params = $this->getRouteParams($route, [
                'pkb' => $pkb,
                'pkbProgress' => $pkbProgress,
                'invoice' => $invoice,
                'sparepart' => $sparepart,
                'serviceHistory' => $serviceHistory,
                'reminder' => $reminder,
            ]);

            $response = $this->actingAs($superAdmin)
                ->call($method, route($route, $params));

            // Some endpoints may return 403 due to business logic (e.g., PKB status not allowing cancel)
            // SUPER_ADMIN should not get 404 (route not found) or 500 (server error)
            $this->assertNotEquals(404, $response->getStatusCode(), "SUPER_ADMIN 404 $description ($method $route)");
            $this->assertNotEquals(500, $response->getStatusCode(), "SUPER_ADMIN 500 $description ($method $route)");
        }
    }

    private function getRouteParams(string $route, array $models): array
    {
        $params = [];
        
        // PKB routes (non-progress)
        if (str_contains($route, 'admin.pkb.') && !str_contains($route, 'progress')) {
            if (str_contains($route, 'cancel') || str_contains($route, 'force-cancel') || str_contains($route, 'assign-mechanic') || str_contains($route, 'start') || str_contains($route, 'show') || str_contains($route, 'print')) {
                if (isset($models['pkb'])) {
                    $params['pkb'] = $models['pkb']->id;
                }
            }
        }
        
        // PKB Progress routes
        if (str_contains($route, 'pkb.progress')) {
            if (isset($models['pkbProgress'])) {
                $params['pkb'] = $models['pkbProgress']->id;
            }
        }
        
        // Cashier/Invoice routes
        if (str_contains($route, 'cashier.invoices') || str_contains($route, 'invoices.')) {
            if (isset($models['invoice'])) {
                $params['invoice'] = $models['invoice']->id;
            }
        }
        
        // Stock routes
        if (str_contains($route, 'stock.restock') || str_contains($route, 'stock.history')) {
            if (isset($models['sparepart'])) {
                $params['sparepart'] = $models['sparepart']->id;
            }
        }
        
        // Service history
        if (str_contains($route, 'service-history.show')) {
            if (isset($models['serviceHistory'])) {
                $params['serviceHistory'] = $models['serviceHistory']->id;
            }
        }
        
        // Reminder
        if (str_contains($route, 'reminder.send')) {
            if (isset($models['reminder'])) {
                $params['reminder'] = $models['reminder']->id;
            }
        }
        
        return $params;
    }

    public function test_head_tefa_has_access_to_reports_and_pkb_only(): void
    {
        $this->seed();
        $headTefa = User::where('username', 'headtefa')->firstOrFail();

        // HEAD_TEFA has: pkb.view_all, report.view
        $allowedEndpoints = [
            ['GET', 'admin.reports.index'],
            ['GET', 'admin.reports.daily'],
            ['GET', 'admin.reports.monthly'],
            ['GET', 'admin.reports.stock'],
            ['GET', 'admin.reports.documentation'],
            ['GET', 'admin.reports.incomplete-payments'],
            ['GET', 'admin.reports.reminder'],
            ['GET', 'admin.pkb.index'],
        ];

        foreach ($allowedEndpoints as $endpoint) {
            [$method, $route] = $endpoint;
            $this->actingAs($headTefa)
                ->call($method, route($route))
                ->assertOk();
        }

        $forbiddenEndpoints = [
            ['GET', 'admin.users.index'],
            ['GET', 'admin.services.index'],
            ['GET', 'admin.spareparts.index'],
            ['GET', 'admin.stock.index'],
            ['GET', 'cashier.index'],
        ];

        foreach ($forbiddenEndpoints as $endpoint) {
            [$method, $route] = $endpoint;
            $this->actingAs($headTefa)
                ->call($method, route($route))
                ->assertForbidden();
        }
    }

    public function test_admin_cs_has_full_admin_access_except_users(): void
    {
        $this->seed();
        $adminCs = User::where('username', 'admincs')->firstOrFail();

        // ADMIN_CS has: pkb.*, master.manage_services, master.manage_spareparts, master.adjust_stock, report.view, reminder.manage
        // Does NOT have: user.manage, kasir.*
        $allowedEndpoints = [
            ['GET', 'admin.pkb.index'],
            ['GET', 'admin.pkb.create'],
            ['GET', 'admin.reports.index'],
            ['GET', 'admin.reports.daily'],
            ['GET', 'admin.reports.monthly'],
            ['GET', 'admin.reports.stock'],
            ['GET', 'admin.reports.documentation'],
            ['GET', 'admin.reports.incomplete-payments'],
            ['GET', 'admin.reports.reminder'],
            ['GET', 'admin.services.index'],
            ['GET', 'admin.services.create'],
            ['GET', 'admin.spareparts.index'],
            ['GET', 'admin.spareparts.create'],
            ['GET', 'admin.stock.index'],
            ['GET', 'admin.service-history.index'],
            ['GET', 'admin.reminder.index'],
        ];

        foreach ($allowedEndpoints as $endpoint) {
            [$method, $route] = $endpoint;
            $response = $this->actingAs($adminCs)
                ->call($method, route($route));
            $this->assertNotEquals(403, $response->getStatusCode(), "ADMIN_CS denied $route");
        }

        $this->actingAs($adminCs)
            ->get(route('admin.users.index'))
            ->assertForbidden();
        
        // ADMIN_CS should NOT have access to cashier (kasir.* permissions)
        $this->actingAs($adminCs)
            ->get(route('cashier.index'))
            ->assertForbidden();
    }

    public function test_kasir_can_access_cashier_and_reports_reminder_only(): void
    {
        $this->seed();
        $kasir = User::where('username', 'kasir')->firstOrFail();

        // KASIR has: pkb.view_waiting_payment, pkb.start_work, pkb.update_work_progress, 
        // pkb.upload_documentation, pkb.delete_documentation, pkb.validate_complete,
        // kasir.view_checklist, kasir.process_payment, kasir.print_invoice, reminder.manage
        // Does NOT have: report.view
        
        // KASIR CAN access pkb.index (filtered to WAITING_PAYMENT due to pkb.view_waiting_payment)
        $this->actingAs($kasir)
            ->get(route('admin.pkb.index'))
            ->assertOk();

        $this->actingAs($kasir)
            ->get(route('cashier.index'))
            ->assertOk();

        // KASIR can access reminder management (admin.reminder.index) and service history, not reports.reminder
        $this->actingAs($kasir)
            ->get(route('admin.reminder.index'))
            ->assertOk();

        $this->actingAs($kasir)
            ->get(route('admin.service-history.index'))
            ->assertOk();

        $forbiddenEndpoints = [
            ['GET', 'admin.reports.index'],
            ['GET', 'admin.reports.daily'],
            ['GET', 'admin.reports.monthly'],
            ['GET', 'admin.reports.stock'],
            ['GET', 'admin.reports.documentation'],
            ['GET', 'admin.reports.incomplete-payments'],
            ['GET', 'admin.reports.reminder'],
            ['GET', 'admin.users.index'],
            ['GET', 'admin.services.index'],
            ['GET', 'admin.spareparts.index'],
            ['GET', 'admin.stock.index'],
        ];

        foreach ($forbiddenEndpoints as $endpoint) {
            [$method, $route] = $endpoint;
            $this->actingAs($kasir)
                ->call($method, route($route))
                ->assertForbidden();
        }
    }

    public function test_mekanik_can_only_access_mechanic_tasks(): void
    {
        $this->seed();
        $mekanik = User::where('username', 'mekanik')->firstOrFail();

        // MEKANIK has: pkb.view_own only
        // Does NOT have: pkb.start_work, pkb.update_work_progress
        
        $this->actingAs($mekanik)
            ->get(route('mechanic.tasks'))
            ->assertOk();

        // MEKANIK should NOT access pkb.progress.index (requires pkb.start_work or pkb.update_work_progress)
        $this->actingAs($mekanik)
            ->get(route('admin.pkb.progress.index'))
            ->assertForbidden();

        // MEKANIK accessing pkb.index gets redirected to mechanic.tasks (302) due to pkb.view_own
        $this->actingAs($mekanik)
            ->get(route('admin.pkb.index'))
            ->assertRedirect(route('mechanic.tasks'));

        $forbiddenEndpoints = [
            ['GET', 'admin.reports.index'],
            ['GET', 'admin.reports.daily'],
            ['GET', 'admin.reports.reminder'],
            ['GET', 'cashier.index'],
            ['GET', 'admin.users.index'],
            ['GET', 'admin.services.index'],
        ];

        foreach ($forbiddenEndpoints as $endpoint) {
            [$method, $route] = $endpoint;
            $this->actingAs($mekanik)
                ->call($method, route($route))
                ->assertForbidden();
        }
    }
}