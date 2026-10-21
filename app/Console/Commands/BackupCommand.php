<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use RuntimeException;

#[Signature('tefa:backup {--compress : Compress the backup using gzip} {--keep=7 : Number of recent backups to keep}')]
#[Description('Dump database + zip storage/app/public to storage/app/backups, keep N latest')]
class BackupCommand extends Command
{
    protected $signature = 'tefa:backup {--compress : Compress the backup using gzip} {--keep=7 : Number of recent backups to keep}';
    protected $description = 'Dump database + zip storage/app/public to storage/app/backups, keep N latest';

    public function handle(): int
    {
        $compress = $this->option('compress');
        $keep = (int) $this->option('keep');

        $timestamp = now()->format('Ymd_His');
        $backupName = "backup_{$timestamp}";
        $backupDir = storage_path('app/backups');

        if (! File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true, true);
        }

        $tempDir = storage_path("app/temp/{$backupName}");
        File::makeDirectory($tempDir, 0755, true, true);

        $this->info("Creating backup: {$backupName}");

        // 1. Dump database
        $dbFile = "{$tempDir}/database.sql";
        $this->dumpDatabase($dbFile);

        // 2. Copy storage/app/public
        $publicDir = storage_path('app/public');
        $backupPublicDir = "{$tempDir}/storage_app_public";
        if (File::isDirectory($publicDir)) {
            $this->copyDirectory($publicDir, $backupPublicDir);
        }

        // 3. Create manifest
        $manifest = [
            'name' => $backupName,
            'created_at' => now()->toIso8601String(),
            'database' => 'database.sql',
            'storage_app_public' => 'storage_app_public/',
            'compressed' => $compress,
        ];
        File::put("{$tempDir}/manifest.json", json_encode($manifest, JSON_PRETTY_PRINT));

        // 4. Create archive
        $archiveName = $compress ? "{$backupName}.tar.gz" : "{$backupName}.tar";
        $archivePath = "{$backupDir}/{$archiveName}";

        $this->createArchive($tempDir, $archivePath, $compress);

        // 5. Cleanup temp
        File::deleteDirectory($tempDir);

        $this->info("Backup created: {$archivePath}");

        // 6. Rotate old backups
        $this->rotateBackups($backupDir, $keep);

        return self::SUCCESS;
    }

    private function dumpDatabase(string $outputFile): void
    {
        $config = config('database.connections.' . config('database.default'));
        $driver = $config['driver'] ?? 'mysql';

        if ($driver === 'sqlite') {
            $database = $config['database'] ?? database_path('database.sqlite');
            File::copy($database, $outputFile);
            return;
        }

        if ($driver !== 'mysql') {
            throw new RuntimeException("Unsupported database driver for backup: {$driver}");
        }

        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 3306;
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'] ?? '';

        // Get mysqldump path from config/env
        $mysqldumpPath = config('database.mysqldump_path', 'mysqldump');

        $cmd = [$mysqldumpPath];
        $cmd[] = "--host={$host}";
        $cmd[] = "--port={$port}";
        $cmd[] = "--user={$username}";
        $cmd[] = "--single-transaction";
        $cmd[] = "--routines";
        $cmd[] = "--triggers";
        $cmd[] = "--events";
        if ($password !== '') {
            $cmd[] = "--password={$password}";
        }
        $cmd[] = $database;

        $this->info("Dumping database using: {$mysqldumpPath}...");
        $result = Process::timeout(120)->run($cmd, fn ($type, $output) => File::append($outputFile, $output));

        if ($result->failed()) {
            throw new RuntimeException("mysqldump failed: " . $result->errorOutput());
        }

        if (! File::exists($outputFile) || File::size($outputFile) === 0) {
            throw new RuntimeException('Database dump is empty');
        }
    }

    private function copyDirectory(string $source, string $destination): void
    {
        if (! File::isDirectory($destination)) {
            File::makeDirectory($destination, 0755, true, true);
        }

        foreach (File::allFiles($source) as $file) {
            $relativePath = $file->getRelativePathname();
            $destFile = "{$destination}/{$relativePath}";
            File::ensureDirectoryExists(dirname($destFile));
            File::copy($file->getRealPath(), $destFile);
        }
    }

    private function createArchive(string $sourceDir, string $outputPath, bool $compress): void
    {
        $this->info('Creating archive...');

        $tarArgs = ['-cf', $outputPath, '-C', dirname($sourceDir), basename($sourceDir)];
        if ($compress) {
            $tarArgs = ['-czf', $outputPath, '-C', dirname($sourceDir), basename($sourceDir)];
        }

        $result = Process::timeout(300)->run(['tar', ...$tarArgs]);

        if ($result->failed()) {
            throw new RuntimeException("tar failed: " . $result->errorOutput());
        }

        if (! File::exists($outputPath) || File::size($outputPath) === 0) {
            throw new RuntimeException('Archive is empty');
        }
    }

    private function rotateBackups(string $backupDir, int $keep): void
    {
        $backups = collect(File::files($backupDir))
            ->filter(fn ($file) => Str::startsWith($file->getFilename(), 'backup_'))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->values();

        $toDelete = $backups->slice($keep);
        foreach ($toDelete as $file) {
            File::delete($file->getRealPath());
            $this->line("Removed old backup: {$file->getFilename()}");
        }

        if ($toDelete->isNotEmpty()) {
            $this->info("Rotated backups, kept {$keep} latest.");
        }
    }
}