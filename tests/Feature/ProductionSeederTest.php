<?php

namespace Tests\Feature;

use App\Support\AdminPasswordRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionSeederTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_admin_password_rule_rejects_null(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ADMIN_PASSWORD tidak boleh kosong.');
        AdminPasswordRule::check(null);
    }

    /** @test */
    public function test_admin_password_rule_rejects_empty_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ADMIN_PASSWORD tidak boleh kosong.');
        AdminPasswordRule::check('');
    }

    /** @test */
    public function test_admin_password_rule_rejects_changeme123_case_insensitive(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ADMIN_PASSWORD tidak boleh menggunakan nilai default "changeme123". Gunakan password yang kuat dan unik.');
        AdminPasswordRule::check('changeme123');
    }

    /** @test */
    public function test_admin_password_rule_rejects_changeme123_uppercase(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ADMIN_PASSWORD tidak boleh menggunakan nilai default "changeme123". Gunakan password yang kuat dan unik.');
        AdminPasswordRule::check('CHANGEME123');
    }

    /** @test */
    public function test_admin_password_rule_rejects_changeme123_mixed_case(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ADMIN_PASSWORD tidak boleh menggunakan nilai default "changeme123". Gunakan password yang kuat dan unik.');
        AdminPasswordRule::check('ChangeMe123');
    }

    /** @test */
    public function test_admin_password_rule_rejects_short_password(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ADMIN_PASSWORD minimal 12 karakter.');
        AdminPasswordRule::check('short');
    }

    /** @test */
    public function test_admin_password_rule_accepts_valid_password(): void
    {
        // Should not throw
        AdminPasswordRule::check('ValidPassword12345');
        $this->assertTrue(true);
    }

    /** @test */
    public function test_production_seeder_creates_super_admin_with_valid_password(): void
    {
        config([
            'tefa.admin.username' => 'superadmin',
            'tefa.admin.password' => 'ValidPassword12345',
        ]);

        $this->seed(\Database\Seeders\ProductionSeeder::class);

        $this->assertDatabaseHas('users', [
            'username' => 'superadmin',
            'email' => 'superadmin@tefa.local',
        ]);

        $user = \App\Models\User::where('username', 'superadmin')->first();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('ValidPassword12345', $user->password));
    }
}