<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Countries>
 */
class CountriesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->country(),
            'code' => $this->faker->unique()->countryCode(),
            'flag_url' => $this->faker->imageUrl(),
            'description' => $this->faker->paragraph(),
            'f1_races_hosted' => $this->faker->numberBetween(0, 100),
            'world_championships_won' => $this->faker->numberBetween(0, 20),
            'drivers_count' => $this->faker->numberBetween(0, 50),
            'teams_count' => $this->faker->numberBetween(0, 20),
            'circuits_count' => $this->faker->numberBetween(0, 10),
            'is_active' => $this->faker->boolean(80),
        ];
    }
}
