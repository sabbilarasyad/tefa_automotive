<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Pkb;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class PkbFactory extends Factory
{
    protected $model = Pkb::class;

    public function definition(): array
    {
        return [
            'number' => 'PKB-' . date('Ymd') . '-' . fake()->unique()->numberBetween(1000, 9999),
            'customer_id' => Customer::factory(),
            'vehicle_id' => Vehicle::factory(),
            'created_by' => \App\Models\User::factory(),
            'mechanic_id' => null,
            'complaint' => fake()->sentence(),
            'inspection_result' => fake()->optional()->paragraph(),
            'status' => 'WAITING_MECHANIC',
            'service_date' => now(),
            'estimated_total' => 0,
            'final_total' => 0,
            'started_at' => null,
            'completed_at' => null,
            'cancel_reason' => null,
        ];
    }
}