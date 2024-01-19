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
            'institute_id' => \App\Models\Institute::factory(),
            'exam_session_name' => fake()->word(),
            'scheduled_date' => fake()->date(),
            'type' => null,
            'maximum_number_of_students' => fake()->numberBetween(1, 100),
            'comments' => fake()->optional()->sentence(),
            'modules' => [
                [
                    'type' => \App\Enums\Module::Listening,
                    'price' => fake()->numberBetween(1, 100),
                ],
                [
                    'type' => \App\Enums\Module::ReadingAndWriting,
                    'price' => fake()->numberBetween(1, 100),
                ],
                [
                    'type' => \App\Enums\Module::Speaking,
                    'price' => fake()->numberBetween(1, 100),
                ],
            ],
        ];
    }
}
