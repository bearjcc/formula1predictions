<?php

use App\Livewire\Races\RacesList;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\User;
use App\Services\F1ApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\mock;

uses(RefreshDatabase::class);

test('races page loads successfully', function () {
    // Mock the F1ApiService to return test data
    mock(F1ApiService::class, function ($mock) {
        $mock->shouldReceive('getRacesForYear')
            ->with(2024)
            ->andReturn([
                [
                    'round' => 1,
                    'raceName' => 'Test Race 1',
                    'circuit' => ['circuitName' => 'Test Circuit', 'country' => 'Test Country'],
                    'date' => '2024-03-15',
                    'time' => '14:00:00Z',
                    'status' => 'upcoming',
                    'results' => [],
                ],
            ]);
    });

    $response = $this->get('/2024/races');

    $response->assertSuccessful();
    $response->assertSee('2024 Races');
    $response->assertSeeLivewire('races.races-list');
});

test('races page shows error state when API fails', function () {
    mock(F1ApiService::class, function ($mock) {
        $mock->shouldReceive('getRacesForYear')
            ->with(2024)
            ->andThrow(new \App\Exceptions\F1ApiException('API Error', 500, '/2024/1/race', 2024));
    });

    $response = $this->get('/2024/races');

    $response->assertSuccessful();
    $response->assertSee('Error Loading Races');
    $response->assertSee('We\'re having trouble loading race data right now');
});

test('races page shows user-friendly error for connection failures', function () {
    mock(F1ApiService::class, function ($mock) {
        $mock->shouldReceive('getRacesForYear')
            ->with(2024)
            ->andThrow(new \Exception('Connection refused'));
    });

    $response = $this->get('/2024/races');

    $response->assertSuccessful();
    $response->assertSee('Error Loading Races');
    $response->assertSee('We\'re having trouble loading race data right now');
});

test('races page does not expose technical error details to user', function () {
    mock(F1ApiService::class, function ($mock) {
        $mock->shouldReceive('getRacesForYear')
            ->with(2024)
            ->andThrow(new \Exception('cURL error 28: Operation timed out'));
    });

    $response = $this->get('/2024/races');

    $response->assertSuccessful();
    $response->assertDontSee('cURL error');
    $response->assertDontSee('Operation timed out');
});

test('current season races page returns 200 and shows error state when API fails', function () {
    $year = (int) config('f1.current_season', 2026);

    mock(F1ApiService::class, function ($mock) use ($year) {
        $mock->shouldReceive('getRacesForYear')
            ->with($year)
            ->andThrow(new \App\Exceptions\F1ApiException('Service unavailable', 503, "/{$year}/1/race", $year));
    });

    $response = $this->get("/{$year}/races");

    $response->assertOk();
    $response->assertSee((string) $year);
    $response->assertSee('Error Loading Races');
    $response->assertSee('We\'re having trouble loading race data right now');
});

test('races list shows all races grouped as next, future, and past for current season', function () {
    $year = (int) config('f1.current_season', 2026);

    mock(F1ApiService::class, function ($mock) use ($year) {
        $mock->shouldReceive('getRacesForYear')
            ->with($year)
            ->andReturn([
                [
                    'round' => 1,
                    'raceName' => 'Australian Grand Prix',
                    'circuit' => [
                        'circuitName' => 'Albert Park',
                        'country' => 'Australia',
                    ],
                    'date' => "{$year}-03-15",
                    'time' => '04:00:00Z',
                    'status' => 'upcoming',
                    'results' => [],
                ],
                [
                    'round' => 2,
                    'raceName' => 'Canadian Grand Prix',
                    'circuit' => [
                        'circuitName' => 'Circuit Gilles Villeneuve',
                        'country' => 'Canada',
                    ],
                    'date' => "{$year}-06-09",
                    'time' => '18:00:00Z',
                    'status' => 'completed',
                    'results' => [
                        ['driver' => 'Sample Driver'],
                    ],
                ],
            ]);
    });

    Livewire::test(RacesList::class, ['year' => $year])
        ->assertSee('Australian Grand Prix')
        ->assertSee('Canadian Grand Prix')
        ->assertSee('Next race')
        ->assertSee('Past races');
});

test('completed season shows flat races list without next or past grouping', function () {
    mock(F1ApiService::class, function ($mock) {
        $mock->shouldReceive('getRacesForYear')
            ->with(2025)
            ->andReturn([
                [
                    'round' => 5,
                    'raceName' => 'Miami Grand Prix',
                    'circuit' => ['circuitName' => 'Miami', 'country' => 'USA'],
                    'date' => '2025-05-04',
                    'time' => '20:00:00Z',
                    'status' => 'upcoming',
                    'results' => [],
                ],
            ]);
    });

    Livewire::test(RacesList::class, ['year' => 2025])
        ->assertSee('Miami Grand Prix')
        ->assertSee('Races (1)')
        ->assertDontSee('Next race')
        ->assertDontSee('Past races')
        ->assertDontSee('Future races');
});

