<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Always create roles and permissions first
        $this->call(\Database\Seeders\TefaAccessSeeder::class);

        if (App::environment('production')) {
            $this->call(ProductionSeeder::class);
        } else {
            $this->call(DemoSeeder::class);
        }
    }
}