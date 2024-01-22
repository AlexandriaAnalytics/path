<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InstituteTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $instituteTypes = [
            'Affiliate Member',
            'Associate Member',
            'Approved Exam Centre',
            'Premium Exam Centre',
        ];

        foreach ($instituteTypes as $instituteType) {
            \App\Models\InstituteType::factory()->create([
                'name' => $instituteType,
            ]);
        }
    }
}
