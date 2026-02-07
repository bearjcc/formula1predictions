<?php

declare(strict_types=1);

use App\Models\Prediction;
use App\Models\Races;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('score historical predictions command exits successfully with no races', function () {
    $this->artisan('app:score-historical-predictions')
        ->expectsOutput('No completed races found to score.')
        ->assertExitCode(0);
});

test('score historical predictions command dry run processes races', function () {
    $race = Races::factory()->create([
        'season' => 2024,
        'round' => 1,
        'status' => 'completed',
        'race_name' => 'Monaco GP',
        'results' => [
            ['driver' => ['driverId' => 'max_verstappen'], 'status' => 'finished'],
        ],
    ]);
    $user = User::factory()->create();
    Prediction::factory()->create([
        'user_id' => $user->id,
        'race_id' => $race->id,
        'type' => 'race',
        'status' => 'submitted',
        'prediction_data' => [
            'driver_order' => ['max_verstappen'],
            'fastest_lap' => 'max_verstappen',
        ],
    ]);

    $this->artisan('app:score-historical-predictions', ['--dry-run' => true])
        ->expectsOutputToContain('Found 1 races to process')
        ->assertExitCode(0);
});

test('send test notification command sends notification to first user', function () {
    $user = User::factory()->create();

    $this->artisan('notifications:test')
        ->expectsOutputToContain('Sending test notification')
        ->expectsOutputToContain('Test notification sent successfully!')
        ->assertExitCode(0);
});

test('send test notification command fails when no users exist', function () {
    $this->artisan('notifications:test')
        ->expectsOutputToContain('No users found')
        ->assertExitCode(1);
});

test('send test notification command fails for invalid user id', function () {
    User::factory()->create();

    $this->artisan('notifications:test', ['--user' => 99999])
        ->expectsOutputToContain('not found')
        ->assertExitCode(1);
});

test('send test notification command accepts type option', function () {
    User::factory()->create();

    $this->artisan('notifications:test', ['--type' => 'prediction'])
        ->expectsOutputToContain('prediction scored notification')
        ->assertExitCode(0);
});

test('send test notification command fails for invalid type', function () {
    User::factory()->create();

    $this->artisan('notifications:test', ['--type' => 'invalid'])
        ->expectsOutputToContain('Invalid notification type')
        ->assertExitCode(1);
});

test('sync race schedule command runs and updates races when API returns data', function () {
    $race = Races::factory()->create([
        'season' => 2025,
        'round' => 1,
        'qualifying_start' => null,
        'sprint_qualifying_start' => null,
    ]);

    $mock = \Mockery::mock(\App\Services\F1ApiService::class);
    $mock->shouldReceive('syncScheduleToRaces')->once()->with(2025)->andReturn(1);
    $this->app->instance(\App\Services\F1ApiService::class, $mock);

    $this->artisan('f1:sync-schedule', ['year' => 2025])
        ->expectsOutputToContain('Updated 1 race(s)')
        ->assertExitCode(0);
});
