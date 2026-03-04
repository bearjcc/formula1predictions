<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

test('legacy:import-drivers imports drivers from csv', function () {
    $relativePath = 'drivers_legacy_test.csv';
    $csvPath = base_path($relativePath);

    $csvContent = implode(PHP_EOL, [
        'driver_id,name,surname,nationality,is_active',
        '1,Max,Verstappen,Dutch,1',
        '44,Lewis,Hamilton,British,1',
    ]);

    File::put($csvPath, $csvContent);

    try {
        $this->artisan('legacy:import-drivers', [
            'path' => $relativePath,
        ])
            ->expectsOutputToContain('Importing 2 driver row(s) from')
            ->expectsOutputToContain('Created: 2, Updated: 0, Skipped: 0, Errors: 0')
            ->expectsOutputToContain('Driver import completed successfully.')
            ->assertExitCode(0);
    } finally {
        if (File::exists($csvPath)) {
            File::delete($csvPath);
        }
    }
});

test('legacy:import-drivers supports dry run option', function () {
    $relativePath = 'drivers_legacy_dry_run.csv';
    $csvPath = base_path($relativePath);

    $csvContent = implode(PHP_EOL, [
        'driver_id,name,surname,nationality,is_active',
        '11,Sergio,Perez,Mexican,1',
    ]);

    File::put($csvPath, $csvContent);

    try {
        $this->artisan('legacy:import-drivers', [
            'path' => $relativePath,
            '--dry-run' => true,
        ])
            ->expectsOutputToContain('Importing 1 driver row(s) from')
            ->expectsOutputToContain('Created: 1, Updated: 0, Skipped: 0, Errors: 0')
            ->assertExitCode(0);
    } finally {
        if (File::exists($csvPath)) {
            File::delete($csvPath);
        }
    }
});

test('legacy:import-drivers fails gracefully when file is missing', function () {
    $this->artisan('legacy:import-drivers', [
        'path' => 'non_existent_drivers_file.csv',
    ])
        ->expectsOutputToContain('File not found or not readable:')
        ->assertExitCode(1);
});
