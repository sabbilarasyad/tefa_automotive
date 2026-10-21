<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use RuntimeException;

class RestoreCommand extends Command
{
    protected $signature = 'tefa:restore {file : Backup file name in storage/app/backups} {--force : Skip confirmation prompt}';
    protected $description = 'Restore database + storage/app/public from a backup file in storage/app/backups';

    public function handle(): int
    {
        $file = $this->argument('file');
        $force = $this->option('force');

        $backupPath = storage_path("app/backups/{$file}");

        if (! File::exists($backupPath)) {
            $this->error("Backup file not found: {$backupPath}");
            return self::FAILURE;
        }

        if (! $force) {
            if (! $this->confirm("This will REPLACE the current database and storage/app/public. Continue?")) {
                $this->info('Restore cancelled.');
                return self::SUCCESS;
            }
        }

        $tempDir = storage_path('app/temp/restore_' . now()->format('Ymd_His'));
        File::makeDirectory($tempDir, 0755, true, true);

        $this->info("Restoring from: {$file}");

        // 1. Extract archive
        $this->extractArchive($backupPath, $tempDir);

        // 2. Read manifest
        $manifestPath = "{$tempDir}/manifest.json";
        if (! File::exists($manifestPath)) {
            $this->error('Invalid backup: manifest.json not found');
            return self::FAILURE;
        }

        $manifest = json_decode(File::get($manifestPath), true);

        // 3. Restore database
        $this->restoreDatabase("{$tempDir}/database.sql");

        // 4. Restore storage/app/public
        $publicDir = storage_path('app/public');
        $backupPublicDir = "{$tempDir}/storage_app_public";
        if (File::isDirectory($backupPublicDir)) {
            $this->restorePublicDirectory($backupPublicDir, $publicDir);
        }

        // 5. Cleanup
        File::deleteDirectory($tempDir);

        $this->info('Restore completed successfully.');
        return self::SUCCESS;
    }

    private function extractArchive(string $archivePath, string $destination): void
    {
        $this->info('Extracting archive...');

        $extension = pathinfo($archivePath, PATHINFO_EXTENSION);
        $isGz = $extension === 'gz';

        $tarArgs = ['-xf', $archivePath, '-C', $destination, '--strip-components=1'];
        if ($isGz) {
            $tarArgs = ['-xzf', $archivePath, '-C', $destination, '--strip-components=1'];
        }

        $result = Process::timeout(300)->run(['tar', ...$tarArgs]);

        if ($result->failed()) {
            throw new RuntimeException("tar extract failed: " . $result->errorOutput());
        }

        if (! File::exists("{$destination}/manifest.json")) {
            throw new RuntimeException('Extracted archive does not contain manifest.json');
        }
    }

    private function restoreDatabase(string $sqlFile): void
    {
        if (! File::exists($sqlFile)) {
            throw new RuntimeException('Database dump not found in backup');
        }

        $config = config('database.connections.' . config('database.default'));
        $driver = $config['driver'] ?? 'mysql';

        if ($driver === 'sqlite') {
            $database = $config['database'] ?? database_path('database.sqlite');
            File::copy($sqlFile, $database);
            $this->info('SQLite database restored.');
            return;
        }

        if ($driver !== 'mysql') {
            throw new RuntimeException("Unsupported database driver for restore: {$driver}");
        }

        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 3306;
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'] ?? '';

        // Get mysql path from config/env
        $mysqlPath = config('database.mysql_path', 'mysql');

        $cmd = [$mysqlPath];
        $cmd[] = "--host={$host}";
        $cmd[] = "--port={$port}";
        $cmd[] = "--user={$username}";
        if ($password !== '') {
            $cmd[] = "--password={$password}";
        }
        $cmd[] = $database;

        $this->info("Restoring database using: {$mysqlPath}...");
        $result = Process::timeout(120)->input(File::get($sqlFile))->run($cmd);

        if ($result->failed()) {
            throw new RuntimeException("mysql restore failed: " . $result->errorOutput());
        }

        $this->info('Database restored.');
    }

    private function restorePublicDirectory(string $sourceDir, string $destinationDir): void
    {
        $this->info('Restoring storage/app/public...');

        if (File::isDirectory($destinationDir)) {
            File::deleteDirectory($destinationDir);
        }
        File::makeDirectory($destinationDir, 0755, true, true);

        foreach (File::allFiles($sourceDir) as $file) {
            $relativePath = $file->getRelativePathname();
            $destFile = "{$destinationDir}/{$relativePath}";
            File::ensureDirectoryExists(dirname($destFile));
            File::copy($file->getRealPath(), $destFile);
        }

        $this->info('Public directory restored.');
    }
}