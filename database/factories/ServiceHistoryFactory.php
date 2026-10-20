<?php

namespace Database\Factories;

use App\Models\ServiceHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceHistoryFactory extends Factory
{
    protected $model = ServiceHistory::class;

    public function definition(): array
    {
        return [
            'pkb_id' => \App\Models\Pkb::factory(),
            'vehicle_id' => \App\Models\Vehicle::factory(),
            'service_date' => now(),
            'km' => fake()->numberBetween(0, 200000),
            'summary' => fake()->optional()->paragraph(),
            'total_amount' => fake()->numberBetween(50000, 5000000),
            'created_at' => now(),
        ];
    }
}