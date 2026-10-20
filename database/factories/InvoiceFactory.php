<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Pkb;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'number' => 'INV-' . date('Ymd') . '-' . fake()->unique()->numberBetween(1000, 9999),
            'pkb_id' => Pkb::factory(),
            'subtotal' => 0,
            'discount' => 0,
            'tax' => 0,
            'grand_total' => 0,
            'status' => 'UNPAID',
            'issued_at' => now(),
            'paid_at' => null,
        ];
    }
}