<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Service;
use App\Models\Sparepart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMasterDataTest extends TestCase
{
    use RefreshDatabase;

    // --- Services Tests ---

    public function test_admin_cs_can_access_services_index(): void
    {
        $this->seed();
        $adminCs = User::where('username', 'admincs')->firstOrFail();

        $this->actingAs($adminCs)
            ->get(route('admin.services.index'))
            ->assertOk()
            ->assertSee('Daftar Jasa');
    }

    public function test_super_admin_can_access_services_index(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();

        $this->actingAs($superAdmin)
            ->get(route('admin.services.index'))
            ->assertOk()
            ->assertSee('Daftar Jasa');
    }

    public function test_kasir_cannot_access_services_index(): void
    {
        $this->seed();
        $kasir = User::where('username', 'kasir')->firstOrFail();

        $this->actingAs($kasir)
            ->get(route('admin.services.index'))
            ->assertForbidden();
    }

    public function test_mekanik_cannot_access_services_index(): void
    {
        $this->seed();
        $mekanik = User::where('username', 'mekanik')->firstOrFail();

        $this->actingAs($mekanik)
            ->get(route('admin.services.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_create_service_with_documentation_required(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();

        $this->actingAs($superAdmin)
            ->post(route('admin.services.store'), [
                'code' => 'NEW-SRV',
                'name' => 'New Service',
                'description' => 'Description',
                'price' => 150000,
                'documentation_required' => '1',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.services.index'))
            ->assertSessionHas('success');

        $service = Service::where('code', 'NEW-SRV')->firstOrFail();
        $this->assertTrue($service->documentation_required);
    }

    public function test_services_index_route_not_404(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();

        $this->actingAs($superAdmin)
            ->get('/admin/services')
            ->assertOk();
    }

    // --- Spareparts Tests ---

    public function test_admin_cs_can_access_spareparts_index(): void
    {
        $this->seed();
        $adminCs = User::where('username', 'admincs')->firstOrFail();

        $this->actingAs($adminCs)
            ->get(route('admin.spareparts.index'))
            ->assertOk()
            ->assertSee('Daftar Sparepart');
    }

    public function test_super_admin_can_access_spareparts_index(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();

        $this->actingAs($superAdmin)
            ->get(route('admin.spareparts.index'))
            ->assertOk()
            ->assertSee('Daftar Sparepart');
    }

    public function test_kasir_cannot_access_spareparts_index(): void
    {
        $this->seed();
        $kasir = User::where('username', 'kasir')->firstOrFail();

        $this->actingAs($kasir)
            ->get(route('admin.spareparts.index'))
            ->assertForbidden();
    }

    public function test_mekanik_cannot_access_spareparts_index(): void
    {
        $this->seed();
        $mekanik = User::where('username', 'mekanik')->firstOrFail();

        $this->actingAs($mekanik)
            ->get(route('admin.spareparts.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_create_sparepart_with_documentation_required(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();

        $this->actingAs($superAdmin)
            ->post(route('admin.spareparts.store'), [
                'code' => 'NEW-PART',
                'name' => 'New Part',
                'unit' => 'pcs',
                'selling_price' => 50000,
                'cost_price' => 30000,
                'stock_qty' => 10,
                'min_stock' => 5,
                'documentation_required' => '1',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.spareparts.index'))
            ->assertSessionHas('success');

        $part = Sparepart::where('code', 'NEW-PART')->firstOrFail();
        $this->assertTrue($part->documentation_required);
    }

    public function test_spareparts_index_route_not_404(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();

        $this->actingAs($superAdmin)
            ->get('/admin/spareparts')
            ->assertOk();
    }
}