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

    // Create test data
    $driver1 = Drivers::factory()->create(['name' => 'Lewis', 'surname' => 'Hamilton']);
    $driver2 = Drivers::factory()->create(['name' => 'Max', 'surname' => 'Verstappen']);

    $race1 = Races::factory()->create([
        'season' => 2024,
        'round' => 1,
        'race_name' => 'Bahrain GP',
        'results' => [
            ['driver_id' => $driver1->id, 'position' => 1],
            ['driver_id' => $driver2->id, 'position' => 2],
        ],
    ]);

    $race2 = Races::factory()->create([
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
        ->and($data[0]['Max Verstappen'])->toBe(2)
        ->and($data[1]['race'])->toBe('Saudi GP')
        ->and($data[1]['Lewis Hamilton'])->toBe(2)
        ->and($data[1]['Max Verstappen'])->toBe(1);
});

test('chart data service can get team standings progression', function () {
    $service = app(ChartDataService::class);

    // Create test data
    $team1 = Teams::factory()->create(['team_name' => 'Mercedes']);
    $team2 = Teams::factory()->create(['team_name' => 'Red Bull']);

    $driver1 = Drivers::factory()->create(['name' => 'Lewis', 'surname' => 'Hamilton', 'team_id' => $team1->id]);
    $driver2 = Drivers::factory()->create(['name' => 'Max', 'surname' => 'Verstappen', 'team_id' => $team2->id]);

    $race1 = Races::factory()->create([
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
        ->and($data[0]['race'])->toBe('Bahrain GP')
        ->and($data[0]['Mercedes'])->toBe(1)
        ->and($data[0]['Red Bull'])->toBe(2);
});

test('chart data service can get user prediction accuracy trends', function () {
    $service = app(ChartDataService::class);

    $user = User::factory()->create(['name' => 'John Doe']);
    $race = Races::factory()->create(['season' => 2024]);

    $prediction1 = Prediction::factory()->create([
        'user_id' => $user->id,
        'race_id' => $race->id,
        'season' => 2024,
        'type' => 'race',
        'accuracy' => 85.5,
        'score' => 95,
        'submitted_at' => now()->subDays(7),
    ]);

    $prediction2 = Prediction::factory()->create([
        'user_id' => $user->id,
        'race_id' => $race->id,
        'season' => 2024,
        'type' => 'race',
        'accuracy' => 92.0,
        'score' => 100,
        'submitted_at' => now()->subDays(3),
    ]);

    $data = $service->getUserPredictionAccuracyTrends($user, 2024);

    expect($data)->toHaveCount(2)
        ->and($data[0]['accuracy'])->toBe(85.5)
        ->and($data[0]['score'])->toBe(95)
        ->and($data[0]['type'])->toBe('race')
        ->and($data[1]['accuracy'])->toBe(92.0)
        ->and($data[1]['score'])->toBe(100);
});

test('chart data service can get prediction accuracy comparison', function () {
    $service = app(ChartDataService::class);

    $user1 = User::factory()->create(['name' => 'John Doe']);
    $user2 = User::factory()->create(['name' => 'Jane Smith']);
    $race = Races::factory()->create(['season' => 2024]);

    // User 1 predictions
    Prediction::factory()->create([
        'user_id' => $user1->id,
        'race_id' => $race->id,
        'season' => 2024,
        'accuracy' => 80.0,
        'score' => 90,
    ]);

    Prediction::factory()->create([
        'user_id' => $user1->id,
        'race_id' => $race->id,
        'season' => 2024,
        'accuracy' => 90.0,
        'score' => 100,
    ]);

    // User 2 predictions
    Prediction::factory()->create([
        'user_id' => $user2->id,
        'race_id' => $race->id,
        'season' => 2024,
        'accuracy' => 95.0,
        'score' => 105,
    ]);

    $data = $service->getPredictionAccuracyComparison(2024);

    expect($data)->toHaveCount(2)
        ->and($data[0]['user'])->toBe('Jane Smith') // Higher accuracy first
        ->and($data[0]['avg_accuracy'])->toBe(95.0)
        ->and($data[0]['total_score'])->toBe(105)
        ->and($data[0]['total_predictions'])->toBe(1)
        ->and($data[1]['user'])->toBe('John Doe')
        ->and($data[1]['avg_accuracy'])->toBe(85.0)
        ->and($data[1]['total_score'])->toBe(190)
        ->and($data[1]['total_predictions'])->toBe(2);
});

test('chart data service can get race prediction accuracy by race', function () {
    $service = app(ChartDataService::class);

    $race1 = Races::factory()->create([
        'season' => 2024,
        'race_name' => 'Bahrain GP',
        'round' => 1,
        'results' => [['driver_id' => 1, 'position' => 1]],
    ]);

    $race2 = Races::factory()->create([
        'season' => 2024,
        'race_name' => 'Saudi GP',
        'round' => 2,
        'results' => [['driver_id' => 1, 'position' => 1]],
    ]);

    // Predictions for race 1
    Prediction::factory()->create([
        'race_id' => $race1->id,
        'type' => 'race',
        'accuracy' => 85.0,
    ]);

    Prediction::factory()->create([
        'race_id' => $race1->id,
        'type' => 'race',
        'accuracy' => 95.0,
    ]);

    // Predictions for race 2
    Prediction::factory()->create([
        'race_id' => $race2->id,
        'type' => 'race',
        'accuracy' => 90.0,
    ]);

    $data = $service->getRacePredictionAccuracyByRace(2024);

    expect($data)->toHaveCount(2)
        ->and($data[0]['race'])->toBe('Bahrain GP')
        ->and($data[0]['avg_accuracy'])->toBe(90.0)
        ->and($data[0]['total_predictions'])->toBe(2)
        ->and($data[1]['race'])->toBe('Saudi GP')
        ->and($data[1]['avg_accuracy'])->toBe(90.0)
        ->and($data[1]['total_predictions'])->toBe(1);
});

test('chart data service can get driver performance comparison', function () {
    $service = app(ChartDataService::class);

    $team = Teams::factory()->create(['team_name' => 'Mercedes']);
    $driver = Drivers::factory()->create([
        'name' => 'Lewis',
        'surname' => 'Hamilton',
        'team_id' => $team->id,
    ]);

    $standings = Standings::factory()->create([
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
        ->and($data[0]['team'])->toBe('Mercedes')
        ->and($data[0]['points'])->toBe('100.00')
        ->and($data[0]['position'])->toBe(1)
        ->and($data[0]['wins'])->toBe(3)
        ->and($data[0]['podiums'])->toBe(5);
});

test('chart data service can get team performance comparison', function () {
    $service = app(ChartDataService::class);

    $team = Teams::factory()->create(['team_name' => 'Mercedes']);

    $standings = Standings::factory()->create([
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
        ->and($data[0]['points'])->toBe('200.00')
        ->and($data[0]['position'])->toBe(1)
        ->and($data[0]['wins'])->toBe(5)
        ->and($data[0]['podiums'])->toBe(10);
});

test('chart data service returns empty arrays when no data exists', function () {
    $service = app(ChartDataService::class);

    expect($service->getDriverStandingsProgression(2024))->toBe([])
        ->and($service->getTeamStandingsProgression(2024))->toBe([])
        ->and($service->getPredictionAccuracyComparison(2024))->toBe([])
        ->and($service->getRacePredictionAccuracyByRace(2024))->toBe([])
        ->and($service->getDriverPerformanceComparison(2024))->toBe([])
        ->and($service->getTeamPerformanceComparison(2024))->toBe([]);
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

    // Test using reflection to access private method
    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('calculatePoints');
    $method->setAccessible(true);

    expect($method->invoke($service, 0))->toBe(25) // 1st place
        ->and($method->invoke($service, 1))->toBe(18) // 2nd place
        ->and($method->invoke($service, 2))->toBe(15) // 3rd place
        ->and($method->invoke($service, 9))->toBe(1)  // 10th place
        ->and($method->invoke($service, 10))->toBe(0); // 11th place
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

    $queries = DB::getQueryLog();

    expect(count($queries))->toBeLessThanOrEqual(10);
});
