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
    $race1 = Races::factory()->create(['season' => 2024, 'round' => 1]);
    $race2 = Races::factory()->create(['season' => 2024, 'round' => 2]);

    Prediction::factory()->create([
        'user_id' => $user->id,
        'race_id' => $race1->id,
        'race_round' => 1,
        'season' => 2024,
        'type' => 'race',
        'accuracy' => 85.5,
        'score' => 95,
        'submitted_at' => now()->subDays(7),
    ]);

    Prediction::factory()->create([
        'user_id' => $user->id,
        'race_id' => $race2->id,
        'race_round' => 2,
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
    $race1 = Races::factory()->create(['season' => 2024, 'round' => 1]);
    $race2 = Races::factory()->create(['season' => 2024, 'round' => 2]);

    // User 1 predictions (two races to satisfy unique user_id + type + season + race_round)
    Prediction::factory()->create([
        'user_id' => $user1->id,
        'race_id' => $race1->id,
        'race_round' => 1,
        'season' => 2024,
        'type' => 'race',
        'accuracy' => 80.0,
        'score' => 90,
    ]);

    Prediction::factory()->create([
        'user_id' => $user1->id,
        'race_id' => $race2->id,
        'race_round' => 2,
        'season' => 2024,
        'type' => 'race',
        'accuracy' => 90.0,
        'score' => 100,
    ]);

    // User 2 prediction
    Prediction::factory()->create([
        'user_id' => $user2->id,
        'race_id' => $race1->id,
        'race_round' => 1,
        'season' => 2024,
        'type' => 'race',
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

    // Predictions for race 1 (different users to satisfy unique user_id + type + season + race_round)
    Prediction::factory()->create([
        'race_id' => $race1->id,
        'race_round' => 1,
        'season' => 2024,
        'type' => 'race',
        'accuracy' => 85.0,
    ]);

    Prediction::factory()->create([
        'race_id' => $race1->id,
        'race_round' => 1,
        'season' => 2024,
        'type' => 'race',
        'accuracy' => 95.0,
    ]);

    // Prediction for race 2
    Prediction::factory()->create([
        'race_id' => $race2->id,
        'race_round' => 2,
        'season' => 2024,
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

test('chart data service returns getPredictorLuckAndVariance with expected structure', function () {
    $service = app(ChartDataService::class);

    $user1 = User::factory()->create(['name' => 'Alice']);
    $user2 = User::factory()->create(['name' => 'Bob']);
    $race1 = Races::factory()->create(['season' => 2024, 'round' => 1]);
    $race2 = Races::factory()->create(['season' => 2024, 'round' => 2]);

    Prediction::factory()->create([
        'user_id' => $user1->id,
        'race_id' => $race1->id,
        'race_round' => 1,
        'season' => 2024,
        'type' => 'race',
        'accuracy' => 80.0,
        'score' => 20,
    ]);
    Prediction::factory()->create([
        'user_id' => $user1->id,
        'race_id' => $race2->id,
        'race_round' => 2,
        'season' => 2024,
        'type' => 'race',
        'accuracy' => 80.0,
        'score' => 24,
    ]);
    Prediction::factory()->create([
        'user_id' => $user2->id,
        'race_id' => $race1->id,
        'race_round' => 1,
        'season' => 2024,
        'type' => 'race',
        'accuracy' => 100.0,
        'score' => 25,
    ]);

    $data = $service->getPredictorLuckAndVariance(2024);

    expect($data)->toHaveCount(2);
    $bob = collect($data)->firstWhere('user', 'Bob');
    $alice = collect($data)->firstWhere('user', 'Alice');
    expect($bob)->toHaveKeys(['user', 'total_score', 'avg_accuracy', 'prediction_count', 'score_std_dev', 'expected_score', 'luck_index'])
        ->and($bob['total_score'])->toBe(25)
        ->and($bob['avg_accuracy'])->toBe(100.0)
        ->and($bob['prediction_count'])->toBe(1)
        ->and($bob['expected_score'])->toBe(25.0)
        ->and($bob['luck_index'])->toBe(0.0)
        ->and($bob['score_std_dev'])->toBeNull();
    expect($alice['total_score'])->toBe(44)
        ->and($alice['prediction_count'])->toBe(2)
        ->and($alice['avg_accuracy'])->toBe(80.0)
        ->and($alice['expected_score'])->toBe(40.0)
        ->and($alice['luck_index'])->toBe(4.0);
    expect($alice['score_std_dev'])->toBeNumeric();
});

test('chart data service getPredictorLuckAndVariance returns empty for season with no scored predictions', function () {
    $service = app(ChartDataService::class);

    $data = $service->getPredictorLuckAndVariance(2024);

    expect($data)->toBe([]);
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

    // Test using reflection to access private method (PHP 8.1+ no longer requires setAccessible)
    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('calculatePoints');

    expect($method->invoke($service, 0))->toBe(25) // 1st place
        ->and($method->invoke($service, 1))->toBe(18) // 2nd place
        ->and($method->invoke($service, 2))->toBe(15) // 3rd place
        ->and($method->invoke($service, 9))->toBe(1)  // 10th place
        ->and($method->invoke($service, 10))->toBe(0); // 11th place
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
        'accuracy' => 80,
    ]);
    Prediction::factory()->create([
        'user_id' => $user2->id,
        'race_id' => $race->id,
        'race_round' => 1,
        'season' => 2024,
        'type' => 'race',
        'status' => 'scored',
        'score' => 18,
        'accuracy' => 72,
    ]);

    $data = $service->getHeadToHeadComparison([$user1->id, $user2->id], 2024);

    expect($data)->toHaveCount(2)
        ->and($data[0]['user'])->toBe('User One')
        ->and($data[0]['total_score'])->toBe(20)
        ->and($data[0]['avg_accuracy'])->toBe(80.0)
        ->and($data[0]['prediction_count'])->toBe(1)
        ->and($data[1]['user'])->toBe('User Two')
        ->and($data[1]['total_score'])->toBe(18);
});

test('chart data service get head-to-head comparison returns empty for empty user ids', function () {
    $service = app(ChartDataService::class);

    $data = $service->getHeadToHeadComparison([], 2024);

    expect($data)->toBeEmpty();
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
        ->and($data[0]['race'])->toBe('Bahrain GP')
        ->and($data[0]['Alice'])->toBe(10)
        ->and($data[0]['Bob'])->toBe(8)
        ->and($data[1]['race'])->toBe('Saudi GP')
        ->and($data[1]['Alice'])->toBe(25)
        ->and($data[1]['Bob'])->toBe(8);
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
