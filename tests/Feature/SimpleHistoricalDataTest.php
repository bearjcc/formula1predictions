<?php

use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\Teams;
use App\Models\User;
use Database\Seeders\HistoricalPredictionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BacktestScoringHarness;

uses(RefreshDatabase::class);

test('historical predictions seeder creates users', function () {
    // Run the seeder
    $seeder = new HistoricalPredictionsSeeder;
    $seeder->run();

    // Verify that users were created
    expect(User::where('email', 'bearjcc@example.com')->exists())->toBeTrue();
    expect(User::where('email', 'sunny@example.com')->exists())->toBeTrue();
    expect(User::where('email', 'ccaswell@example.com')->exists())->toBeTrue();
    expect(User::where('email', 'chatgpt@example.com')->exists())->toBeTrue();
});

test('historical predictions seeder handles missing files gracefully', function () {
    // Run the seeder (should not fail even if files don't exist)
    $seeder = new HistoricalPredictionsSeeder;
    $seeder->run();

    // Should still create users
    expect(User::where('email', 'bearjcc@example.com')->exists())->toBeTrue();
});

test('seeder can create predictions when data exists', function () {
    // Create test data first
    $drivers = [];
    for ($i = 1; $i <= 20; $i++) {
        $drivers[] = Drivers::factory()->create(['driver_id' => $i]);
    }

    $teams = [];
    for ($i = 1; $i <= 10; $i++) {
        $teams[] = Teams::factory()->create(['team_id' => $i]);
    }

    // Create a simple test prediction file
    $testFile = 'previous/predictions.2022.bearjcc.md';
    $testContent = "# Formula 1 2022 Predictions\n\n## Bahrain\nFL -> Max Verstappen\nMax Verstappen\nCharles Leclerc\nLewis Hamilton\n\n## Preseason\n\n### Team Championship Order\nRed Bull Racing\nMercedes\nFerrari\n\n### Drivers\nMax Verstappen\nCharles Leclerc\nLewis Hamilton";

    // Create directory if it doesn't exist
    if (! is_dir('previous')) {
        mkdir('previous', 0755, true);
    }

    file_put_contents($testFile, $testContent);

    // Run the seeder
    $seeder = new HistoricalPredictionsSeeder;
    $seeder->run();

    // Verify that predictions were created
    expect(Prediction::count())->toBeGreaterThan(0);

    // Clean up test file
    if (file_exists($testFile)) {
        unlink($testFile);
    }
});

test('backtest harness runs against sample historical-style predictions', function () {
    $user = User::factory()->create();
    $race = Races::factory()->create([
        'status' => 'completed',
        'results' => [
            ['driver' => ['driverId' => 'max_verstappen'], 'status' => 'finished'],
            ['driver' => ['driverId' => 'charles_leclerc'], 'status' => 'finished'],
            ['driver' => ['driverId' => 'lewis_hamilton'], 'status' => 'finished'],
            ['driver' => ['driverId' => 'carlos_sainz'], 'status' => 'finished'],
        ],
    ]);

    $predictions = [];
    $predictions[] = Prediction::factory()->create([
        'user_id' => $user->id,
        'race_id' => $race->id,
        'type' => 'race',
        'season' => $race->season,
        'race_round' => $race->round,
        'prediction_data' => [
            'driver_order' => ['max_verstappen', 'charles_leclerc', 'lewis_hamilton', 'carlos_sainz'],
        ],
        'status' => 'submitted',
    ]);

    $harness = new BacktestScoringHarness;
    $result = $harness->compareVariants($predictions, 'flatter');

    expect($result['production_scores'])->toHaveCount(1);
    expect($result['alternative_scores'])->toHaveCount(1);
    $prodScore = $result['production_scores'][$predictions[0]->id];
    $altScore = $result['alternative_scores'][$predictions[0]->id];
    expect($prodScore)->toBe(150);
    expect($altScore)->toBeGreaterThan(0);
    expect($result['score_deltas'][$predictions[0]->id])->toBe($altScore - $prodScore);
});
