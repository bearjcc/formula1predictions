<?php

use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\Standings;
use App\Models\Teams;
use App\Models\User;
use App\Services\ChartDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('chart data service can get driver standings progression', function () {
    $service = app(ChartDataService::class);

    $driver1 = Drivers::factory()->create(['name' => 'Lewis', 'surname' => 'Hamilton']);
    $driver2 = Drivers::factory()->create(['name' => 'Max', 'surname' => 'Verstappen']);

    Races::factory()->create([
        'season' => 2024,
        'round' => 1,
        'race_name' => 'Bahrain GP',
        'results' => [
            ['driver_id' => $driver1->id, 'position' => 1],
            ['driver_id' => $driver2->id, 'position' => 2],
        ],
    ]);

    Races::factory()->create([
        'season' => 2024,
        'round' => 2,
        'race_name' => 'Saudi GP',
        'results' => [
            ['driver_id' => $driver2->id, 'position' => 1],
            ['driver_id' => $driver1->id, 'position' => 2],
        ],
    ]);

    $data = $service->getDriverStandingsProgression(2024);

    expect($data)->toHaveCount(2)
        ->and($data[0]['race'])->toBe('Bahrain GP')
        ->and($data[0]['Lewis Hamilton'])->toBe(1)
        ->and($data[1]['Max Verstappen'])->toBe(1);
});

test('chart data service can get team standings progression', function () {
    $service = app(ChartDataService::class);

    $team1 = Teams::factory()->create(['team_name' => 'Mercedes']);
    $team2 = Teams::factory()->create(['team_name' => 'Red Bull']);

    $driver1 = Drivers::factory()->create(['team_id' => $team1->id]);
    $driver2 = Drivers::factory()->create(['team_id' => $team2->id]);

    Races::factory()->create([
        'season' => 2024,
        'round' => 1,
        'race_name' => 'Bahrain GP',
        'results' => [
            ['driver_id' => $driver1->id, 'position' => 1],
            ['driver_id' => $driver2->id, 'position' => 2],
        ],
    ]);

    $data = $service->getTeamStandingsProgression(2024);

    expect($data)->toHaveCount(1)
        ->and($data[0]['Mercedes'])->toBe(1)
        ->and($data[0]['Red Bull'])->toBe(2);
});

test('chart data service can get driver performance comparison', function () {
    $service = app(ChartDataService::class);

    $team = Teams::factory()->create(['team_name' => 'Mercedes']);
    $driver = Drivers::factory()->create([
        'name' => 'Lewis',
        'surname' => 'Hamilton',
        'team_id' => $team->id,
    ]);

    Standings::factory()->create([
        'entity_id' => (string) $driver->id,
        'entity_name' => $driver->name.' '.$driver->surname,
        'type' => 'drivers',
        'season' => 2024,
        'round' => null,
        'points' => 100,
        'position' => 1,
        'wins' => 3,
        'podiums' => 5,
    ]);

    $data = $service->getDriverPerformanceComparison(2024);

    expect($data)->toHaveCount(1)
        ->and($data[0]['driver'])->toBe('Lewis Hamilton')
        ->and($data[0]['team'])->toBe('Mercedes');
});

test('chart data service can get team performance comparison', function () {
    $service = app(ChartDataService::class);

    $team = Teams::factory()->create(['team_name' => 'Mercedes']);

    Standings::factory()->create([
        'entity_id' => (string) $team->id,
        'entity_name' => $team->team_name,
        'type' => 'constructors',
        'season' => 2024,
        'round' => null,
        'points' => 200,
        'position' => 1,
        'wins' => 5,
        'podiums' => 10,
    ]);

    $data = $service->getTeamPerformanceComparison(2024);

    expect($data)->toHaveCount(1)
        ->and($data[0]['team'])->toBe('Mercedes')
        ->and($data[0]['position'])->toBe(1);
});

