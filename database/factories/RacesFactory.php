<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Races>
 */
class RacesFactory extends Factory
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
            'round' => $this->faker->numberBetween(1, 24),
            'race_name' => $this->faker->city().' Grand Prix',
            'date' => $this->faker->dateTimeBetween('now', '+1 year'),
            'time' => $this->faker->time(),
            'circuit_api_id' => null,
            'circuit_name' => $this->faker->city().' Circuit',
            'circuit_url' => $this->faker->url(),
            'country' => $this->faker->country(),
            'locality' => $this->faker->city(),
            'circuit_length' => $this->faker->randomFloat(3, 3.0, 7.0),
            'laps' => $this->faker->numberBetween(50, 80),
            'weather' => $this->faker->randomElement(['sunny', 'cloudy', 'rainy', 'overcast']),
            'temperature' => $this->faker->randomFloat(1, 15, 35),
            'status' => $this->faker->randomElement(['upcoming', 'ongoing', 'completed', 'cancelled']),
            'has_sprint' => $this->faker->boolean(30),
            'is_special_event' => $this->faker->boolean(10),
            'results' => $this->faker->optional()->randomElement([null, json_encode([])]),
        ];
    }
}
