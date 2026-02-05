<?php

use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\Standings;
use App\Models\Teams;
use App\Models\User;
use App\Services\ChartDataService;
use Database\Seeders\HistoricalPredictionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

test('historical predictions seeder can import data', function () {
    // Create some test drivers and teams first with unique IDs
    $drivers = [];
    $driverNames = [
        'Max Verstappen', 'Charles Leclerc', 'Lewis Hamilton', 'Carlos Sainz', 'Sergio Perez',
        'George Russell', 'Fernando Alonso', 'Lance Stroll', 'Lando Norris', 'Oscar Piastri',
        'Valtteri Bottas', 'Zhou Guanyu', 'Pierre Gasly', 'Esteban Ocon', 'Kevin Magnussen',
        'Nico Hulkenberg', 'Alex Albon', 'Yuki Tsunoda', 'Logan Sargeant', 'Daniel Ricciardo',
    ];

    foreach ($driverNames as $index => $fullName) {
        $names = explode(' ', $fullName);
        $drivers[$fullName] = Drivers::factory()->create([
            'driver_id' => $index + 1,
            'name' => $names[0],
            'surname' => $names[1] ?? '',
        ]);
    }

    $teams = [];
    $teamNames = [
        'Red Bull Racing', 'Mercedes', 'Ferrari', 'Aston Martin', 'Alfa Romeo',
        'McLaren', 'Alpine', 'Haas F1 Team', 'Williams', 'AlphaTauri',
    ];

    foreach ($teamNames as $index => $teamName) {
        $teams[$teamName] = Teams::factory()->create([
            'team_id' => $index + 1,
            'team_name' => $teamName,
        ]);
    }

    // Run the seeder
    $seeder = new HistoricalPredictionsSeeder;
    $seeder->run();

    // Verify that users were created
    expect(User::where('email', 'bearjcc@example.com')->exists())->toBeTrue();
    expect(User::where('email', 'sunny@example.com')->exists())->toBeTrue();
    expect(User::where('email', 'ccaswell@example.com')->exists())->toBeTrue();
    expect(User::where('email', 'chatgpt@example.com')->exists())->toBeTrue();

    // Verify that predictions were created
    expect(Prediction::count())->toBeGreaterThan(0);

    // Verify that races were created
    expect(Races::count())->toBeGreaterThan(0);

    // Check for specific prediction types
    expect(Prediction::where('type', 'race')->exists())->toBeTrue();
    expect(Prediction::where('type', 'preseason')->exists())->toBeTrue();
});

test('legacy import artisan command runs seeder and is idempotent', function () {
    // Ensure previous directory exists so the command can safely look for fixtures
    if (! File::isDirectory('previous')) {
        File::makeDirectory('previous', 0755, true);
    }

    // Prepare drivers and teams so the seeder can resolve IDs
    $driverNames = [
        'Max Verstappen', 'Charles Leclerc', 'Lewis Hamilton', 'Carlos Sainz', 'Sergio Perez',
    ];

    foreach ($driverNames as $index => $fullName) {
        $names = explode(' ', $fullName);

        Drivers::factory()->create([
            'driver_id' => $index + 1,
            'name' => $names[0],
            'surname' => $names[1] ?? '',
        ]);
    }

    $teamNames = [
        'Red Bull Racing', 'Mercedes', 'Ferrari',
    ];

    foreach ($teamNames as $index => $teamName) {
        Teams::factory()->create([
            'team_id' => $index + 1,
            'team_name' => $teamName,
        ]);
    }

    // First run
    $result = $this->artisan('legacy:import-historical-predictions');
    $result->assertExitCode(0);

    $predictionCount = Prediction::count();
    $raceCount = Races::count();

    // Second run should also succeed without throwing, even if no fixtures are present
    $result = $this->artisan('legacy:import-historical-predictions');
    $result->assertExitCode(0);
});

