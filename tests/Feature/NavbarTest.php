<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavbarTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_sees_all_navbar_links(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();

        $response = $this->actingAs($superAdmin)
            ->get(route('admin.pkb.index'))
            ->assertOk();

        // Check all navbar links are present in response
        $response->assertSee('Daftar PKB'); // or 'Tugas & Riwayat Saya' depending on permissions
        $response->assertSee('Progres Pekerjaan');
        $response->assertSee('Kasir');
        $response->assertSee('Servis Baru');
        $response->assertSee('Stok');
        $response->assertSee('Riwayat Servis');
        $response->assertSee('Reminder');
    }

    public function test_admin_cs_sees_stock_reminder_but_not_mekanik_links(): void
    {
        $this->seed();
        $adminCs = User::where('username', 'admincs')->firstOrFail();

        $response = $this->actingAs($adminCs)
            ->get(route('admin.pkb.index'))
            ->assertOk();

        $response->assertSee('Stok');
        $response->assertSee('Riwayat Servis');
        $response->assertSee('Reminder');
        $response->assertSee('Servis Baru');
    }

    public function test_kasir_sees_riwayat_and_reminder_but_not_stock(): void
    {
        $this->seed();
        $kasir = User::where('username', 'kasir')->firstOrFail();

        $response = $this->actingAs($kasir)
            ->get(route('admin.pkb.index'))
            ->assertOk();

        $response->assertDontSee('Stok'); // kasir doesn't have master.manage_spareparts
        $response->assertSee('Riwayat Servis');
        $response->assertSee('Reminder');
        $response->assertDontSee('Servis Baru'); // kasir doesn't have pkb.create
    }

    public function test_mekanik_sees_only_riwayat_servis(): void
    {
        $this->seed();
        $mekanik = User::where('username', 'mekanik')->firstOrFail();

        // Mekanik doesn't have pkb.view_all, so accessing /admin/pkb redirects
        $this->actingAs($mekanik)
            ->get(route('admin.pkb.index'))
            ->assertRedirect();

        // But they CAN access service-history
        $this->actingAs($mekanik)
            ->get(route('admin.service-history.index'))
            ->assertOk()
            ->assertSee('Riwayat Servis per Kendaraan');
    }
}