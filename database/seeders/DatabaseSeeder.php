<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Enums\ModuleType;
use App\Models\CertificateType;
use App\Models\Country;
use App\Models\Exam;
use App\Models\Institute;
use App\Models\Level;
use App\Models\LevelCountry;
use App\Models\Module;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

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
            RoleSeeder::class,
            StatusSeeder::class,
            ModalitySeeder::class,
            CertificateTypeSeeder::class,
        ]);




        $countries = Country::all();
        $modules = Module::all();

        $levels = Level::all();
        foreach ($levels as $level) {
            foreach ($countries as $country) {
                $level->countries()->attach($country, [
                    'price_all_modules' => rand(1000, 5000),
                    'price_exam_right_all_modules' => $examRight = rand(1000, 5000),
                    'price_exam_right' => $examRight + rand(1000, 2000),
                ]);
            }
            $level->save();
        }

        LevelCountry::all()->each(function (LevelCountry $levelCountry) use ($modules): void {
            $levelCountry->modules()->attach($modules->random(3), [
                'price' => rand(1000, 5000),
                'module_type' => ModuleType::cases()[rand(0, 2)],
            ]);
        });

        Exam::factory(10)
            ->create()
            ->each(function (Exam $exam) use ($modules): void {
                $exam->modules()->attach($modules->random(3));
            });

        $testUser = User::factory()
            ->has(
                Institute::factory(3)
                    ->afterCreating(function (Institute $institute, User $user): void {
                        $institute->owner()->associate($user);
                        $institute->save();
                    })
                    ->has(
                        Student::factory(10)
                    )
            )
            ->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $testUser->assignRole(Role::firstOrCreate(['name' => 'Superadministrator'])->first());

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
