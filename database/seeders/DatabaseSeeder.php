<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Country;
use App\Models\Exam;
use App\Models\Institute;
use App\Models\Module;
use App\Models\Status;
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
            PaymentMethodSeeder::class,
            CountrySeeder::class,
            InstituteTypeSeeder::class,
            PeriodSeeder::class,
            LevelSeeder::class,
            ModuleSeeder::class,
            StatusSeeder::class
        ]);



        $countries = Country::all();
        $modules = Module::all();

        foreach($modules as $module){
            foreach($countries as $country){
                $module->countries()->attach($country, ['price' => rand(100, 1000)]);
            }
            $module->save();
        }

        $statuses = Status::all();

        $exams = Exam::factory(10)
            ->create()
            ->each(function (Exam $exam) use ($modules): void {
                $exam->modules()->attach($modules->random(3));
            });

        User::factory()
            ->has(
                Institute::factory(3)
                    ->afterCreating(function (Institute $institute, User $user): void {
                        $institute->owner()->associate($user);
                        $institute->save();
                    })
                    ->has(
                        Student::factory(10)
                        //->hasAttached($exams->random(3)),
                    )

            )
            ->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);


        User::factory(10)
            ->has(
                Institute::factory(3)
                    ->afterCreating(function (Institute $institute): void {
                        $institute->owner()->associate(User::all()->random());
                        $institute->save();
                    })
                    ->has(
                        Student::factory(10)
                        //->hasAttached($exams->random(3)),
                    )
            )
            ->create();
    }
}
