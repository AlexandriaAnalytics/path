<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Period::factory(3)
            ->sequence(
                [
                    'starts_at' => now()->subYear(),
                    'ends_at' => now()->subYear()->addMonth(),
                ],
                [
                    'starts_at' => now()->subMonth(),
                    'ends_at' => now()->addMonth(),
                ],
                [
                    'starts_at' => now()->addMonth(),
                    'ends_at' => now()->addYear(),
                ],
            )
            ->create();
    }
}
