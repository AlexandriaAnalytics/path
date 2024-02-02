<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'institute_id' => \App\Models\Institute::factory(),
            'national_id' => fake()->numerify('##-########-#'),
            'country_id' => fake()->randomElement(\App\Models\Country::pluck('id')->toArray()),
            'names' => fake()->firstName(),
            'slug' => fake()->slug(),
            'country' => fake()->randomElement(\App\Enums\Country::values()),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'cbu' => fake()->bankAccountNumber(),
            'birth_date' => fake()->date(),
            'status' => 'active',
        ];
    }
}
