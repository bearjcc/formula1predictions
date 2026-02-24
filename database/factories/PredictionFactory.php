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
            'race_id' => null,
            'prediction_data' => [
                // driver_order stores driver_id strings (e.g. 'max_verstappen'), not integer PKs.
                // We use a fixed representative set so factories work without hitting the DB.
                // Tests that need real driver IDs should override this via ->state() or create([...]).
                'driver_order' => $this->faker->shuffleArray([
                    'max_verstappen', 'lewis_hamilton', 'charles_leclerc',
                    'lando_norris', 'carlos_sainz', 'george_russell',
                    'fernando_alonso', 'oscar_piastri', 'lance_stroll',
                    'esteban_ocon',
                ]),
                'fastest_lap' => $this->faker->randomElement(['max_verstappen', 'lewis_hamilton', 'charles_leclerc']),
            ],
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function submitted(): static
    {
        return $this->afterCreating(fn ($p) => $p->forceFill(['status' => 'submitted', 'submitted_at' => now()])->save());
    }

    public function locked(): static
    {
        return $this->afterCreating(fn ($p) => $p->forceFill(['status' => 'locked', 'locked_at' => now()])->save());
    }

    public function scored(int $score = 10, float $accuracy = 50.0): static
    {
        return $this->afterCreating(fn ($p) => $p->forceFill([
            'status' => 'scored',
            'score' => $score,
            'accuracy' => $accuracy,
            'scored_at' => now(),
        ])->save());
    }
}
