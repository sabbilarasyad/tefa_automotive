<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckCommandTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Mode standar (tanpa --production): bila APP_ENV != production atau APP_DEBUG=true,
     * harus menampilkan WARN "bukan konfigurasi produksi" dan "Lokal: belum siap produksi".
     */
    public function test_standard_mode_shows_warn_when_env_is_local(): void
    {
        config(['app.env' => 'local', 'app.debug' => true]);

        $this->artisan('tefa:check')
            ->expectsOutputToContain('WARN  bukan konfigurasi produksi')
            ->expectsOutputToContain('Lokal: belum siap produksi')
            ->assertExitCode(0);
    }

    /**
     * Mode standar: bila APP_ENV=production dan APP_DEBUG=false,
     * tidak menampilkan WARN dan tidak menampilkan "Lokal: belum siap produksi".
     */
    public function test_standard_mode_no_warn_when_production_and_debug_false(): void
    {
        config(['app.env' => 'production', 'app.debug' => false]);

        $this->artisan('tefa:check')
            ->doesntExpectOutputToContain('Lokal: belum siap produksi')
            ->assertExitCode(0);
    }

    /**
     * Mode --production: bila APP_ENV != production => FAIL + exit code 1.
     */
    public function test_production_mode_fails_when_env_is_not_production(): void
    {
        config(['app.env' => 'local', 'app.debug' => false, 'app.timezone' => 'Asia/Jakarta']);

        $this->artisan('tefa:check --production')
            ->expectsOutputToContain('FAIL')
            ->assertExitCode(1);
    }

    /**
     * Mode --production: bila APP_DEBUG=true => FAIL + exit code 1.
     */
    public function test_production_mode_fails_when_debug_is_true(): void
    {
        config(['app.env' => 'production', 'app.debug' => true, 'app.timezone' => 'Asia/Jakarta']);

        $this->artisan('tefa:check --production')
            ->expectsOutputToContain('FAIL')
            ->assertExitCode(1);
    }

    /**
     * Mode --production: bila APP_TIMEZONE != Asia/Jakarta => FAIL + exit code 1.
     */
    public function test_production_mode_fails_when_timezone_is_not_asia_jakarta(): void
    {
        config(['app.env' => 'production', 'app.debug' => false, 'app.timezone' => 'UTC']);

        $this->artisan('tefa:check --production')
            ->expectsOutputToContain('FAIL')
            ->assertExitCode(1);
    }

    /**
     * Mode --production: bila ada akun demo => FAIL + exit code 1.
     */
    public function test_production_mode_fails_when_demo_accounts_exist(): void
    {
        $this->seed();

        config(['app.env' => 'production', 'app.debug' => false, 'app.timezone' => 'Asia/Jakarta']);

        $this->artisan('tefa:check --production')
            ->expectsOutputToContain('FAIL')
            ->assertExitCode(1);
    }
}
