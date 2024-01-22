<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Exam;
use App\Models\Institute;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            InstituteTypeSeeder::class,
        ]);

        $this->call([
            LevelSeeder::class,
        ]);

        $exams = Exam::factory(10)
            ->create();

        User::factory()
            ->has(
                Institute::factory(3)
                    ->has(
                        Student::factory(10)
                            ->hasAttached($exams->random(3)),
                    )
            )
            ->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);


        User::factory(10)
            ->has(
                Institute::factory(3)
                    ->has(
                        Student::factory(10)
                            ->hasAttached($exams->random(3)),
                    )
            )
            ->create();
    }
}
