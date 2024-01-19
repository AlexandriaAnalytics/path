<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Institute;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            LevelSeeder::class,
        ]);

        // \App\Models\User::factory(10)->create();

        \App\Models\User::factory()
            ->has(
                Institute::factory(3)
                    ->hasStudents(10),
            )
            ->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
    }
}