test('chart data service can get head-to-head comparison', function () {
    $service = app(ChartDataService::class);

    $user1 = User::factory()->create(['name' => 'User One']);
    $user2 = User::factory()->create(['name' => 'User Two']);
    $race = Races::factory()->create(['season' => 2024, 'round' => 1]);

    Prediction::factory()->create([
        'user_id' => $user1->id,
        'race_id' => $race->id,
        'race_round' => 1,
        'season' => 2024,
        'type' => 'race',
        'status' => 'scored',
        'score' => 20,
    ]);
    Prediction::factory()->create([
        'user_id' => $user2->id,
        'race_id' => $race->id,
        'race_round' => 1,
        'season' => 2024,
        'type' => 'race',
        'status' => 'scored',
        'score' => 18,
    ]);

    $data = $service->getHeadToHeadComparison([$user1->id, $user2->id], 2024);

    expect($data)->toHaveCount(2)
        ->and($data[0]['user'])->toBe('User One')
        ->and($data[0]['total_score'])->toBe(20)
        ->and($data[0]['avg_score'])->toBe(20.0)
        ->and($data[1]['user'])->toBe('User Two');
});

test('chart data service get head-to-head comparison returns empty for empty user ids', function () {
    $service = app(ChartDataService::class);

    expect($service->getHeadToHeadComparison([], 2024))->toBeEmpty();
});

test('chart data service can get head-to-head score progression', function () {
    $service = app(ChartDataService::class);

    $user1 = User::factory()->create(['name' => 'Alice']);
    $user2 = User::factory()->create(['name' => 'Bob']);

    $race1 = Races::factory()->create([
        'season' => 2024,
        'round' => 1,
        'race_name' => 'Bahrain GP',
        'results' => [['driver_id' => 1, 'position' => 1]],
    ]);
    $race2 = Races::factory()->create([
        'season' => 2024,
        'round' => 2,
        'race_name' => 'Saudi GP',
        'results' => [['driver_id' => 1, 'position' => 1]],
    ]);

    Prediction::factory()->create([
        'user_id' => $user1->id,
        'race_id' => $race1->id,
        'race_round' => 1,
        'season' => 2024,
        'type' => 'race',
        'status' => 'scored',
        'score' => 10,
    ]);
    Prediction::factory()->create([
        'user_id' => $user1->id,
        'race_id' => $race2->id,
        'race_round' => 2,
        'season' => 2024,
        'type' => 'race',
        'status' => 'scored',
        'score' => 15,
    ]);
    Prediction::factory()->create([
        'user_id' => $user2->id,
        'race_id' => $race1->id,
        'race_round' => 1,
        'season' => 2024,
        'type' => 'race',
        'status' => 'scored',
        'score' => 8,
    ]);

    $data = $service->getHeadToHeadScoreProgression([$user1->id, $user2->id], 2024);

    expect($data)->toHaveCount(2)
        ->and($data[0]['Alice'])->toBe(10)
        ->and($data[1]['Alice'])->toBe(25)
        ->and($data[1]['Bob'])->toBe(8);
});

test('chart data service can get chart configuration', function () {
    $service = app(ChartDataService::class);

    $config = $service->getChartConfig('line', []);

    expect($config)->toHaveKey('type')
        ->and($config)->toHaveKey('data')
        ->and($config)->toHaveKey('options')
        ->and($config['type'])->toBe('line');
});

test('chart data service calculates F1 points correctly', function () {
    $service = app(ChartDataService::class);

    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('calculatePoints');

    expect($method->invoke($service, 0))->toBe(25)
        ->and($method->invoke($service, 1))->toBe(18)
        ->and($method->invoke($service, 9))->toBe(1)
        ->and($method->invoke($service, 10))->toBe(0);
});

test('team points progression avoids excessive driver queries', function () {
    $service = app(ChartDataService::class);

    $team = Teams::factory()->create();
    $drivers = Drivers::factory()->count(3)->create([
        'team_id' => $team->id,
    ]);

    Races::factory()->create([
        'season' => 2024,
        'round' => 1,
        'race_name' => 'Test GP',
        'results' => [
            ['driver_id' => $drivers[0]->id, 'position' => 0],
            ['driver_id' => $drivers[1]->id, 'position' => 1],
            ['driver_id' => $drivers[2]->id, 'position' => 2],
        ],
    ]);

    DB::enableQueryLog();
    DB::flushQueryLog();

    $service->getTeamPointsProgression(2024);

    expect(count(DB::getQueryLog()))->toBeLessThanOrEqual(10);
});
