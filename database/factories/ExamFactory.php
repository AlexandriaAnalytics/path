<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Exam>
 */
class ExamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'session_name' => str(fake()->word())->title(),
            'scheduled_date' => fake()->date(),
            'location' => fake()->address(),
            'type' => fake()->randomElement(\App\Enums\ExamType::values()),
            'maximum_number_of_students' => fake()->numberBetween(1, 100),
            'comments' => fake()->optional()->sentence(),
            'payment_deadline' => fake()->date(),
        ];
    }
}
