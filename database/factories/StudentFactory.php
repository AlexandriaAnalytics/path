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
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'slug' => fake()->slug(),
            'country' => fake()->randomElement(\App\Enums\Country::values()),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'cbu' => fake()->bankAccountNumber(),
            'cuil' => fake()->numerify('##-########-#'),
            'birth_date' => fake()->date(),
            'status' => 'active',
        ];
    }
}