test('non-admin users do not see refresh races button', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    mock(F1ApiService::class, function ($mock) {
        $mock->shouldReceive('getRacesForYear')
            ->with(2024)
            ->andReturn([]);
    });

    Livewire::test(RacesList::class, ['year' => 2024])
        ->assertDontSee('Refresh Data');
});

test('admins can refresh races data', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    mock(F1ApiService::class, function ($mock) {
        $mock->shouldReceive('getRacesForYear')
            ->with(2024)
            ->twice()
            ->andReturn([]);
    });

    Livewire::test(RacesList::class, ['year' => 2024])
        ->call('refreshRaces');
});

test('race card shows Edit Prediction button when user already has a prediction for open race', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $race = Races::factory()->create([
        'season' => 2026,
        'round' => 1,
        'status' => 'upcoming',
    ]);

    Prediction::factory()->create([
        'user_id' => $user->id,
        'type' => 'race',
        'season' => 2026,
        'race_round' => 1,
        'race_id' => $race->id,
    ]);

    mock(F1ApiService::class, function ($mock) use ($race) {
        $mock->shouldReceive('getRacesForYear')
            ->with(2026)
            ->andReturn([
                [
                    'round' => $race->round,
                    'raceName' => $race->display_name,
                    'circuit' => ['circuitName' => 'Test Circuit', 'country' => 'Australia'],
                    'date' => '2026-03-15',
                    'status' => 'upcoming',
                    'predictions_open' => true,
                    'results' => [],
                ],
            ]);
    });

    Livewire::test(RacesList::class, ['year' => 2026])
        ->assertSee('Edit Prediction')
        ->assertDontSee('Make Prediction');
});

test('race card shows Make Prediction button when user has no prediction for open race', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Races::factory()->create([
        'season' => 2026,
        'round' => 1,
        'status' => 'upcoming',
    ]);

    mock(F1ApiService::class, function ($mock) {
        $mock->shouldReceive('getRacesForYear')
            ->with(2026)
            ->andReturn([
                [
                    'round' => 1,
                    'raceName' => 'Australian Grand Prix',
                    'circuit' => ['circuitName' => 'Albert Park', 'country' => 'Australia'],
                    'date' => '2026-03-15',
                    'status' => 'upcoming',
                    'predictions_open' => true,
                    'results' => [],
                ],
            ]);
    });

    Livewire::test(RacesList::class, ['year' => 2026])
        ->assertSee('Make Prediction')
        ->assertDontSee('Edit Prediction');
});

test('race card shows score badge when prediction is scored', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $race = Races::factory()->create([
        'season' => 2026,
        'round' => 1,
        'status' => 'completed',
    ]);

    Prediction::factory()->scored(42)->create([
        'user_id' => $user->id,
        'type' => 'race',
        'season' => 2026,
        'race_round' => 1,
        'race_id' => $race->id,
    ]);

    mock(F1ApiService::class, function ($mock) use ($race) {
        $mock->shouldReceive('getRacesForYear')
            ->with(2026)
            ->andReturn([
                [
                    'round' => $race->round,
                    'raceName' => $race->display_name,
                    'circuit' => ['circuitName' => 'Albert Park', 'country' => 'Australia'],
                    'date' => '2026-03-15',
                    'status' => 'completed',
                    'predictions_open' => false,
                    'results' => [],
                ],
            ]);
    });

    Livewire::test(RacesList::class, ['year' => 2026])
        ->assertSee('42 pts')
        ->assertDontSee('Make Prediction')
        ->assertDontSee('Edit Prediction');
});

test('race card shows locked state when prediction is locked and no score yet', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $race = Races::factory()->create([
        'season' => 2026,
        'round' => 1,
        'status' => 'ongoing',
    ]);

    Prediction::factory()->locked()->create([
        'user_id' => $user->id,
        'type' => 'race',
        'season' => 2026,
        'race_round' => 1,
        'race_id' => $race->id,
    ]);

    mock(F1ApiService::class, function ($mock) use ($race) {
        $mock->shouldReceive('getRacesForYear')
            ->with(2026)
            ->andReturn([
                [
                    'round' => $race->round,
                    'raceName' => $race->display_name,
                    'circuit' => ['circuitName' => 'Albert Park', 'country' => 'Australia'],
                    'date' => '2026-03-15',
                    'status' => 'ongoing',
                    'predictions_open' => false,
                    'results' => [],
                ],
            ]);
    });

    Livewire::test(RacesList::class, ['year' => 2026])
        ->assertSee('Prediction locked')
        ->assertDontSee('Edit Prediction')
        ->assertDontSee('Make Prediction');
});
