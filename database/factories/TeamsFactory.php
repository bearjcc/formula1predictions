<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Teams>
 */
class TeamsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => 'tm-'.$this->faker->unique()->bothify('##########'),
            'team_name' => $this->faker->company(),
            'nationality' => $this->faker->country(),
            'url' => $this->faker->url(),
            'description' => $this->faker->sentence(),
            'logo_url' => $this->faker->imageUrl(),
            'website' => $this->faker->url(),
            'team_principal' => $this->faker->name(),
            'technical_director' => $this->faker->name(),
            'chassis' => $this->faker->word(),
            'power_unit' => $this->faker->word(),
            'base_location' => $this->faker->city(),
            'founded' => $this->faker->year(),
            'world_championships' => $this->faker->numberBetween(0, 16),
            'race_wins' => $this->faker->numberBetween(0, 200),
            'podiums' => $this->faker->numberBetween(0, 500),
            'pole_positions' => $this->faker->numberBetween(0, 200),
            'fastest_laps' => $this->faker->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
