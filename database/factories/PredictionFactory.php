<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Prediction>
 */
class PredictionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement(['race', 'preseason', 'midseason']),
            'season' => $this->faker->numberBetween(2020, 2025),
            'race_round' => $this->faker->numberBetween(1, 24),
            'race_id' => $this->faker->uuid(),
            'prediction_data' => [
                'driver_order' => $this->faker->shuffleArray(range(1, 20)),
                'fastest_lap' => $this->faker->randomElement(['max_verstappen', 'lewis_hamilton', 'charles_leclerc']),
            ],
            'score' => $this->faker->numberBetween(0, 500),
            'accuracy' => $this->faker->randomFloat(2, 0, 100),
            'status' => $this->faker->randomElement(['draft', 'submitted', 'locked', 'scored']),
            'submitted_at' => $this->faker->optional()->dateTime(),
            'locked_at' => $this->faker->optional()->dateTime(),
            'scored_at' => $this->faker->optional()->dateTime(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
