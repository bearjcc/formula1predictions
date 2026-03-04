<?php

use App\Models\Drivers;
use App\Services\Import\DriverImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('driver import service creates new drivers', function () {
    /** @var DriverImportService $service */
    $service = app(DriverImportService::class);

    $rows = [
        [
            'driver_id' => '1',
            'name' => 'Max',
            'surname' => 'Verstappen',
            'nationality' => 'Dutch',
            'is_active' => true,
        ],
    ];

    $result = $service->import($rows, false);

    expect($result->created)->toBe(1)
        ->and($result->updated)->toBe(0)
        ->and($result->errors)->toBe([]);

    $driver = Drivers::where('driver_id', '1')->first();
    expect($driver)->not()->toBeNull();
    expect($driver->name)->toBe('Max');
    expect($driver->surname)->toBe('Verstappen');
});

test('driver import service updates existing drivers', function () {
    Drivers::factory()->create([
        'driver_id' => '44',
        'name' => 'Lewis',
        'surname' => 'Hamilton',
        'nationality' => 'British',
    ]);

    /** @var DriverImportService $service */
    $service = app(DriverImportService::class);

    $rows = [
        [
            'driver_id' => '44',
            'name' => 'Lewis',
            'surname' => 'Hamilton',
            'nationality' => 'British',
            'is_active' => false,
        ],
    ];

    $result = $service->import($rows, false);

    expect($result->created)->toBe(0)
        ->and($result->updated)->toBe(1)
        ->and($result->errors)->toBe([]);

    $driver = Drivers::where('driver_id', '44')->first();
    expect($driver)->not()->toBeNull();
    expect($driver->is_active)->toBeFalse();
});

test('driver import service supports dry run without writing to database', function () {
    /** @var DriverImportService $service */
    $service = app(DriverImportService::class);

    $rows = [
        [
            'driver_id' => '16',
            'name' => 'Charles',
            'surname' => 'Leclerc',
            'nationality' => 'Monégasque',
            'is_active' => true,
        ],
    ];

    $result = $service->import($rows, true);

    expect($result->created)->toBe(1)
        ->and($result->updated)->toBe(0);

    expect(Drivers::where('driver_id', '16')->exists())->toBeFalse();
});

test('driver import service collects validation errors', function () {
    /** @var DriverImportService $service */
    $service = app(DriverImportService::class);

    $rows = [
        [
            'driver_id' => '',
            'name' => 'Missing Id',
        ],
        [
            'driver_id' => '99',
            'name' => '',
        ],
    ];

    $result = $service->import($rows, false);

    expect($result->created)->toBe(0)
        ->and($result->updated)->toBe(0)
        ->and($result->skipped)->toBe(2)
        ->and(count($result->errors))->toBe(2);
});
