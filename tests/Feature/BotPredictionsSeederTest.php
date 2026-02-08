<?php

use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\User;
use App\Services\F1ApiService;
use Database\Seeders\BotPredictionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('bot predictions seeder creates lastracebot user', function () {
    // Mock F1 API service
    $mockF1Service = Mockery::mock(F1ApiService::class);
    $mockF1Service->shouldReceive('getRacesForYear')
        ->andReturn([]);

    app()->instance(F1ApiService::class, $mockF1Service);

    $seeder = new BotPredictionsSeeder($mockF1Service);
    $seeder->run();

    // Verify bot user was created
    $bot = User::where('email', 'lastracebot@example.com')->first();
    expect($bot)->not->toBeNull();
    expect($bot->name)->toBe('LastRaceBot');
});

test('bot predictions seeder creates predictions for multiple seasons', function () {
    // Mock race data for 2022
    $mockRaceData2022 = [
        [
            'round' => 1,
            'results' => [
                ['driver' => ['id' => 'max_verstappen']],
                ['driver' => ['id' => 'charles_leclerc']],
                ['driver' => ['id' => 'carlos_sainz']],
            ],
        ],
        [
            'round' => 2,
            'results' => [
                ['driver' => ['id' => 'lewis_hamilton']],
                ['driver' => ['id' => 'george_russell']],
                ['driver' => ['id' => 'lando_norris']],
            ],
        ],
    ];

    // Mock F1 API service
    $mockF1Service = Mockery::mock(F1ApiService::class);
    $mockF1Service->shouldReceive('getRacesForYear')
        ->with(2022)
        ->andReturn($mockRaceData2022);
    $mockF1Service->shouldReceive('getRacesForYear')
        ->with(2023)
        ->andReturn([]);
    $mockF1Service->shouldReceive('getRacesForYear')
        ->with(2024)
        ->andReturn([]);

    app()->instance(F1ApiService::class, $mockF1Service);

    $seeder = new BotPredictionsSeeder($mockF1Service);
    $seeder->run();

    // Should create prediction for round 2 (using round 1 results)
    $prediction = Prediction::where('season', 2022)
        ->where('race_round', 2)
        ->where('type', 'race')
        ->first();

    expect($prediction)->not->toBeNull();
    expect($prediction->prediction_data['driver_order'])->toHaveCount(3);
});

test('bot predictions seeder creates driver placeholders when needed', function () {
    // Mock race data with unknown driver IDs
    $mockRaceData = [
        [
            'round' => 1,
            'results' => [
                ['driver' => ['id' => 'unknown_driver_1']],
                ['driver' => ['id' => 'unknown_driver_2']],
            ],
        ],
        [
            'round' => 2,
            'results' => [
                ['driver' => ['id' => 'unknown_driver_3']],
            ],
        ],
    ];

    $mockF1Service = Mockery::mock(F1ApiService::class);
    $mockF1Service->shouldReceive('getRacesForYear')
        ->andReturn($mockRaceData);

    app()->instance(F1ApiService::class, $mockF1Service);

    $seeder = new BotPredictionsSeeder($mockF1Service);
    $seeder->run();

    // Should create placeholder drivers for round 2 prediction (using round 1 results)
    $placeholderDrivers = Drivers::whereIn('driver_id', ['unknown_driver_1', 'unknown_driver_2'])->get();
    expect($placeholderDrivers)->toHaveCount(2);

    foreach ($placeholderDrivers as $driver) {
        expect($driver->name)->toBe($driver->driver_id);
        expect($driver->nationality)->toBe('Unknown');
    }
});

