<?php

namespace Tests\Feature;

use App\Models\Pkb;
use App\Services\NumberGeneratorService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimezoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_app_timezone_is_asia_jakarta(): void
    {
        $this->assertSame('Asia/Jakarta', config('app.timezone'));
    }

    /**
     * Pada pukul 23:30 WIB (31 Oktober 2026) nomor PKB harus menggunakan
     * bulan/tahun WIB (Oktober 2026 = /2026/10/), bukan bulan UTC (November 2026).
     */
    public function test_pkb_number_uses_wib_on_2026_10_31_at_23_30_wib(): void
    {
        // 2026-10-31 23:30 WIB = 2026-10-31 16:30 UTC
        Carbon::setTestNow(Carbon::parse('2026-10-31 23:30:00', 'Asia/Jakarta'));

        $this->seed();
        $generator = app(NumberGeneratorService::class);
        $number = $generator->next('PKB');

        // Tahun dan bulan harus sesuai WIB: 2026/10
        $this->assertStringContainsString('/2026/10/', $number, "PKB number should use WIB month (Oct 2026): {$number}");

        Carbon::setTestNow();
    }

    /**
     * Pada pukul 00:30 WIB (1 November 2026) nomor PKB harus menggunakan
     * bulan/tahun WIB (November 2026 = /2026/11/).
     */
    public function test_pkb_number_uses_wib_on_2026_11_01_at_00_30_wib(): void
    {
        // 2026-11-01 00:30 WIB = 2026-10-31 17:30 UTC
        Carbon::setTestNow(Carbon::parse('2026-11-01 00:30:00', 'Asia/Jakarta'));

        $this->seed();
        $generator = app(NumberGeneratorService::class);
        $number = $generator->next('PKB');

        // Tahun dan bulan harus sesuai WIB: 2026/11
        $this->assertStringContainsString('/2026/11/', $number, "PKB number should use WIB month (Nov 2026): {$number}");

        Carbon::setTestNow();
    }

    /**
     * Pada pukul 23:30 WIB (31 Oktober 2026) batas hari reminder (6 bulan dari service_date)
     * harus dihitung dari tanggal WIB (2026-10-31), menghasilkan reminder 2027-04-30.
     */
    public function test_reminder_date_uses_wib_on_2026_10_31_at_23_30_wib(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-31 23:30:00', 'Asia/Jakarta'));

        // service_date = now() = 2026-10-31 (WIB), reminder = +6 bulan = 2027-04-30
        $serviceDate = now();
        $reminderDate = $serviceDate->copy()->addMonthsNoOverflow(6)->toDateString();

        $this->assertSame('2027-04-30', $reminderDate, 'Reminder date should be 6 months from WIB service date');

        Carbon::setTestNow();
    }

    /**
     * Pada pukul 00:30 WIB (1 November 2026) reminder harus dihitung dari
     * tanggal WIB (2026-11-01), menghasilkan reminder 2027-05-01.
     */
    public function test_reminder_date_uses_wib_on_2026_11_01_at_00_30_wib(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-01 00:30:00', 'Asia/Jakarta'));

        $serviceDate = now();
        $reminderDate = $serviceDate->copy()->addMonthsNoOverflow(6)->toDateString();

        $this->assertSame('2027-05-01', $reminderDate, 'Reminder date should be 6 months from WIB service date');

        Carbon::setTestNow();
    }
}
