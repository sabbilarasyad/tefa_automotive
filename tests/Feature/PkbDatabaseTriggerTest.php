<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Pkb;
use App\Models\Sparepart;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PkbDatabaseTriggerTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_change_is_recorded_automatically(): void
    {
        $this->seed();
        $creator = User::where('username', 'admincs')->firstOrFail();
        $customer = Customer::create(['name' => 'Test Customer', 'phone' => '081234567890']);
        $vehicle = Vehicle::create([
            'customer_id' => $customer->id,
            'plate_number' => 'B 1234 TEST',
            'brand' => 'Toyota',
            'model' => 'Avanza',
        ]);
        $pkb = Pkb::create([
            'number' => 'PKB/2026/09/0001',
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'created_by' => $creator->id,
            'complaint' => 'Mesin berisik',
            'status' => 'WAITING_MECHANIC',
            'service_date' => now(),
        ]);

        DB::table('pkb')->where('id', $pkb->id)->update(['status' => 'IN_PROGRESS']);

        $this->assertDatabaseHas('pkb_status_histories', [
            'pkb_id' => $pkb->id,
            'from_status' => 'WAITING_MECHANIC',
            'to_status' => 'IN_PROGRESS',
        ]);
    }

    public function test_stock_movement_updates_stock_and_negative_stock_is_rejected(): void
    {
        $this->seed();
        $creator = User::where('username', 'admincs')->firstOrFail();
        $sparepart = Sparepart::create([
            'code' => 'OIL-TEST',
            'name' => 'Oli Test',
            'stock_qty' => 2,
        ]);

        DB::table('stock_movements')->insert([
            'sparepart_id' => $sparepart->id,
            'type' => 'RETURN',
            'qty_in' => 1,
            'qty_out' => 0,
            'stock_before' => 2,
            'stock_after' => 3,
            'created_by' => $creator->id,
            'created_at' => now(),
        ]);

        $this->assertDatabaseHas('spareparts', ['id' => $sparepart->id, 'stock_qty' => 3]);

        try {
            $sparepart->update(['stock_qty' => -1]);
            $this->fail('Negative stock should be rejected by the database trigger.');
        } catch (QueryException) {
            $this->assertDatabaseHas('spareparts', ['id' => $sparepart->id, 'stock_qty' => 3]);
        }
    }
}
