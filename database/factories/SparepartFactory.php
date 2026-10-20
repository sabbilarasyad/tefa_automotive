<?php

namespace Database\Factories;

use App\Models\Sparepart;
use Illuminate\Database\Eloquent\Factories\Factory;

class SparepartFactory extends Factory
{
    protected $model = Sparepart::class;

    public function definition(): array
    {
        return [
            'code' => 'SP-' . fake()->unique()->bothify('####'),
            'name' => fake()->words(3, true),
            'unit' => fake()->randomElement(['pcs', 'set', 'box', 'liter']),
            'selling_price' => fake()->numberBetween(10000, 500000),
            'cost_price' => fake()->numberBetween(5000, 300000),
            'stock_qty' => fake()->numberBetween(0, 100),
            'min_stock' => fake()->numberBetween(0, 10),
            'documentation_required' => fake()->boolean(),
            'is_active' => true,
        ];
    }
}