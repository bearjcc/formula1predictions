<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Predictions\PredictionForm;
use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\Standings;
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
            ->assertSee('Cancel');
    }

    public function test_prediction_form_loads_drivers_and_teams(): void
    {
        $user = User::factory()->create();

        // Create some test data
        $team = Teams::factory()->create();
        $driver = Drivers::factory()->create(['team_id' => $team->id]);

        Livewire::actingAs($user)
            ->test(PredictionForm::class)
            ->assertSet('season', (int) config('f1.current_season'))
            ->assertSet('type', 'race');
    }

    public function test_prediction_form_uses_selected_race_season_before_loading_drivers(): void
    {
        $user = User::factory()->create();
        $team2024 = Teams::factory()->create(['team_name' => 'Williams Racing']);
        $team2026 = Teams::factory()->create(['team_name' => 'McLaren']);

        $driver2024 = Drivers::factory()->create([
            'driver_id' => 'alex_albon',
            'name' => 'Alex',
            'surname' => 'Albon',
            'team_id' => $team2024->id,
        ]);
        $driver2026 = Drivers::factory()->create([
            'driver_id' => 'oscar_piastri',
            'name' => 'Oscar',
            'surname' => 'Piastri',
            'team_id' => $team2026->id,
        ]);

        Standings::factory()->create([
            'season' => 2024,
            'type' => 'drivers',
            'round' => null,
            'entity_id' => 'alex_albon',
            'entity_name' => 'Alex Albon',
            'position' => 1,
        ]);
        Standings::factory()->create([
            'season' => 2026,
            'type' => 'drivers',
            'round' => null,
            'entity_id' => 'oscar_piastri',
            'entity_name' => 'Oscar Piastri',
            'position' => 1,
        ]);

        $race = Races::factory()->create([
            'season' => 2024,
            'round' => 1,
            'status' => 'upcoming',
        ]);

        Livewire::actingAs($user)
            ->test(PredictionForm::class, ['race' => $race])
            ->assertSet('season', 2024)
            ->assertSet('drivers', function (array $drivers) use ($driver2024, $driver2026) {
                $ids = collect($drivers)->pluck('id')->all();

                return in_array($driver2024->driver_id, $ids, true)
                    && ! in_array($driver2026->driver_id, $ids, true);
            });
    }

    public function test_prediction_form_falls_back_to_team_assigned_drivers_when_standings_are_missing(): void
    {
        $user = User::factory()->create();
        $race = Races::factory()->create([
            'season' => 2026,
            'round' => 1,
            'status' => 'upcoming',
        ]);
        $team = Teams::factory()->create([
            'team_name' => 'Atlassian Williams Racing',
            'is_active' => true,
        ]);
        $drivers = collect([
            Drivers::factory()->create([
                'driver_id' => 'fallback_driver_one',
                'name' => 'Fallback',
                'surname' => 'One',
                'team_id' => $team->id,
            ]),
            Drivers::factory()->create([
                'driver_id' => 'fallback_driver_two',
                'name' => 'Fallback',
                'surname' => 'Two',
                'team_id' => $team->id,
            ]),
        ]);

        Livewire::actingAs($user)
            ->test(PredictionForm::class, ['race' => $race])
            ->assertSet('drivers', function (array $driverRows) use ($drivers) {
                $ids = collect($driverRows)->pluck('id')->all();
                $teamColors = collect($driverRows)->pluck('team.color')->filter()->all();

                return count($driverRows) === 2
                    && collect($drivers)->pluck('driver_id')->every(fn ($id) => in_array($id, $ids, true))
                    && in_array('#1868DB', $teamColors, true);
            });
    }

    public function test_prediction_form_can_save_race_prediction(): void
    {
        $user = User::factory()->create();

        // Create test data: race so form has context, drivers with driver_id for payload
        $race = \App\Models\Races::factory()->create(['season' => 2024, 'round' => 1, 'status' => 'upcoming']);
        $team = Teams::factory()->create();
        $drivers = Drivers::factory()->count(20)->create(['team_id' => $team->id]);

        $driverOrder = $drivers->pluck('driver_id')->toArray();

        Livewire::actingAs($user)
            ->test(PredictionForm::class, ['race' => $race])
            ->set('type', 'race')
            ->set('season', 2024)
            ->set('raceRound', 1)
            ->set('driverOrder', $driverOrder)
            ->set('fastestLapDriverId', $drivers->first()->driver_id)
            ->call('save')
            ->assertRedirect(route('predictions.index'));

        $this->assertDatabaseHas('predictions', [
            'user_id' => $user->id,
            'type' => 'race',
            'season' => 2024,
            'race_round' => 1,
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
            ->assertHasErrors('raceRound');
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
            ->assertHasErrors('raceRound');
    }

    public function test_sprint_prediction_only_allowed_for_races_with_sprint(): void
    {
        $user = User::factory()->create();
        $raceWithoutSprint = \App\Models\Races::factory()->create([
            'status' => 'upcoming',
            'has_sprint' => false,
            'qualifying_start' => now()->addDays(2),
        ]);
        $team = Teams::factory()->create();
        $drivers = Drivers::factory()->count(20)->create(['team_id' => $team->id]);
        $driverOrder = $drivers->pluck('driver_id')->toArray();

        Livewire::actingAs($user)
            ->test(PredictionForm::class, ['race' => $raceWithoutSprint])
            ->set('type', 'sprint')
            ->set('season', (int) $raceWithoutSprint->season)
            ->set('raceRound', (int) $raceWithoutSprint->round)
            ->set('driverOrder', $driverOrder)
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
            ->assertHasErrors('raceRound');
    }

    public function test_preseason_prediction_can_be_created_via_livewire(): void
    {
        $user = User::factory()->create();

        // Ensure there is a first race with a future qualifying start so preseason is unlocked
        Races::factory()->create([
            'season' => 2026,
            'round' => 1,
            'status' => 'upcoming',
            'qualifying_start' => now()->addDay(),
        ]);

        // Create active teams and drivers (two per team) to power constructor order and teammate battles
        $teams = Teams::factory()->count(3)->create(['is_active' => true]);
        foreach ($teams as $team) {
            Drivers::factory()->count(2)->create(['team_id' => $team->id]);
        }

        $season = 2026;

        Livewire::actingAs($user)
            ->test(PredictionForm::class)
            ->set('type', 'preseason')
            ->set('season', $season)
            ->set('teamOrder', $teams->pluck('id')->toArray())
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('predictions.index'));

        $this->assertDatabaseHas('predictions', [
            'user_id' => $user->id,
            'type' => 'preseason',
            'season' => $season,
            'status' => 'submitted',
        ]);
    }

    public function test_cannot_edit_locked_prediction_via_livewire(): void
    {
        $user = User::factory()->create();
        $team = Teams::factory()->create();
        $drivers = Drivers::factory()->count(3)->create(['team_id' => $team->id]);

        $prediction = Prediction::factory()->locked()->create([
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
            ->assertSet('canEdit', false)
            ->call('save')
            ->assertHasErrors(['base' => 'The prediction deadline has passed.']);
    }

    public function test_cannot_edit_scored_prediction_via_livewire(): void
    {
        $user = User::factory()->create();
        $team = Teams::factory()->create();
        $drivers = Drivers::factory()->count(3)->create(['team_id' => $team->id]);

        $prediction = Prediction::factory()->scored()->create([
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
            ->assertSet('canEdit', false)
            ->call('save')
            ->assertHasErrors(['base' => 'The prediction deadline has passed.']);
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
            ->call('save')
            ->assertHasErrors(['base' => 'The prediction deadline has passed.']);
    }
}
