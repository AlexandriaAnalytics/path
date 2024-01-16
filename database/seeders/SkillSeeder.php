<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'S',
            ],
            [
                'name' => 'L',
            ],
            [
                'name' => 'RW',
            ],
        ];

        Skill::factory(count($data))
            ->sequence(...$data)
            ->create();
    }
}