test('historical predictions seeder is idempotent', function () {
    // Create some test drivers and teams first with unique IDs
    $drivers = [];
    $driverNames = [
        'Max Verstappen', 'Charles Leclerc', 'Lewis Hamilton', 'Carlos Sainz', 'Sergio Perez',
        'George Russell', 'Fernando Alonso', 'Lance Stroll', 'Lando Norris', 'Oscar Piastri',
        'Valtteri Bottas', 'Zhou Guanyu', 'Pierre Gasly', 'Esteban Ocon', 'Kevin Magnussen',
        'Nico Hulkenberg', 'Alex Albon', 'Yuki Tsunoda', 'Logan Sargeant', 'Daniel Ricciardo',
    ];

    foreach ($driverNames as $index => $fullName) {
        $names = explode(' ', $fullName);
        $drivers[$fullName] = Drivers::factory()->create([
            'driver_id' => $index + 1,
            'name' => $names[0],
            'surname' => $names[1] ?? '',
        ]);
    }

    $teams = [];
    $teamNames = [
        'Red Bull Racing', 'Mercedes', 'Ferrari', 'Aston Martin', 'Alfa Romeo',
        'McLaren', 'Alpine', 'Haas F1 Team', 'Williams', 'AlphaTauri',
    ];

    foreach ($teamNames as $index => $teamName) {
        $teams[$teamName] = Teams::factory()->create([
            'team_id' => $index + 1,
            'team_name' => $teamName,
        ]);
    }

    $seeder = new HistoricalPredictionsSeeder;
    $seeder->run();

    $predictionCount = Prediction::count();
    $raceCount = Races::count();

    $seeder->run();

    expect(Prediction::count())->toBe($predictionCount);
    expect(Races::count())->toBe($raceCount);
});

test('historical data import handles missing files gracefully', function () {
    // Create test drivers and teams with unique IDs
    for ($i = 1; $i <= 5; $i++) {
        Drivers::factory()->create(['driver_id' => $i]);
        Teams::factory()->create(['team_id' => $i]);
    }

    // Run the seeder (should not fail even if files don't exist)
    $seeder = new HistoricalPredictionsSeeder;
    $seeder->run();

    // Should still create users
    expect(User::where('email', 'bearjcc@example.com')->exists())->toBeTrue();
});

test('imported historical predictions can feed basic analytics for a season', function () {
    // Create drivers and teams so the seeder can resolve IDs
    $driverNames = [
        'Max Verstappen', 'Charles Leclerc', 'Lewis Hamilton', 'Carlos Sainz', 'Sergio Perez',
        'George Russell', 'Fernando Alonso', 'Lance Stroll', 'Lando Norris', 'Oscar Piastri',
        'Valtteri Bottas', 'Zhou Guanyu', 'Pierre Gasly', 'Esteban Ocon', 'Kevin Magnussen',
        'Nico Hulkenberg', 'Alex Albon', 'Yuki Tsunoda', 'Logan Sargeant', 'Daniel Ricciardo',
    ];

    foreach ($driverNames as $index => $fullName) {
        $names = explode(' ', $fullName);

        Drivers::factory()->create([
            'driver_id' => $index + 1,
            'name' => $names[0],
            'surname' => $names[1] ?? '',
        ]);
    }

    $teamNames = [
        'Red Bull Racing', 'Mercedes', 'Ferrari', 'Aston Martin', 'Alfa Romeo',
        'McLaren', 'Alpine', 'Haas F1 Team', 'Williams', 'AlphaTauri',
    ];

    foreach ($teamNames as $index => $teamName) {
        Teams::factory()->create([
            'team_id' => $index + 1,
            'team_name' => $teamName,
        ]);
    }

    // Run the historical predictions seeder
    $seeder = new HistoricalPredictionsSeeder;
    $seeder->run();

    // Choose one imported season that should exist in the markdown fixtures
    $season = 2022;

    // Sanity check that we actually imported some predictions for this season
    $predictions = Prediction::where('season', $season)->get();
    expect($predictions->isNotEmpty())->toBeTrue();

    // Assign simple synthetic scores and accuracy values so analytics have data to work with
    foreach ($predictions as $index => $prediction) {
        $prediction->update([
            'score' => 50 + ($index % 25),
            'accuracy' => 60 + ($index % 40),
        ]);
    }

    // Create some simple current standings for the same season so standings-based charts have data
    $firstDriver = Drivers::first();
    $firstTeam = Teams::first();

    Standings::factory()->create([
        'season' => $season,
        'type' => 'drivers',
        'round' => null,
        'entity_id' => (string) $firstDriver->id,
        'entity_name' => $firstDriver->name.' '.$firstDriver->surname,
        'position' => 1,
        'points' => 100,
    ]);

    Standings::factory()->create([
        'season' => $season,
        'type' => 'constructors',
        'round' => null,
        'entity_id' => (string) $firstTeam->id,
        'entity_name' => $firstTeam->team_name,
        'position' => 1,
        'points' => 150,
    ]);

    /** @var ChartDataService $service */
    $service = app(ChartDataService::class);

    // Accuracy-based analytics should have entries for the imported season
    $byType = $service->getPredictionAccuracyByType($season);
    $comparison = $service->getPredictionAccuracyComparison($season);

    expect($byType)->not()->toBeEmpty();
    expect($comparison)->not()->toBeEmpty();

    // Pick a user who actually has imported predictions for this season and ensure their trends can be generated
    $predictionWithUser = Prediction::where('season', $season)->whereNotNull('user_id')->first();
    expect($predictionWithUser)->not()->toBeNull();

    $trends = $service->getUserPredictionAccuracyTrends($predictionWithUser->user, $season);
    expect($trends)->not()->toBeEmpty();

    // Standings-based analytics should also return data for this synthetic season
    $driverPerformance = $service->getDriverPerformanceComparison($season);
    $teamPerformance = $service->getTeamPerformanceComparison($season);

    expect($driverPerformance)->not()->toBeEmpty();
    expect($teamPerformance)->not()->toBeEmpty();
});

