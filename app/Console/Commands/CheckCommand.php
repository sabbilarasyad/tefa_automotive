<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CheckCommand extends Command
{
    protected $signature = 'tefa:check {--production : Run strict production checks (exit 1 on any issue)}';

    protected $description = 'Check deployment readiness: PHP version, extensions, APP_DEBUG, DB connection, triggers/functions, storage link, writable dirs';

    public function handle(): int
    {
        if ($this->option('production')) {
            return $this->runProductionChecks();
        }

        return $this->runStandardChecks();
    }

    private function runStandardChecks(): int
    {
        $checks = [
            ['PHP Version >= 8.4.1', $this->checkPhpVersion()],
            ['Required Extensions', $this->checkExtensions()],
            ['APP_DEBUG=false in production', $this->checkAppDebug()],
            ['Database Connection', $this->checkDatabase()],
            ['MySQL Triggers (3)', $this->checkTriggers()],
            ['MySQL Functions (2)', $this->checkFunctions()],
            ['Storage Link Exists', $this->checkStorageLink()],
            ['storage/ writable', $this->checkWritable('storage')],
            ['bootstrap/cache writable', $this->checkWritable('bootstrap/cache')],
        ];

        $allPassed = true;
        foreach ($checks as [$name, $result]) {
            $status = $result['ok'] ? '<fg=green>✓ PASS</>' : '<fg=red>✗ FAIL</>';
            $this->line("{$status}  {$name}");
            if (! $result['ok']) {
                $this->line("    <fg=yellow>{$result['message']}</>");
                $allPassed = false;
            } elseif (isset($result['details'])) {
                $this->line("    <fg=cyan>{$result['details']}</>");
            }
        }

        $this->newLine();

        $env = config('app.env');
        $debug = config('app.debug');

        if ($env !== 'production' || $debug) {
            $this->warn('WARN  bukan konfigurasi produksi');
            $this->line('Lokal: belum siap produksi');

            return self::SUCCESS;
        }

        if ($allPassed) {
            $this->info('All checks passed. Ready for deployment.');

            return self::SUCCESS;
        } else {
            $this->error('Some checks failed. Please fix before deploying.');

            return self::FAILURE;
        }
    }

    private function runProductionChecks(): int
    {
        $failures = [];

        $env = config('app.env');
        $debug = config('app.debug');
        $timezone = config('app.timezone');

        if ($env !== 'production') {
            $failures[] = "APP_ENV={$env} (harus production)";
        }

        if ($debug) {
            $failures[] = 'APP_DEBUG=true (harus false)';
        }

        if ($timezone !== 'Asia/Jakarta') {
            $failures[] = "APP_TIMEZONE={$timezone} (harus Asia/Jakarta)";
        }

        // Check for demo accounts
        $demoUsernames = ['headtefa', 'admincs', 'mekanik', 'kasir'];
        try {
            $found = DB::table('users')
                ->whereIn('username', $demoUsernames)
                ->pluck('username')
                ->toArray();
            if (! empty($found)) {
                $failures[] = 'Akun demo ditemukan: '.implode(', ', $found);
            }
        } catch (\Throwable $e) {
            $failures[] = 'Tidak dapat memeriksa akun demo: '.$e->getMessage();
        }

        if (empty($failures)) {
            $this->info('Production check passed.');

            return self::SUCCESS;
        }

        foreach ($failures as $failure) {
            $this->line("<fg=red>✗ FAIL</>  {$failure}");
        }
        $this->newLine();
        $this->error('Production check GAGAL. Perbaiki sebelum deploy.');

        return self::FAILURE;
    }

    private function checkPhpVersion(): array
    {
        $version = PHP_VERSION;
        $required = '8.4.1';
        $ok = version_compare($version, $required, '>=');

        return [
            'ok' => $ok,
            'message' => $ok ? "PHP {$version} >= {$required}" : "PHP {$version} < {$required} (required {$required}+)",
            'details' => "Current: {$version}",
        ];
    }

    private function checkExtensions(): array
    {
        $required = ['pdo_mysql', 'mbstring', 'openssl', 'fileinfo'];
        $optional = ['gd', 'exif'];
        $missing = [];
        $present = [];

        foreach ($required as $ext) {
            if (extension_loaded($ext)) {
                $present[] = $ext;
            } else {
                $missing[] = $ext.' (required)';
            }
        }

        foreach ($optional as $ext) {
            if (extension_loaded($ext)) {
                $present[] = $ext.' (optional)';
            } else {
                $present[] = $ext.' (optional, missing)';
            }
        }

        return [
            'ok' => empty($missing),
            'message' => empty($missing) ? 'All required extensions loaded' : 'Missing: '.implode(', ', $missing),
            'details' => 'Loaded: '.implode(', ', $present),
        ];
    }

    private function checkAppDebug(): array
    {
        $env = config('app.env');
        $debug = config('app.debug');
        $isProduction = $env === 'production';

        if ($isProduction && $debug) {
            return [
                'ok' => false,
                'message' => 'APP_DEBUG=true in production environment',
                'details' => 'Set APP_DEBUG=false in .env for production',
            ];
        }

        return [
            'ok' => true,
            'message' => 'APP_DEBUG correctly set',
            'details' => "APP_ENV={$env}, APP_DEBUG=".($debug ? 'true' : 'false'),
        ];
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            $driver = DB::connection()->getDriverName();

            return [
                'ok' => true,
                'message' => 'Database connection successful',
                'details' => "Driver: {$driver}",
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'message' => 'Database connection failed: '.$e->getMessage(),
            ];
        }
    }

    private function checkTriggers(): array
    {
        $required = ['trg_pkb_status_history', 'trg_prevent_negative_stock', 'trg_stock_movement_apply'];

        return $this->checkDbObjects('TRIGGERS', $required);
    }

    private function checkFunctions(): array
    {
        $required = ['fn_calculate_pkb_total', 'fn_documentation_complete'];

        return $this->checkDbObjects('FUNCTIONS', $required);
    }

    private function checkDbObjects(string $type, array $required): array
    {
        try {
            $driver = DB::connection()->getDriverName();
            if ($driver !== 'mysql') {
                return [
                    'ok' => true,
                    'message' => "Skipped {$type} check (not MySQL)",
                    'details' => 'Driver: '.DB::connection()->getDriverName(),
                ];
            }

            $column = $type === 'TRIGGERS' ? 'TRIGGER_NAME' : 'ROUTINE_NAME';
            $table = $type === 'TRIGGERS' ? 'TRIGGERS' : 'ROUTINES';
            $schemaColumn = $type === 'TRIGGERS' ? 'TRIGGER_SCHEMA' : 'ROUTINE_SCHEMA';
            $condition = $type === 'TRIGGERS' ? '' : "AND ROUTINE_TYPE = 'FUNCTION'";

            $existing = DB::select(
                "SELECT {$column} FROM INFORMATION_SCHEMA.{$table} WHERE {$schemaColumn} = DATABASE() {$condition}"
            );

            $existingNames = array_column($existing, $column);
            $missing = array_diff($required, $existingNames);

            if (empty($missing)) {
                return [
                    'ok' => true,
                    'message' => "All {$type} present",
                    'details' => 'Found: '.implode(', ', $existingNames),
                ];
            }

            return [
                'ok' => false,
                'message' => 'Missing '.$type.': '.implode(', ', $missing),
                'details' => 'Found: '.implode(', ', $existingNames),
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'message' => "Failed to check {$type}: ".$e->getMessage(),
            ];
        }
    }

    private function checkStorageLink(): array
    {
        $link = public_path('storage');
        $target = storage_path('app/public');

        if (! File::exists($link)) {
            return [
                'ok' => false,
                'message' => 'Storage link not found at public/storage',
                'details' => 'Run: php artisan storage:link',
            ];
        }

        // On Windows, storage:link creates a junction (mount point) which is a reparse point
        // is_link() returns false for junctions on Windows, so we check the real path
        $realTarget = realpath($link);
        $expectedTarget = realpath($target);

        if ($realTarget !== $expectedTarget) {
            return [
                'ok' => false,
                'message' => 'Storage link points to wrong target',
                'details' => "Current: {$realTarget}, Expected: {$expectedTarget}",
            ];
        }

        return [
            'ok' => true,
            'message' => 'Storage link valid',
            'details' => "Points to: {$target}",
        ];
    }

    private function checkWritable(string $path): array
    {
        $fullPath = base_path($path);

        if (! File::isDirectory($fullPath)) {
            return [
                'ok' => false,
                'message' => "Directory not found: {$path}",
            ];
        }

        $testFile = $fullPath.'/.write_test_'.uniqid();
        try {
            File::put($testFile, 'test');
            File::delete($testFile);

            return [
                'ok' => true,
                'message' => "{$path} is writable",
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'message' => "{$path} is not writable: ".$e->getMessage(),
            ];
        }
    }
}
