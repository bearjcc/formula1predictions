<?php

use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\User;
use App\Services\F1ApiService;
use Database\Seeders\BotPredictionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)->group('slow');

test('bot predictions seeder creates LastBot user', function () {
    $mockF1Service = Mockery::mock(F1ApiService::class);
    $mockF1Service->shouldReceive('getRacesForYear')
        ->andReturn([]);

    app()->instance(F1ApiService::class, $mockF1Service);

    $seeder = new BotPredictionsSeeder($mockF1Service);
    $seeder->run();

    $bot = User::where('email', 'lastbot@example.com')->first();
    expect($bot)->not->toBeNull();
    expect($bot->name)->toBe('LastBot');
});

test('bot predictions seeder creates predictions for multiple seasons', function () {
    $mockRaceData2021 = [
        [
            'round' => 1,
            'results' => [
                ['driver' => ['id' => 'hamilton']],
                ['driver' => ['id' => 'max_verstappen']],
            ],
        ],
    ];
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

    $mockF1Service = Mockery::mock(F1ApiService::class);
    $mockF1Service->shouldReceive('getRacesForYear')
        ->with(2021)
        ->andReturn($mockRaceData2021);
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

    // Round 1 uses last race of previous year (2021 round 1)
    $predR1 = Prediction::where('season', 2022)->where('race_round', 1)->where('type', 'race')->first();
    expect($predR1)->not->toBeNull();
    expect($predR1->prediction_data['driver_order'])->toHaveCount(2);

    // Round 2 uses 2022 round 1 results
    $predR2 = Prediction::where('season', 2022)->where('race_round', 2)->where('type', 'race')->first();
    expect($predR2)->not->toBeNull();
    expect($predR2->prediction_data['driver_order'])->toHaveCount(3);
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
    $mockF1Service = Mockery::mock(F1ApiService::class);
    $mockF1Service->shouldReceive('getRacesForYear')
        ->with(2021)
        ->andReturn([]);
    $mockF1Service->shouldReceive('getRacesForYear')
        ->with(2022)
        ->andReturn([
            ['round' => 1, 'results' => []],
            ['round' => 2, 'results' => [['driver' => ['id' => 'max_verstappen']]]],
        ]);
    $mockF1Service->shouldReceive('getRacesForYear')
        ->with(2023)
        ->andReturn([]);
    $mockF1Service->shouldReceive('getRacesForYear')
        ->with(2024)
        ->andReturn([]);

    app()->instance(F1ApiService::class, $mockF1Service);

    $seeder = new BotPredictionsSeeder($mockF1Service);
    $seeder->run();

    expect(Prediction::where('season', 2022)->where('race_round', 1)->first())->toBeNull();
    expect(Prediction::where('season', 2022)->where('race_round', 2)->first())->toBeNull();
});

test('bot predictions seeder creates predictions with correct structure', function () {
    $mockRaceData2021 = [
        ['round' => 1, 'results' => [['driver' => ['id' => 'driver0']]]],
    ];
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
        ->with(2021)
        ->andReturn($mockRaceData2021);
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
    $mockRaceData = [
        ['round' => 1, 'results' => [['driver' => ['id' => 'driver1']]]],
        ['round' => 2, 'results' => [['driver' => ['id' => 'driver2']]]],
    ];

    $mockF1Service = Mockery::mock(F1ApiService::class);
    $mockF1Service->shouldReceive('getRacesForYear')
        ->with(2021)
        ->andReturn([['round' => 1, 'results' => [['driver' => ['id' => 'd0']]]]]);
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
    $seeder->run();
    $seeder->run();

    expect(User::where('email', 'lastbot@example.com')->count())->toBe(1);
    expect(Prediction::where('type', 'race')->where('season', 2022)->count())->toBe(2);
});
