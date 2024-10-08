<?php

namespace Database\Seeders;

use App\Models\Level;
use App\Models\Module;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Ready',
                'description' => 'Ready',
            ],
            [
                'name' => 'Steady',
                'description' => 'Steady',
            ],
            [
                'name' => 'Go!',
                'description' => 'Go!',
            ],
            [
                'name' => 'A1- SPA level I',
                'description' => 'A1- SPA level I',
            ],
            [
                'name' => 'A1 SPA level II',
                'description' => 'A1 SPA level II',
            ],
            [
                'name' => 'A1+ SPA level III',
                'description' => 'A1+ SPA level III',
            ],
            [
                'name' => 'A1- Entry',
                'description' => 'A1- Entry',
            ],
            [
                'name' => 'A1 Access',
                'description' => 'A1 Access',
            ],
            [
                'name' => 'A1+ Achiever',
                'description' => 'A1+ Achiever',
            ],
            [
                'name' => 'A2 Preliminary',
                'description' => 'A2 Preliminary',
            ],
            [
                'name' => 'A2+ Elementary',
                'description' => 'A2+ Elementary',
            ],
            [
                'name' => 'B1 Progress',
                'description' => 'B1 Progress',
            ],
            [
                'name' => 'B1+ Onwards',
                'description' => 'B1+ Onwards',
            ],
            [
                'name' => 'B2 Competency',
                'description' => 'B2 Competency',
            ],
            [
                'name' => 'B2+ Forward',
                'description' => 'B2+ Forward',
            ],
            [
                'name' => 'Tourism',
                'description' => 'Tourism',
            ],
            [
                'name' => 'Hotel Management & Hospitality',
                'description' => 'Hotal Management & Hospitality',
            ],
            [
                'name' => 'C1 | C1+ | C2 Multi-level Proficiency Test of English',
                'description' => 'C1 | C1+ | C2 Multi-level Proficiency Test of English',
            ],
        ];

        foreach ($data as $level) {
            Level::create($level);
        }

        // $levels->each(fn (Level $level) => $level->modules()->attach(Module::all()));
    }
}
