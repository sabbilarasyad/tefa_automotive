<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Role;
use App\Models\Service;
use App\Models\Sparepart;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Skip interactive prompt in tests or when --force is used
        if ($this->command && ! $this->command->option('force') && app()->environment() !== 'testing') {
            $this->command->warn('⚠️  DemoSeeder hanya untuk development/staging. JANGAN dijalankan di produksi!');
            if (! $this->command->confirm('Lanjutkan?')) {
                $this->command->info('Dibatalkan.');
                return;
            }
        }

        // Call ProductionSeeder first for roles, permissions, SUPER_ADMIN
        $this->call(ProductionSeeder::class);

        $roles = Role::all()->keyBy('name');

        // Demo accounts
        $demoAccounts = [
            ['SUPER_ADMIN', 'superadmin', 'Super Administrator'],
            ['HEAD_TEFA', 'headtefa', 'Kepala TEFA'],
            ['ADMIN_CS', 'admincs', 'Admin Customer Service'],
            ['MEKANIK', 'mekanik', 'Mekanik Demo'],
            ['KASIR', 'kasir', 'Kasir Demo'],
        ];

        foreach ($demoAccounts as [$roleName, $username, $name]) {
            User::updateOrCreate(
                ['username' => $username],
                [
                    'role_id' => $roles[$roleName]->id,
                    'name' => $name,
                    'email' => $username . '@tefa.local',
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]
            );
        }

        // In testing environment, create all reference data but skip interactive prompt
        if (app()->environment('testing')) {
            $this->command?->info('Demo accounts seeded successfully (testing mode).');
            // Continue to create all reference data for tests
        }

        // Demo customers
        $customers = [
            ['Budi Santoso', '081234567890', 'budi@example.com', 'Jl. Merdeka No. 10', 'Pelanggan lama'],
            ['Siti Rahayu', '081234567891', 'siti@example.com', 'Jl. Sudirman No. 25', ''],
            ['Ahmad Wijaya', '081234567892', 'ahmad@example.com', 'Jl. Thamrin No. 5', 'Mobil Toyota'],
            ['Dewi Lestari', '081234567893', 'dewi@example.com', 'Jl. Gatot Subroto No. 12', ''],
            ['Rudi Hartono', '081234567894', 'rudi@example.com', 'Jl. Diponegoro No. 8', ''],
        ];

        foreach ($customers as [$name, $phone, $email, $address, $notes]) {
            Customer::updateOrCreate(
                ['phone' => $phone],
                compact('name', 'phone', 'email', 'address', 'notes') + ['is_active' => true]
            );
        }

        $customerBudi = Customer::where('phone', '081234567890')->first();
        $customerSiti = Customer::where('phone', '081234567891')->first();
        $customerAhmad = Customer::where('phone', '081234567892')->first();

        // Demo vehicles
        $vehicles = [
            [$customerBudi, 'B 1234 ABC', 'Honda', 'Civic', 2020, 'Hitam', '1HGCM82633A123456', 'L15A7', 45000],
            [$customerBudi, 'B 5678 DEF', 'Toyota', 'Avanza', 2019, 'Putih', 'MHTB4AA4001234567', '1NR-VE', 62000],
            [$customerSiti, 'B 9012 GHI', 'Suzuki', 'Ertiga', 2021, 'Silver', 'MHTG8AA4002345678', 'K15B', 28000],
            [$customerAhmad, 'B 3456 JKL', 'Daihatsu', 'Xenia', 2018, 'Merah', 'MHKR5AA4003456789', '2NR-VE', 78000],
        ];

        foreach ($vehicles as [$customer, $plate, $brand, $model, $year, $color, $vin, $engine, $km]) {
            Vehicle::updateOrCreate(
                ['plate_number' => $plate],
                [
                    'customer_id' => $customer->id,
                    'brand' => $brand,
                    'model' => $model,
                    'year' => $year,
                    'color' => $color,
                    'vin' => $vin,
                    'engine_number' => $engine,
                    'current_km' => $km,
                    'is_active' => true,
                ]
            );
        }

        // Demo services
        $services = [
            ['SRV-001', 'Servis Berkala', 'Ganti oli, filter oli, cek umum', 200000, true],
            ['SRV-002', 'Ganti Oli Mesin', 'Ganti oli mesun dan filter oli', 150000, true],
            ['SRV-003', 'Tune Up', 'Ganti busi, setel klep, cek sistem kelistrikan', 350000, true],
            ['SRV-004', 'Ganti Kampas Rem', 'Ganti kampas rem depan/belakang', 400000, true],
            ['SRV-005', 'Cek AC', 'Cek freon, kompresor, blower', 100000, false],
        ];

        foreach ($services as [$code, $name, $desc, $price, $docReq]) {
            Service::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'description' => $desc,
                    'price' => $price,
                    'documentation_required' => $docReq,
                    'is_active' => true,
                ]
            );
        }

        // Demo spareparts
        $spareparts = [
            ['PRT-001', 'Oli Mesin 1L', 'pcs', 150000, 100000, 50, 5, true],
            ['PRT-002', 'Filter Oli', 'pcs', 50000, 30000, 30, 3, true],
            ['PRT-003', 'Busi NGK', 'pcs', 75000, 45000, 20, 2, true],
            ['PRT-004', 'Kampas Rem Depan', 'set', 400000, 250000, 10, 1, true],
            ['PRT-005', 'Kampas Rem Belakang', 'set', 350000, 220000, 10, 1, true],
            ['PRT-006', 'Filter Udara', 'pcs', 80000, 50000, 15, 2, false],
            ['PRT-007', 'Oli Transmisi 1L', 'pcs', 120000, 80000, 10, 1, false],
            ['PRT-008', 'Cairan Rem DOT 4', 'pcs', 60000, 40000, 20, 2, false],
        ];

        foreach ($spareparts as [$code, $name, $unit, $sell, $cost, $stock, $min, $docReq]) {
            Sparepart::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'unit' => $unit,
                    'selling_price' => $sell,
                    'cost_price' => $cost,
                    'stock_qty' => $stock,
                    'min_stock' => $min,
                    'documentation_required' => $docReq,
                    'is_active' => true,
                ]
            );
        }

        $this->command?->info('Demo data seeded successfully.');
    }
}