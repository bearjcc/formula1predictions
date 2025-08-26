<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Standings>
 */
class StandingsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'season' => $this->faker->numberBetween(2020, 2025),
            'type' => $this->faker->randomElement(['drivers', 'constructors']),
            'round' => $this->faker->optional()->numberBetween(1, 24),
            'entity_id' => $this->faker->slug(),
            'entity_name' => $this->faker->name(),
            'position' => $this->faker->numberBetween(1, 20),
            'points' => $this->faker->randomFloat(1, 0, 500),
            'wins' => $this->faker->numberBetween(0, 10),
            'podiums' => $this->faker->numberBetween(0, 20),
            'poles' => $this->faker->numberBetween(0, 15),
            'fastest_laps' => $this->faker->numberBetween(0, 10),
            'dnfs' => $this->faker->numberBetween(0, 5),
            'additional_data' => $this->faker->optional()->randomElement([null, json_encode([])]),
        ];
    }
}
