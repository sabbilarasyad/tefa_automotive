<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Pkb;
use App\Models\Reminder;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReminderFactory extends Factory
{
    protected $model = Reminder::class;

    public function definition(): array
    {
        return [
            'pkb_id' => Pkb::factory(),
            'vehicle_id' => Vehicle::factory(),
            'customer_id' => Customer::factory(),
            'reminder_date' => now()->toDateString(),
            'type' => 'SERVICE_6_MONTHS',
            'status' => 'PENDING',
            'sent_at' => null,
            'message' => null,
        ];
    }
}