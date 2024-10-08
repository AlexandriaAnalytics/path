<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Institute>
 */
class InstituteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => \App\Models\User::factory(),
            'institute_type_id' => \App\Models\InstituteType::all()->random()->id,
            'name' => fake()->company(),
            'files_url' => fake()->url(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'street_name' => fake()->streetName(),
            'number' => fake()->optional()->buildingNumber(),
            'city' => fake()->city(),
            'province' => fake()->state(),
            'postcode' => fake()->postcode(),
            'country' => \App\Models\Country::all()->random()->id,
            'unique_number' => fake()->numberBetween(1, 1000)
        ];
    }
}
