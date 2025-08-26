<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Predictions\PredictionForm;
use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Teams;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LivewirePredictionFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_prediction_form_can_be_rendered(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(PredictionForm::class)
            ->assertSee('Create New Prediction')
            ->assertSee('Prediction Type')
            ->assertSee('Season');
    }

    public function test_prediction_form_loads_drivers_and_teams(): void
    {
        $user = User::factory()->create();

        // Create some test data
        $team = Teams::factory()->create();
        $driver = Drivers::factory()->create(['team_id' => $team->id]);

        Livewire::actingAs($user)
            ->test(PredictionForm::class)
            ->assertSet('season', 2024)
            ->assertSet('type', 'race');
    }

    public function test_prediction_form_can_save_race_prediction(): void
    {
        $user = User::factory()->create();

        // Create test data
        $team = Teams::factory()->create();
        $drivers = Drivers::factory()->count(20)->create(['team_id' => $team->id]);

        $driverOrder = $drivers->pluck('id')->toArray();

        Livewire::actingAs($user)
            ->test(PredictionForm::class)
            ->set('type', 'race')
            ->set('season', 2024)
            ->set('raceRound', 1)
            ->set('driverOrder', $driverOrder)
            ->set('fastestLapDriverId', $drivers->first()->id)
            ->set('notes', 'Test prediction')
            ->call('save')
            ->assertRedirect(route('predictions.index'));

        $this->assertDatabaseHas('predictions', [
            'user_id' => $user->id,
            'type' => 'race',
            'season' => 2024,
            'race_round' => 1,
            'notes' => 'Test prediction',
        ]);
    }

    public function test_prediction_form_validates_required_fields(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(PredictionForm::class)
            ->set('type', '')
            ->set('season', 0) // Invalid season
            ->call('save')
            ->assertHasErrors(['type', 'season']);
    }

    public function test_prediction_form_can_edit_existing_prediction(): void
    {
        $user = User::factory()->create();
        $team = Teams::factory()->create();
        $drivers = Drivers::factory()->count(3)->create(['team_id' => $team->id]);

        $prediction = Prediction::factory()->create([
            'user_id' => $user->id,
            'type' => 'race',
            'season' => 2024,
            'prediction_data' => [
                'driver_order' => $drivers->pluck('id')->toArray(),
                'fastest_lap' => $drivers->first()->id,
            ],
        ]);

        Livewire::actingAs($user)
            ->test(PredictionForm::class, ['existingPrediction' => $prediction])
            ->assertSet('type', 'race')
            ->assertSet('season', 2024)
            ->assertSet('driverOrder', $drivers->pluck('id')->toArray())
            ->assertSet('fastestLapDriverId', $drivers->first()->id);
    }
}
