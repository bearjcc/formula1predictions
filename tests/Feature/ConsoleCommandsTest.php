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
    Races::factory()->create([
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

test('sync season data command runs and syncs races teams drivers', function () {
    $mock = \Mockery::mock(\App\Services\F1ApiService::class);
    $mock->shouldReceive('syncSeasonRacesFromSchedule')->once()->with(2026)->andReturn(['created' => 24, 'updated' => 0]);
    $mock->shouldReceive('syncTeamsForSeason')->once()->with(2026)->andReturn(10);
    $mock->shouldReceive('syncDriversForSeason')->once()->with(2026)->andReturn(22);
    $this->app->instance(\App\Services\F1ApiService::class, $mock);

    $this->artisan('f1:sync-season', ['year' => 2026])
        ->expectsOutputToContain('Races: 24 created, 0 updated.')
        ->expectsOutputToContain('Teams: 10 synced.')
        ->expectsOutputToContain('Drivers: 22 synced.')
        ->expectsOutputToContain('Done.')
        ->assertExitCode(0);
});

test('sync season data command respects races-only option', function () {
    $mock = \Mockery::mock(\App\Services\F1ApiService::class);
    $mock->shouldReceive('syncSeasonRacesFromSchedule')->once()->with(2025)->andReturn(['created' => 0, 'updated' => 24]);
    $mock->shouldReceive('syncTeamsForSeason')->never();
    $mock->shouldReceive('syncDriversForSeason')->never();
    $this->app->instance(\App\Services\F1ApiService::class, $mock);

    $this->artisan('f1:sync-season', ['year' => 2025, '--races-only' => true])
        ->expectsOutputToContain('Races: 0 created, 24 updated.')
        ->assertExitCode(0);
});

test('promote admin command requires email when ADMIN_EMAIL not set', function () {
    config(['admin.promotable_admin_email' => null]);

    $this->artisan('app:promote-admin')
        ->expectsOutputToContain('Provide an email')
        ->assertExitCode(1);
});

test('promote admin command fails for unknown email', function () {
    $this->artisan('app:promote-admin', ['email' => 'nobody@example.com'])
        ->expectsOutputToContain('No user found')
        ->assertExitCode(1);
});

test('promote admin command promotes user by email', function () {
    $user = User::factory()->create(['email' => 'deploy-admin@example.com', 'is_admin' => false]);

    $this->artisan('app:promote-admin', ['email' => 'deploy-admin@example.com'])
        ->expectsOutputToContain('Promoted deploy-admin@example.com to admin.')
        ->assertExitCode(0);

    $user->refresh();
    expect($user->is_admin)->toBeTrue();
});

test('promote admin command is idempotent when user already admin', function () {
    $user = User::factory()->create(['email' => 'already-admin@example.com', 'is_admin' => true]);

    $this->artisan('app:promote-admin', ['email' => 'already-admin@example.com'])
        ->expectsOutputToContain('already an admin')
        ->assertExitCode(0);

    $user->refresh();
    expect($user->is_admin)->toBeTrue();
});
