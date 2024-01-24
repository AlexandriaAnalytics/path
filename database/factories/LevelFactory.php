<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Level>
 */
class LevelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $name = fake()->name(),
            'slug' => Str::slug($name),
            'description' => fake()->optional()->realText(),
            'price' => fake()->randomFloat(2, 0, 100),
            'complete_price' => fake()->randomFloat(2, 0, 100),
            'minimum_age' => $minimumAge = fake()->numberBetween(1, 20),
            'maximum_age' => fake()->numberBetween($minimumAge, 85),
            'modules' => fake()->optional()->text(),
            'tier' => fake()->optional()->numberBetween(1, 3),
        ];
    }
}
