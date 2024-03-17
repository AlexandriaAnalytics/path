<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Reading and writing',
            ],
            [
                'name' => 'Listening',
            ],
            [
                'name' => 'Speaking',
            ],
        ];

        foreach ($data as $module) {
            Module::create($module);
        }
    }
}
