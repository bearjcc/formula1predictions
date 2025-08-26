<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Drivers>
 */
class DriversFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Use a wide namespace to avoid unique collisions across many tests
            'driver_id' => 'drv-'.$this->faker->unique()->bothify('##########'),
            'name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'nationality' => $this->faker->country(),
            'url' => $this->faker->url(),
            'driver_number' => $this->faker->numberBetween(1, 99),
            'description' => $this->faker->sentence(),
            'photo_url' => $this->faker->imageUrl(),
            'helmet_url' => $this->faker->imageUrl(),
            'date_of_birth' => $this->faker->date(),
            'website' => $this->faker->url(),
            'twitter' => $this->faker->userName(),
            'instagram' => $this->faker->userName(),
            'world_championships' => $this->faker->numberBetween(0, 7),
            'race_wins' => $this->faker->numberBetween(0, 100),
            'podiums' => $this->faker->numberBetween(0, 200),
            'pole_positions' => $this->faker->numberBetween(0, 100),
            'fastest_laps' => $this->faker->numberBetween(0, 50),
            'points' => $this->faker->numberBetween(0, 3000),
            'is_active' => true,
        ];
    }
}
