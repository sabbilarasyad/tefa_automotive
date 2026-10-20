<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionSeederTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_production_seeder_rejects_empty_admin_password(): void
    {
        $this->artisan('db:seed', ['--class' => 'Database\Seeders\ProductionSeeder'], ['ADMIN_PASSWORD' => ''])
            ->expectsOutput('ADMIN_PASSWORD minimal 12 karakter.')
            ->assertExitCode(1);

        $this->assertDatabaseMissing('users', ['username' => 'superadmin']);
    }

    /** @test */
    public function test_production_seeder_rejects_short_admin_password(): void
    {
        $this->artisan('db:seed', ['--class' => 'Database\Seeders\ProductionSeeder'], ['ADMIN_PASSWORD' => 'short'])
            ->expectsOutput('ADMIN_PASSWORD minimal 12 karakter.')
            ->assertExitCode(1);

        $this->assertDatabaseMissing('users', ['username' => 'superadmin']);
    }

    /** @test */
    public function test_production_seeder_rejects_default_admin_password(): void
    {
        $this->artisan('db:seed', ['--class' => 'Database\Seeders\ProductionSeeder'], ['ADMIN_PASSWORD' => 'changeme123'])
            ->expectsOutput('ADMIN_PASSWORD tidak boleh menggunakan nilai default "changeme123". Gunakan password yang kuat dan unik.')
            ->assertExitCode(1);

        $this->assertDatabaseMissing('users', ['username' => 'superadmin']);
    }

    /** @test */
    public function test_production_seeder_accepts_valid_admin_password(): void
    {
        $this->artisan('db:seed', ['--class' => 'Database\Seeders\ProductionSeeder'], ['ADMIN_PASSWORD' => 'validpassword123'])
            ->expectsOutput('SUPER_ADMIN \'superadmin\' created/updated.')
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'username' => 'superadmin',
            'email' => 'superadmin@tefa.local',
        ]);

        $user = User::where('username', 'superadmin')->first();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('validpassword123', $user->password));
    }
}