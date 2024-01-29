<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Period>
 */
class PeriodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'description' => fake()->sentence(),
            'starts_at' => $startsAt = fake()->dateTimeBetween('-1 year', '+1 year'),
            'ends_at' => fake()->dateTimeBetween($startsAt, '+1 year'),
        ];
    }
}
