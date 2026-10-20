<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return [
            'code' => 'SV-' . fake()->unique()->bothify('####'),
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'price' => fake()->numberBetween(50000, 1000000),
            'documentation_required' => fake()->boolean(),
            'is_active' => true,
        ];
    }
}