<?php

use App\Models\Drivers;
use App\Models\Teams;
use App\Models\User;
use Database\Seeders\FakerBasicSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)->group('slow');

test('faker basic seeder creates expected number of records', function () {
    // Run the seeder
    $seeder = new FakerBasicSeeder;
    $seeder->run();

    // Verify users were created
    expect(User::count())->toBe(5);

    // Verify teams were created
    expect(Teams::count())->toBe(5);

    // Verify drivers were created
    expect(Drivers::count())->toBe(10);
});

test('faker basic seeder creates valid user data', function () {
    $seeder = new FakerBasicSeeder;
    $seeder->run();

    $users = User::all();

    foreach ($users as $user) {
        expect($user->name)->not->toBeEmpty();
        expect($user->email)->not->toBeEmpty();
        expect($user->email)->toContain('@');
        expect($user->password)->not->toBeEmpty();
    }
});

test('faker basic seeder creates valid team data', function () {
    $seeder = new FakerBasicSeeder;
    $seeder->run();

    $teams = Teams::all();

    foreach ($teams as $team) {
        expect($team->team_name)->not->toBeEmpty();
        expect($team->nationality)->not->toBeEmpty();
        expect($team->team_id)->not->toBeEmpty();
    }
});

test('faker basic seeder creates valid driver data', function () {
    $seeder = new FakerBasicSeeder;
    $seeder->run();

    $drivers = Drivers::all();

    foreach ($drivers as $driver) {
        expect($driver->name)->not->toBeEmpty();
        expect($driver->surname)->not->toBeEmpty();
        expect($driver->nationality)->not->toBeEmpty();
        expect($driver->driver_id)->not->toBeEmpty();
    }
});

test('faker basic seeder can be run multiple times without errors', function () {
    $seeder = new FakerBasicSeeder;

    // Run twice
    $seeder->run();
    $seeder->run();

    // Should create additional records (factories don't enforce unique constraints)
    expect(User::count())->toBe(10); // 5 + 5
    expect(Teams::count())->toBe(10); // 5 + 5
    expect(Drivers::count())->toBe(20); // 10 + 10
});
