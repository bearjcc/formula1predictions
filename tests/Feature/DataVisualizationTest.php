<?php

use App\Models\Drivers;
use App\Models\Races;
use App\Models\Teams;
use App\Models\User;
use App\Services\ChartDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

test('chart data service can generate driver standings progression', function () {
    $chartService = new ChartDataService;

    Races::factory()->create([
        'season' => 2024,
        'round' => 1,
        'race_name' => 'Test Grand Prix',
        'results' => [
            ['driver_id' => 1, 'position' => 0],
            ['driver_id' => 2, 'position' => 1],
        ],
    ]);

    Drivers::factory()->create(['id' => 1, 'name' => 'Lewis', 'surname' => 'Hamilton']);
    Drivers::factory()->create(['id' => 2, 'name' => 'Max', 'surname' => 'Verstappen']);

    $data = $chartService->getDriverStandingsProgression(2024);

    expect($data)->toHaveCount(1)
        ->and($data[0]['Lewis Hamilton'])->toBe(1)
        ->and($data[0]['Max Verstappen'])->toBe(2);
});

test('chart data service can generate team standings progression', function () {
    $chartService = new ChartDataService;

    $team1 = Teams::factory()->create(['team_name' => 'Mercedes']);
    $team2 = Teams::factory()->create(['team_name' => 'Red Bull']);

    $driver1 = Drivers::factory()->create(['team_id' => $team1->id]);
    $driver2 = Drivers::factory()->create(['team_id' => $team2->id]);

    Races::factory()->create([
        'season' => 2024,
        'round' => 1,
        'race_name' => 'Test Grand Prix',
        'results' => [
            ['driver_id' => $driver1->id, 'position' => 0],
            ['driver_id' => $driver2->id, 'position' => 1],
        ],
    ]);

    $data = $chartService->getTeamStandingsProgression(2024);

    expect($data)->toHaveCount(1)
        ->and($data[0]['Mercedes'])->toBe(1)
        ->and($data[0]['Red Bull'])->toBe(2);
});

test('chart data service can generate driver points progression', function () {
    $chartService = new ChartDataService;

    Races::factory()->create([
        'season' => 2024,
        'round' => 1,
        'race_name' => 'Test Grand Prix',
        'results' => [
            ['driver_id' => 1, 'position' => 0],
            ['driver_id' => 2, 'position' => 1],
        ],
    ]);

    Drivers::factory()->create(['id' => 1, 'name' => 'Lewis', 'surname' => 'Hamilton']);
    Drivers::factory()->create(['id' => 2, 'name' => 'Max', 'surname' => 'Verstappen']);

    $data = $chartService->getDriverPointsProgression(2024);

    expect($data)->toHaveCount(1)
        ->and($data[0]['Lewis Hamilton'])->toBe(25)
        ->and($data[0]['Max Verstappen'])->toBe(18);
});

test('chart data service can generate driver consistency analysis', function () {
    $chartService = new ChartDataService;

    $driver = Drivers::factory()->create(['name' => 'Lewis', 'surname' => 'Hamilton']);

    Races::factory()->create([
        'season' => 2024,
        'round' => 1,
        'results' => [
            ['driver_id' => $driver->id, 'position' => 1],
        ],
    ]);

    Races::factory()->create([
        'season' => 2024,
        'round' => 2,
        'results' => [
            ['driver_id' => $driver->id, 'position' => 2],
        ],
    ]);

    $data = $chartService->getDriverConsistencyAnalysis(2024);

    expect($data)->toHaveCount(1)
        ->and($data[0]['driver'])->toBe('Lewis Hamilton')
        ->and($data[0]['races'])->toBe(2);
});

test('chart components can change chart type', function () {
    Livewire::test('charts.standings-chart')
        ->set('chartType', 'team')
        ->assertSet('chartType', 'team');
});

test('chart components can change season', function () {
    Livewire::test('charts.standings-chart')
        ->set('season', 2023)
        ->assertSet('season', 2023);
});

test('analytics page shows enhanced statistics', function () {
    $user = User::factory()->create();
    actingAs($user);

    $response = $this->get('/analytics');
    $response->assertStatus(200);
    $response->assertSee('Analytics Dashboard');
    $response->assertSee('Driver Consistency Analysis');
    $response->assertSee('Points Progression');
});
