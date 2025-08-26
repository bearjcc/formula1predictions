<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Circuits>
 */
class CircuitsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'circuit_id' => $this->faker->unique()->slug(),
            'circuit_name' => $this->faker->city() . ' Circuit',
            'url' => $this->faker->url(),
            'country' => $this->faker->country(),
            'locality' => $this->faker->city(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'circuit_length' => $this->faker->randomFloat(3, 3.0, 7.0),
            'laps' => $this->faker->numberBetween(50, 80),
            'description' => $this->faker->paragraph(),
            'photo_url' => $this->faker->imageUrl(),
            'map_url' => $this->faker->imageUrl(),
            'capacity' => $this->faker->numberBetween(50000, 150000),
            'first_grand_prix' => $this->faker->year(),
            'lap_record_driver' => $this->faker->name(),
            'lap_record_time' => $this->faker->time('H:i:s'),
            'lap_record_year' => $this->faker->numberBetween(2010, 2024),
            'is_active' => $this->faker->boolean(80),
        ];
    }
}
