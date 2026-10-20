<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_access_users_index(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();

        $this->actingAs($superAdmin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Daftar Pengguna');
    }

    public function test_admin_cs_cannot_access_users_index(): void
    {
        $this->seed();
        $adminCs = User::where('username', 'admincs')->firstOrFail();

        $this->actingAs($adminCs)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_kasir_cannot_access_users_index(): void
    {
        $this->seed();
        $kasir = User::where('username', 'kasir')->firstOrFail();

        $this->actingAs($kasir)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_mekanik_cannot_access_users_index(): void
    {
        $this->seed();
        $mekanik = User::where('username', 'mekanik')->firstOrFail();

        $this->actingAs($mekanik)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_head_tefa_cannot_access_users_index(): void
    {
        $this->seed();
        $headTefa = User::where('username', 'headtefa')->firstOrFail();

        $this->actingAs($headTefa)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_create_user(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();

        $this->actingAs($superAdmin)
            ->post(route('admin.users.store'), [
                'role_id' => Role::where('name', 'KASIR')->first()->id,
                'name' => 'New User',
                'username' => 'newuser',
                'email' => 'newuser@tefa.local',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['username' => 'newuser']);
    }

    public function test_super_admin_can_edit_user(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();
        $kasir = User::where('username', 'kasir')->firstOrFail();

        $this->actingAs($superAdmin)
            ->put(route('admin.users.update', $kasir), [
                'role_id' => $kasir->role_id,
                'name' => 'Updated Name',
                'username' => 'kasir',
                'email' => 'kasir@tefa.local',
                'password' => '',
                'password_confirmation' => '',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $kasir->refresh();
        $this->assertEquals('Updated Name', $kasir->name);
    }

    public function test_super_admin_can_deactivate_user(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();
        $userToDeactivate = User::create([
            'role_id' => Role::where('name', 'KASIR')->first()->id,
            'name' => 'To Deactivate',
            'username' => 'todeactivate',
            'email' => 'todeactivate@tefa.local',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->actingAs($superAdmin)
            ->delete(route('admin.users.destroy', $userToDeactivate))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $userToDeactivate->refresh();
        $this->assertFalse($userToDeactivate->is_active);
        $this->assertDatabaseHas('users', ['id' => $userToDeactivate->id, 'is_active' => false]);
    }

    public function test_super_admin_cannot_delete_self(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();

        $this->actingAs($superAdmin)
            ->delete(route('admin.users.destroy', $superAdmin))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $superAdmin->id]);
    }

    public function test_super_admin_can_reset_password(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();
        $kasir = User::where('username', 'kasir')->firstOrFail();
        $oldPasswordHash = $kasir->password;

        $this->actingAs($superAdmin)
            ->post(route('admin.users.reset-password', $kasir))
            ->assertRedirect()
            ->assertSessionHas('success');

        $kasir->refresh();
        $this->assertNotEquals($oldPasswordHash, $kasir->password);
    }

    public function test_users_index_route_not_404(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();

        $this->actingAs($superAdmin)
            ->get('/admin/users')
            ->assertOk();
    }

    public function test_super_admin_sees_pengguna_link_in_navbar(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();

        $response = $this->actingAs($superAdmin)
            ->get(route('admin.pkb.index'))
            ->assertOk();

        $response->assertSee('Pengguna');
    }

    public function test_admin_cs_does_not_see_pengguna_link_in_navbar(): void
    {
        $this->seed();
        $adminCs = User::where('username', 'admincs')->firstOrFail();

        $response = $this->actingAs($adminCs)
            ->get(route('admin.pkb.index'))
            ->assertOk();

        $response->assertDontSee('Pengguna');
    }

    /** @test */
    public function test_production_seeder_creates_exactly_one_super_admin_from_env(): void
    {
        // This test uses the seeded database
        $this->seed();

        $superAdmins = User::whereHas('role', fn ($q) => $q->where('name', 'SUPER_ADMIN'))
            ->where('is_active', true)
            ->get();

        $this->assertCount(1, $superAdmins);
        $this->assertEquals('superadmin', $superAdmins->first()->username);
        $this->assertEquals('superadmin@tefa.local', $superAdmins->first()->email);
    }

    /** @test */
    public function test_production_environment_has_no_demo_accounts(): void
    {
        config(['app.env' => 'production']);
        $this->app->instance('config', config());

        // In production, only ProductionSeeder runs
        $this->artisan('db:seed', ['--class' => 'Database\Seeders\ProductionSeeder']);

        $users = User::whereHas('role', fn ($q) => $q->whereIn('name', ['HEAD_TEFA', 'ADMIN_CS', 'MEKANIK', 'KASIR']))
            ->where('is_active', true)
            ->get();

        $this->assertCount(0, $users, 'No demo accounts should exist in production');
    }
}