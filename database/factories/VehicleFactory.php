<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'plate_number' => strtoupper(fake()->bothify('?? #### ??')),
            'brand' => fake()->randomElement(['Toyota', 'Honda', 'Suzuki', 'Daihatsu', 'Mitsubishi']),
            'model' => fake()->word(),
            'year' => fake()->numberBetween(2010, 2024),
            'color' => fake()->safeColorName(),
            'vin' => fake()->optional()->bothify('?????????????????'),
            'engine_number' => fake()->optional()->bothify('##########'),
            'current_km' => fake()->numberBetween(0, 200000),
            'is_active' => true,
        ];
    }
}