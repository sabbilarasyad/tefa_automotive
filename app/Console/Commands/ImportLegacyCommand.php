<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class ImportLegacyCommand extends Command
{
    protected $signature = 'tefa:import-legacy
        {--path=storage/app/legacy : Folder berisi customers.csv, vehicles.csv, services.csv, spareparts.csv}
        {--simpan : Simpan permanen. Tanpa opsi ini hanya uji coba (semua dibatalkan di akhir)}';

    protected $description = 'Impor data lama client (pelanggan, kendaraan, jasa, sparepart) dari CSV. Data yang sudah ada dilewati, tidak ditimpa.';

    public function handle(): int
    {
        $dir = base_path($this->option('path'));
        $simpan = (bool) $this->option('simpan');

        foreach (['customers', 'vehicles', 'services', 'spareparts'] as $name) {
            if (! is_file("{$dir}/{$name}.csv")) {
                $this->error("File tidak ditemukan: {$dir}/{$name}.csv");

                return self::FAILURE;
            }
        }

        $stat = [];
        foreach (['pelanggan', 'kendaraan', 'jasa', 'sparepart'] as $item) {
            $stat[$item] = ['baru' => 0, 'dilewati' => 0];
        }

        DB::beginTransaction();

        try {
            $now = now();

            foreach ($this->rows("{$dir}/customers.csv") as $r) {
                $exists = DB::table('customers')->where('name', $r['name'])->where('phone', $r['phone'])->exists();
                if ($exists) {
                    $stat['pelanggan']['dilewati']++;

                    continue;
                }
                DB::table('customers')->insert([
                    'name' => $r['name'],
                    'phone' => $r['phone'],
                    'email' => $this->val($r, 'email'),
                    'address' => $this->val($r, 'address'),
                    'notes' => $this->val($r, 'notes'),
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $stat['pelanggan']['baru']++;
            }

            foreach ($this->rows("{$dir}/vehicles.csv") as $r) {
                if (DB::table('vehicles')->where('plate_number', $r['plate_number'])->exists()) {
                    $stat['kendaraan']['dilewati']++;

                    continue;
                }
                $customerId = DB::table('customers')
                    ->where('name', $r['customer_name'])
                    ->where('phone', $r['customer_phone'])
                    ->value('id');
                if (! $customerId) {
                    throw new RuntimeException("Pelanggan tidak ditemukan untuk kendaraan {$r['plate_number']}");
                }
                DB::table('vehicles')->insert([
                    'customer_id' => $customerId,
                    'plate_number' => $r['plate_number'],
                    'brand' => $r['brand'],
                    'model' => $r['model'],
                    'year' => $this->val($r, 'year'),
                    'color' => $this->val($r, 'color'),
                    'vin' => $this->val($r, 'vin'),
                    'engine_number' => $this->val($r, 'engine_number'),
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $stat['kendaraan']['baru']++;
            }

            foreach ($this->rows("{$dir}/services.csv") as $r) {
                if (DB::table('services')->where('code', $r['code'])->exists()) {
                    $stat['jasa']['dilewati']++;

                    continue;
                }
                DB::table('services')->insert([
                    'code' => $r['code'],
                    'name' => $r['name'],
                    'price' => $r['price'],
                    'documentation_required' => false,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $stat['jasa']['baru']++;
            }

            foreach ($this->rows("{$dir}/spareparts.csv") as $r) {
                if (DB::table('spareparts')->where('code', $r['code'])->exists()) {
                    $stat['sparepart']['dilewati']++;

                    continue;
                }
                DB::table('spareparts')->insert([
                    'code' => $r['code'],
                    'name' => $r['name'],
                    'unit' => 'pcs',
                    'selling_price' => $r['price'],
                    'cost_price' => 0,
                    'stock_qty' => 0,
                    'min_stock' => 0,
                    'documentation_required' => false,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $stat['sparepart']['baru']++;
            }

            if ($simpan) {
                DB::commit();
            } else {
                DB::rollBack();
            }
        } catch (Throwable $e) {
            DB::rollBack();
            $this->error('GAGAL, semua perubahan dibatalkan (rollback): '.$e->getMessage());

            return self::FAILURE;
        }

        $this->table(
            ['Data', 'Baru', 'Dilewati (sudah ada)'],
            collect($stat)->map(fn (array $v, string $k): array => [$k, $v['baru'], $v['dilewati']])->values()->all()
        );
        $this->info($simpan
            ? 'SELESAI: data disimpan permanen (commit).'
            : 'UJI COBA: tidak ada yang disimpan (rollback). Jalankan lagi dengan --simpan untuk menyimpan.');

        return self::SUCCESS;
    }

    /** @return \Generator<int, array<string, string>> */
    private function rows(string $file): \Generator
    {
        $handle = fopen($file, 'r');
        if ($handle === false) {
            throw new RuntimeException("Tidak bisa membuka {$file}");
        }

        $header = fgetcsv($handle, 0, ',', '"', '\\');
        $header[0] = ltrim((string) $header[0], "\xEF\xBB\xBF");

        while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            if ($row === [null]) {
                continue;
            }
            if (count($row) !== count($header)) {
                throw new RuntimeException("Jumlah kolom tidak cocok di {$file}");
            }
            yield array_combine($header, $row);
        }

        fclose($handle);
    }

    /** @param array<string, string> $row */
    private function val(array $row, string $key): ?string
    {
        $v = trim($row[$key] ?? '');

        return $v === '' ? null : $v;
    }
}
