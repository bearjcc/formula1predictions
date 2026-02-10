<?php

use App\Livewire\Predictions\PredictionForm;
use App\Livewire\Races\RacesList;
use App\Models\Drivers;
use App\Models\Races;
use App\Models\User;
use App\Services\F1ApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Laravel\mock;

uses(RefreshDatabase::class);

test('home page guides new users to sign up or log in', function () {
    $response = get(route('home'));

    $response->assertOk();
    $response->assertSee('Start Predicting');
    $response->assertSee('Get Started');
});

test('authenticated user can go from races list to race prediction form with race preselected', function () {
    $season = (int) config('f1.current_season', 2026);

    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    /** @var Races $race */
    $race = Races::factory()->create([
        'season' => $season,
        'round' => 1,
        'status' => 'upcoming',
    ]);

    // Mock F1 API to return a calendar that matches this race
    mock(F1ApiService::class, function ($mock) use ($season) {
        $mock->shouldReceive('getRacesForYear')
            ->with($season)
            ->andReturn([
                [
                    'round' => 1,
                    'raceName' => 'Season Opener',
                    'circuit' => [
                        'circuitName' => 'Test Circuit',
                        'country' => 'Test Country',
                    ],
                    'date' => "{$season}-03-10",
                    'time' => '14:00:00Z',
                    'status' => 'upcoming',
                    'results' => [],
                ],
            ]);
    });

    // From races list, clicking "Make Prediction" should redirect with race context
    Livewire::test(RacesList::class, ['year' => $season])
        ->call('makePrediction', 1)
        ->assertRedirect(route('predict.create', ['race_id' => $race->id]));

    // Visiting predict.create with race_id should mount the prediction form with race preselected
    $response = get(route('predict.create', ['race_id' => $race->id]));
    $response->assertOk();
    $response->assertSeeLivewire('predictions.prediction-form');

    Livewire::test(PredictionForm::class, ['race' => $race])
        ->assertSet('season', $season)
        ->assertSet('raceRound', 1)
        ->assertSet('type', 'race');
});

test('user can submit a basic race prediction and it is stored', function () {
    $season = (int) config('f1.current_season', 2026);

    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    /** @var Races $race */
    $race = Races::factory()->create([
        'season' => $season,
        'round' => 1,
        'status' => 'upcoming',
    ]);

    $drivers = Drivers::factory()->count(5)->create(['is_active' => true]);

    // For validation, using numeric IDs is sufficient and matches the closure logic
    $driverOrder = $drivers->pluck('id')->toArray();
    $fastestLapId = (string) $drivers->first()->id;

    Livewire::test(PredictionForm::class, ['race' => $race])
        ->set('type', 'race')
        ->set('season', $season)
        ->set('raceRound', 1)
        ->set('driverOrder', $driverOrder)
        ->set('fastestLapDriverId', $fastestLapId)
        ->call('save')
        ->assertHasNoErrors();

    assertDatabaseHas('predictions', [
        'user_id' => $user->id,
        'type' => 'race',
        'season' => $season,
        'race_round' => 1,
        'race_id' => (string) $race->id,
        'status' => 'submitted',
    ]);
});
