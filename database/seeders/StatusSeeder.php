<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Unpaid',
            ],
            [
                'name' => 'Paid',
            ],
            [
                'name' => 'Cancelled',
            ],
            [
                'name' => 'paying'
            ]

        ];

        Status::factory()
            ->count(count($data))
            ->sequence(...$data)
            ->create();
    }
}
