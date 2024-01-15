<?php

namespace Database\Seeders;

use App\Models\Level;
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
                'slug' => 'ready',
                'description' => 'Ready',
            ],
            [
                'name' => 'Steady',
                'slug' => 'steady',
                'description' => 'Steady',
            ],
            [
                'name' => 'Go!',
                'slug' => 'go',
                'description' => 'Go!',
            ],
            [
                'name' => 'A1- SPA level I',
                'slug' => 'a1-spa-level-i',
                'description' => 'A1- SPA level I',
            ],
            [
                'name' => 'A1 SPA level II',
                'slug' => 'a1-spa-level-ii',
                'description' => 'A1 SPA level II',
            ],
            [
                'name' => 'A1+ SPA level III',
                'slug' => 'a1-spa-level-iii',
                'description' => 'A1+ SPA level III',
            ],
            [
                'name' => 'A1- Entry',
                'slug' => 'a1-entry',
                'description' => 'A1- Entry',
            ],
            [
                'name' => 'A1 Access',
                'slug' => 'a1-access',
                'description' => 'A1 Access',
            ],
            [
                'name' => 'A1+ Achiever',
                'slug' => 'a1-achiever',
                'description' => 'A1+ Achiever',
            ],
            [
                'name' => 'A2 Preliminary',
                'slug' => 'a2-preliminary',
                'description' => 'A2 Preliminary',
            ],
            [
                'name' => 'A2+ Elementary',
                'slug' => 'a2-elementary',
                'description' => 'A2+ Elementary',
            ],
            [
                'name' => 'B1 Progress',
                'slug' => 'b1-progress',
                'description' => 'B1 Progress',
            ],
            [
                'name' => 'B1+ Onwards',
                'slug' => 'b1-onwards',
                'description' => 'B1+ Onwards',
            ],
            [
                'name' => 'B2 Competency',
                'slug' => 'b2-competency',
                'description' => 'B2 Competency',
            ],
            [
                'name' => 'B2+ Forward',
                'slug' => 'b2-forward',
                'description' => 'B2+ Forward',
            ],
            [
                'name' => 'Tourism',
                'slug' => 'tourism',
                'description' => 'Tourism',
            ],
            [
                'name' => 'Hotel Management & Hospitality',
                'slug' => 'hotel-management-hospitality',
                'description' => 'Hotal Management & Hospitality',
            ],
            [
                'name' => 'C1 | C1+ | C2 Multi-level Proficiency Test of English',
                'slug' => 'c1-c1-c2-multi-level-proficiency-test-of-english',
                'description' => 'C1 | C1+ | C2 Multi-level Proficiency Test of English',
            ],
        ];

        Level::factory()
            ->count(count($data))
            ->sequence(...$data)
            ->create();
    }
}