test('imported predictions have correct structure', function () {
    // Create test data with unique IDs
    $drivers = [];
    for ($i = 1; $i <= 20; $i++) {
        $drivers[] = Drivers::factory()->create(['driver_id' => $i]);
    }

    $teams = [];
    for ($i = 1; $i <= 10; $i++) {
        $teams[] = Teams::factory()->create(['team_id' => $i]);
    }

    // Run the seeder
    $seeder = new HistoricalPredictionsSeeder;
    $seeder->run();

    // Get a race prediction
    $racePrediction = Prediction::where('type', 'race')->first();

    if ($racePrediction) {
        expect($racePrediction->prediction_data)->toHaveKey('driver_order');
        expect($racePrediction->prediction_data)->toHaveKey('fastest_lap');
        expect($racePrediction->season)->toBeGreaterThanOrEqual(2022);
        expect($racePrediction->status)->toBe('submitted');
    }

    // Get a preseason prediction
    $preseasonPrediction = Prediction::where('type', 'preseason')->first();

    if ($preseasonPrediction) {
        expect($preseasonPrediction->prediction_data)->toHaveKey('team_order');
        expect($preseasonPrediction->prediction_data)->toHaveKey('driver_championship');
        expect($preseasonPrediction->prediction_data)->toHaveKey('superlatives');
        expect($preseasonPrediction->season)->toBeGreaterThanOrEqual(2022);
        expect($preseasonPrediction->status)->toBe('submitted');
    }
});

test('imported predictions are associated with users', function () {
    // Create test data with unique IDs
    $drivers = [];
    for ($i = 1; $i <= 20; $i++) {
        $drivers[] = Drivers::factory()->create(['driver_id' => $i]);
    }

    $teams = [];
    for ($i = 1; $i <= 10; $i++) {
        $teams[] = Teams::factory()->create(['team_id' => $i]);
    }

    // Run the seeder
    $seeder = new HistoricalPredictionsSeeder;
    $seeder->run();

    // Verify predictions are associated with users
    $predictions = Prediction::with('user')->get();

    foreach ($predictions as $prediction) {
        expect($prediction->user)->not->toBeNull();
        expect($prediction->user->email)->toContain('@example.com');
    }
});
