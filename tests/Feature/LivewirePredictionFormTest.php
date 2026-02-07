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

        // Refresh to ensure data is loaded from database
        $prediction->refresh();

        Livewire::actingAs($user)
            ->test(PredictionForm::class, ['existingPrediction' => $prediction])
            ->assertSet('type', 'race')
            ->assertSet('season', 2024)
            ->assertSet('driverOrder', function ($value) use ($drivers) {
                // Livewire hydrates integer IDs to strings, so compare as strings
                $expected = $drivers->pluck('id')->toArray();
                // Sort both arrays for comparison since order might differ
                sort($expected);
                sort($value);
                return $value == $expected;
            })
            ->assertSet('fastestLapDriverId', $drivers->first()->id);
    }

    public function test_race_prediction_requires_race_round_in_livewire(): void
    {
        $user = User::factory()->create();

        $team = Teams::factory()->create();
        $drivers = Drivers::factory()->count(20)->create(['team_id' => $team->id]);

        $driverOrder = $drivers->pluck('id')->toArray();

        Livewire::actingAs($user)
            ->test(PredictionForm::class)
            ->set('type', 'race')
            ->set('season', 2024)
            ->set('raceRound', null)
            ->set('driverOrder', $driverOrder)
            ->call('save')
            ->assertHasErrors(['raceRound' => 'required_if']);
    }

    public function test_sprint_prediction_requires_race_round_and_driver_order_in_livewire(): void
    {
        $user = User::factory()->create();

        $team = Teams::factory()->create();
        $drivers = Drivers::factory()->count(20)->create(['team_id' => $team->id]);

        $driverOrder = $drivers->pluck('id')->toArray();

        Livewire::actingAs($user)
            ->test(PredictionForm::class)
            ->set('type', 'sprint')
            ->set('season', 2024)
            ->set('raceRound', null)
            ->set('driverOrder', $driverOrder)
            ->call('save')
            ->assertHasErrors(['raceRound' => 'required_if']);
    }

    public function test_sprint_prediction_only_allowed_for_races_with_sprint(): void
    {
        $user = User::factory()->create();
        $raceWithoutSprint = \App\Models\Races::factory()->create([
            'has_sprint' => false,
        ]);

        Teams::factory()->count(10)->create();
        Drivers::factory()->count(20)->create();

        Livewire::actingAs($user)
            ->test(PredictionForm::class, ['race' => $raceWithoutSprint])
            ->set('type', 'sprint')
            ->set('season', (int) $raceWithoutSprint->season)
            ->set('raceRound', (int) $raceWithoutSprint->round)
            ->call('save')
            ->assertHasErrors(['type']);
    }

    public function test_preseason_prediction_rejects_race_round_in_livewire(): void
    {
        $user = User::factory()->create();

        Teams::factory()->count(10)->create();
        Drivers::factory()->count(20)->create();

        Livewire::actingAs($user)
            ->test(PredictionForm::class)
            ->set('type', 'preseason')
            ->set('season', 2024)
            ->set('raceRound', 1)
            ->call('save')
            ->assertHasErrors(['raceRound' => 'prohibited_if']);
    }

    public function test_cannot_edit_locked_prediction_via_livewire(): void
    {
        $user = User::factory()->create();
        $team = Teams::factory()->create();
        $drivers = Drivers::factory()->count(3)->create(['team_id' => $team->id]);

        $prediction = Prediction::factory()->create([
            'user_id' => $user->id,
            'type' => 'race',
            'season' => 2024,
            'status' => 'locked',
            'prediction_data' => [
                'driver_order' => $drivers->pluck('id')->toArray(),
                'fastest_lap' => $drivers->first()->id,
            ],
        ]);

        Livewire::actingAs($user)
            ->test(PredictionForm::class, ['existingPrediction' => $prediction])
            ->assertSet('canEdit', false)
            ->set('notes', 'Updated notes that should not persist')
            ->call('save')
            ->assertHasErrors(['base' => 'This prediction can no longer be edited.']);

        $this->assertDatabaseMissing('predictions', [
            'id' => $prediction->id,
            'notes' => 'Updated notes that should not persist',
        ]);
    }

    public function test_cannot_edit_scored_prediction_via_livewire(): void
    {
        $user = User::factory()->create();
        $team = Teams::factory()->create();
        $drivers = Drivers::factory()->count(3)->create(['team_id' => $team->id]);

        $prediction = Prediction::factory()->create([
            'user_id' => $user->id,
            'type' => 'race',
            'season' => 2024,
            'status' => 'scored',
            'prediction_data' => [
                'driver_order' => $drivers->pluck('id')->toArray(),
                'fastest_lap' => $drivers->first()->id,
            ],
        ]);

        Livewire::actingAs($user)
            ->test(PredictionForm::class, ['existingPrediction' => $prediction])
            ->assertSet('canEdit', false)
            ->set('notes', 'Updated notes that should not persist')
            ->call('save')
            ->assertHasErrors(['base' => 'This prediction can no longer be edited.']);

        $this->assertDatabaseMissing('predictions', [
            'id' => $prediction->id,
            'notes' => 'Updated notes that should not persist',
        ]);
    }

    public function test_cannot_edit_other_users_prediction_via_livewire(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $team = Teams::factory()->create();
        $drivers = Drivers::factory()->count(3)->create(['team_id' => $team->id]);

        $prediction = Prediction::factory()->create([
            'user_id' => $owner->id,
            'type' => 'race',
            'season' => 2024,
            'status' => 'draft',
            'prediction_data' => [
                'driver_order' => $drivers->pluck('id')->toArray(),
                'fastest_lap' => $drivers->first()->id,
            ],
        ]);

        Livewire::actingAs($otherUser)
            ->test(PredictionForm::class, ['existingPrediction' => $prediction])
            ->assertSet('canEdit', false)
            ->set('notes', 'Updated notes that should not persist')
            ->call('save')
            ->assertHasErrors(['base' => 'This prediction can no longer be edited.']);

        $this->assertDatabaseMissing('predictions', [
            'id' => $prediction->id,
            'notes' => 'Updated notes that should not persist',
        ]);
    }
}