test('bot predictions seeder uses existing drivers when available', function () {
    // Create existing driver
    $existingDriver = Drivers::factory()->create([
        'driver_id' => 'max_verstappen',
        'name' => 'Max',
        'surname' => 'Verstappen',
    ]);

    // Mock race data
    $mockRaceData = [
        [
            'round' => 1,
            'results' => [
                ['driver' => ['id' => 'max_verstappen']],
            ],
        ],
    ];

    $mockF1Service = Mockery::mock(F1ApiService::class);
    $mockF1Service->shouldReceive('getRacesForYear')
        ->andReturn($mockRaceData);

    app()->instance(F1ApiService::class, $mockF1Service);

    $seeder = new BotPredictionsSeeder($mockF1Service);
    $seeder->run();

    // Should use existing driver, not create duplicate
    $drivers = Drivers::where('driver_id', 'max_verstappen')->get();
    expect($drivers)->toHaveCount(1);
    expect($drivers->first()->id)->toBe($existingDriver->id);
});

test('bot predictions seeder handles empty race results gracefully', function () {
    // Mock race data with no results
    $mockRaceData = [
        [
            'round' => 1,
            'results' => [],
        ],
        [
            'round' => 2,
            'results' => [
                ['driver' => ['id' => 'max_verstappen']],
            ],
        ],
    ];

    $mockF1Service = Mockery::mock(F1ApiService::class);
    $mockF1Service->shouldReceive('getRacesForYear')
        ->andReturn($mockRaceData);

    app()->instance(F1ApiService::class, $mockF1Service);

    $seeder = new BotPredictionsSeeder($mockF1Service);
    $seeder->run();

    // Should not create prediction for round 1 (no previous results)
    $prediction1 = Prediction::where('season', 2022)
        ->where('race_round', 1)
        ->first();
    expect($prediction1)->toBeNull();

    // Should create prediction for round 2 (using round 1 empty results = no prediction)
    $prediction2 = Prediction::where('season', 2022)
        ->where('race_round', 2)
        ->first();
    expect($prediction2)->toBeNull();
});

test('bot predictions seeder creates predictions with correct structure', function () {
    // Mock race data
    $mockRaceData = [
        [
            'round' => 1,
            'results' => [
                ['driver' => ['id' => 'driver1']],
                ['driver' => ['id' => 'driver2']],
            ],
        ],
        [
            'round' => 2,
            'results' => [
                ['driver' => ['id' => 'driver3']],
            ],
        ],
    ];

    $mockF1Service = Mockery::mock(F1ApiService::class);
    $mockF1Service->shouldReceive('getRacesForYear')
        ->andReturn($mockRaceData);

    app()->instance(F1ApiService::class, $mockF1Service);

    $seeder = new BotPredictionsSeeder($mockF1Service);
    $seeder->run();

    $prediction = Prediction::where('season', 2022)
        ->where('race_round', 2)
        ->first();

    expect($prediction)->not->toBeNull();
    expect($prediction->type)->toBe('race');
    expect($prediction->status)->toBe('submitted');
    expect($prediction->prediction_data)->toHaveKey('driver_order');
    expect($prediction->prediction_data['driver_order'])->toHaveCount(2);
});

test('bot predictions seeder can be run multiple times safely', function () {
    // Mock race data with two races so we get a prediction
    $mockRaceData = [
        [
            'round' => 1,
            'results' => [
                ['driver' => ['id' => 'driver1']],
            ],
        ],
        [
            'round' => 2,
            'results' => [
                ['driver' => ['id' => 'driver2']],
            ],
        ],
    ];

    $mockF1Service = Mockery::mock(F1ApiService::class);
    $mockF1Service->shouldReceive('getRacesForYear')
        ->with(2022)
        ->andReturn($mockRaceData);
    $mockF1Service->shouldReceive('getRacesForYear')
        ->with(2023)
        ->andReturn([]);
    $mockF1Service->shouldReceive('getRacesForYear')
        ->with(2024)
        ->andReturn([]);

    app()->instance(F1ApiService::class, $mockF1Service);

    $seeder = new BotPredictionsSeeder($mockF1Service);

    // Run twice
    $seeder->run();
    $seeder->run();

    // Should only have one bot user
    expect(User::where('email', 'lastracebot@example.com')->count())->toBe(1);

    // Should only have one prediction (due to updateOrCreate) for 2022 season
    expect(Prediction::where('type', 'race')->where('season', 2022)->count())->toBe(1);
});
