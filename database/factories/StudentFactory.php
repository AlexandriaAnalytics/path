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
            'names' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'country' => fake()->randomElement(\App\Enums\Country::values()),
            'cbu' => fake()->bankAccountNumber(),
            'birth_date' => fake()->date(),
            'status' => 'active',
        ];
    }
}
