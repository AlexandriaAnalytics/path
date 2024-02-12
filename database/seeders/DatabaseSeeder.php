<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Country;
use App\Models\Exam;
use App\Models\Institute;
use App\Models\Level;
use App\Models\Module;
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

        foreach ($modules as $module) {
            foreach ($countries as $country) {
                $module->countries()->attach($country, ['price' => rand(100, 1000)]);
            }
            $module->save();
        }

        $levels = Level::all();
        foreach ($levels as $level) {
            foreach ($countries as $country) {
                $level->countries()->attach($country, [
                    'price_discounted' => rand(1000, 5000),
                    'price_right_exam' => rand(1000, 5000),
                ]);
            }
            $level->save();
        }

        Exam::factory(10)
            ->create()
            ->each(function (Exam $exam) use ($modules): void {
                $exam->modules()->attach($modules->random(3));
            });

        User::factory()
            ->has(
                Institute::factory(3)
                    ->afterCreating(function (Institute $institute, User $user): void {
                        $institute->owner()->associate($user);
                        $levels = Level::all();


                        foreach ($levels as $level) {
                            $institute->levels()->attach($level, [
                                'institute_diferencial_percentage_price' => rand(-20, 20),
                                'institute_diferencial_aditional_price' => rand(-500, 500),
                                'institute_right_exam' => $institute->instituteType->slug == 'premium_exam_centre'? rand(1000, 5000) : null,
                                'can_edit' => $institute->instituteType->slug == 'premium_exam_centre',
                            ]);
                        }
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
