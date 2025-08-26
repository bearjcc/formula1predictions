<?php

use App\Models\User;
use App\Models\Prediction;
use App\Models\Drivers;
use App\Models\Teams;
use App\Models\Races;
use Database\Seeders\HistoricalPredictionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('historical predictions seeder can import data', function () {
    // Create some test drivers and teams first with unique IDs
    $drivers = [];
    $driverNames = [
        'Max Verstappen', 'Charles Leclerc', 'Lewis Hamilton', 'Carlos Sainz', 'Sergio Perez',
        'George Russell', 'Fernando Alonso', 'Lance Stroll', 'Lando Norris', 'Oscar Piastri',
        'Valtteri Bottas', 'Zhou Guanyu', 'Pierre Gasly', 'Esteban Ocon', 'Kevin Magnussen',
        'Nico Hulkenberg', 'Alex Albon', 'Yuki Tsunoda', 'Logan Sargeant', 'Daniel Ricciardo'
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
        'McLaren', 'Alpine', 'Haas F1 Team', 'Williams', 'AlphaTauri'
    ];
    
    foreach ($teamNames as $index => $teamName) {
        $teams[$teamName] = Teams::factory()->create([
            'team_id' => $index + 1,
            'team_name' => $teamName,
        ]);
    }

    // Run the seeder
    $seeder = new HistoricalPredictionsSeeder();
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

test('historical data import handles missing files gracefully', function () {
    // Create test drivers and teams with unique IDs
    for ($i = 1; $i <= 5; $i++) {
        Drivers::factory()->create(['driver_id' => $i]);
        Teams::factory()->create(['team_id' => $i]);
    }

    // Run the seeder (should not fail even if files don't exist)
    $seeder = new HistoricalPredictionsSeeder();
    $seeder->run();

    // Should still create users
    expect(User::where('email', 'bearjcc@example.com')->exists())->toBeTrue();
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
    $seeder = new HistoricalPredictionsSeeder();
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
    $seeder = new HistoricalPredictionsSeeder();
    $seeder->run();

    // Verify predictions are associated with users
    $predictions = Prediction::with('user')->get();
    
    foreach ($predictions as $prediction) {
        expect($prediction->user)->not->toBeNull();
        expect($prediction->user->email)->toContain('@example.com');
    }
});
