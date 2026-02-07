<?php

/**
 * Integration-level prediction form validation tests.
 * Tests validation via HTTP POST to /predictions (full stack, Form Requests).
 * For unit-level rule tests, see FormValidationTest.
 */

use App\Models\Drivers;
use App\Models\Teams;
use App\Models\User;

test('prediction form validation requires authentication', function () {
    $response = $this->post('/predictions', []);

    $response->assertRedirect('/login');
});

test('prediction form validation requires valid type', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post('/predictions', [
        'type' => 'invalid_type',
        'season' => 2024,
    ]);

    $response->assertSessionHasErrors(['type']);
});

test('sprint prediction requires valid configuration', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Missing race_round and driver_order for sprint prediction.
    $response = $this->post('/predictions', [
        'type' => 'sprint',
        'season' => 2024,
        'prediction_data' => [],
    ]);

    $response->assertSessionHasErrors(['race_round', 'prediction_data.driver_order']);
});

test('prediction form validation requires valid season', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post('/predictions', [
        'type' => 'race',
        'season' => 1800, // Too early
    ]);

    $response->assertSessionHasErrors(['season']);

    $response = $this->post('/predictions', [
        'type' => 'race',
        'season' => 2050, // Too late
    ]);

    $response->assertSessionHasErrors(['season']);
});

test('race prediction requires driver order', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post('/predictions', [
        'type' => 'race',
        'season' => 2024,
        'race_round' => 1,
        'prediction_data' => [
            // Missing driver_order
        ],
    ]);

    $response->assertSessionHasErrors(['prediction_data.driver_order']);
});

test('race prediction driver order must have between 1 and max drivers', function () {
    $user = User::factory()->create();
    $maxDrivers = config('f1.max_drivers', 22);
    $drivers = Drivers::factory()->count($maxDrivers + 5)->create();
    $this->actingAs($user);

    // Empty driver order fails (min 1)
    $response = $this->post('/predictions', [
        'type' => 'race',
        'season' => 2024,
        'race_round' => 1,
        'prediction_data' => [
            'driver_order' => [],
        ],
    ]);
    $response->assertSessionHasErrors(['prediction_data.driver_order']);

    // Too many drivers (max 20)
    $response = $this->post('/predictions', [
        'type' => 'race',
        'season' => 2024,
        'race_round' => 1,
        'prediction_data' => [
            'driver_order' => $drivers->pluck('id')->toArray(),
        ],
    ]);
    $response->assertSessionHasErrors(['prediction_data.driver_order']);

    // Partial prediction (e.g. 5 drivers) passes
    $response = $this->post('/predictions', [
        'type' => 'race',
        'season' => 2024,
        'race_round' => 1,
        'prediction_data' => [
            'driver_order' => $drivers->take(5)->pluck('id')->toArray(),
        ],
    ]);
    $response->assertSessionHasNoErrors();
});

test('race prediction driver order must contain valid driver IDs', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post('/predictions', [
        'type' => 'race',
        'season' => 2024,
        'race_round' => 1,
        'prediction_data' => [
            'driver_order' => [999, 998, 997, 996, 995, 994, 993, 992, 991, 990, 989, 988, 987, 986, 985, 984, 983, 982, 981, 980], // Invalid IDs
        ],
    ]);

    $response->assertSessionHasErrors(['prediction_data.driver_order.0']);
});

test('preseason prediction requires team order', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post('/predictions', [
        'type' => 'preseason',
        'season' => 2024,
        'prediction_data' => [
            // Missing team_order
        ],
    ]);

    $response->assertSessionHasErrors(['prediction_data.team_order']);
});

test('preseason prediction team order must have between 1 and max teams', function () {
    $user = User::factory()->create();
    $maxTeams = config('f1.max_teams', 11);
    $teams = Teams::factory()->count($maxTeams + 5)->create();
    $drivers = Drivers::factory()->count(config('f1.max_drivers', 22))->create();
    $this->actingAs($user);

    // Empty team order fails (min 1)
    $response = $this->post('/predictions', [
        'type' => 'preseason',
        'season' => 2024,
        'prediction_data' => [
            'team_order' => [],
            'driver_championship' => $drivers->pluck('id')->toArray(),
        ],
    ]);
    $response->assertSessionHasErrors(['prediction_data.team_order']);

    // Too many teams (max 10)
    $response = $this->post('/predictions', [
        'type' => 'preseason',
        'season' => 2024,
        'prediction_data' => [
            'team_order' => $teams->pluck('id')->toArray(),
            'driver_championship' => $drivers->pluck('id')->toArray(),
        ],
    ]);
    $response->assertSessionHasErrors(['prediction_data.team_order']);

    // Partial team order (e.g. 5 teams) passes
    $response = $this->post('/predictions', [
        'type' => 'preseason',
        'season' => 2024,
        'prediction_data' => [
            'team_order' => $teams->take(5)->pluck('id')->toArray(),
            'driver_championship' => $drivers->take(5)->pluck('id')->toArray(),
        ],
    ]);
    $response->assertSessionHasNoErrors();
});

test('preseason prediction requires driver championship order', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post('/predictions', [
        'type' => 'preseason',
        'season' => 2024,
        'prediction_data' => [
            'team_order' => Teams::factory()->count(10)->create()->pluck('id')->toArray(),
            // Missing driver_championship
        ],
    ]);

    $response->assertSessionHasErrors(['prediction_data.driver_championship']);
});

test('preseason prediction driver championship must have between 1 and max drivers', function () {
    $user = User::factory()->create();
    $maxDrivers = config('f1.max_drivers', 22);
    $drivers = Drivers::factory()->count($maxDrivers + 5)->create();
    $teams = Teams::factory()->count(config('f1.max_teams', 11))->create();
    $this->actingAs($user);

    // Empty driver championship fails (min 1)
    $response = $this->post('/predictions', [
        'type' => 'preseason',
        'season' => 2024,
        'prediction_data' => [
            'team_order' => $teams->pluck('id')->toArray(),
            'driver_championship' => [],
        ],
    ]);
    $response->assertSessionHasErrors(['prediction_data.driver_championship']);

    // Too many drivers (max 20)
    $response = $this->post('/predictions', [
        'type' => 'preseason',
        'season' => 2024,
        'prediction_data' => [
            'team_order' => $teams->pluck('id')->toArray(),
            'driver_championship' => $drivers->pluck('id')->toArray(),
        ],
    ]);
    $response->assertSessionHasErrors(['prediction_data.driver_championship']);

    // Partial driver championship (e.g. 10 drivers) passes
    $response = $this->post('/predictions', [
        'type' => 'preseason',
        'season' => 2024,
        'prediction_data' => [
            'team_order' => $teams->pluck('id')->toArray(),
            'driver_championship' => $drivers->take(10)->pluck('id')->toArray(),
        ],
    ]);
    $response->assertSessionHasNoErrors();
});

test('valid race prediction passes validation', function () {
    $user = User::factory()->create();
    $drivers = Drivers::factory()->count(20)->create();
    $this->actingAs($user);

    $response = $this->post('/predictions', [
        'type' => 'race',
        'season' => 2024,
        'race_round' => 1,
        'prediction_data' => [
            'driver_order' => $drivers->pluck('id')->toArray(),
            'fastest_lap' => $drivers->first()->id,
        ],
        'notes' => 'Test prediction notes',
    ]);

    $response->assertSessionHasNoErrors();
});

test('valid preseason prediction passes validation', function () {
    $user = User::factory()->create();
    $drivers = Drivers::factory()->count(20)->create();
    $teams = Teams::factory()->count(10)->create();
    $this->actingAs($user);

    $response = $this->post('/predictions', [
        'type' => 'preseason',
        'season' => 2024,
        'prediction_data' => [
            'team_order' => $teams->pluck('id')->toArray(),
            'driver_championship' => $drivers->pluck('id')->toArray(),
            'superlatives' => [
                'most_podiums_team' => $teams->first()->id,
                'most_podiums_driver' => $drivers->first()->id,
                'most_dnfs_team' => $teams->last()->id,
                'most_dnfs_driver' => $drivers->last()->id,
            ],
        ],
        'notes' => 'Test preseason prediction',
    ]);

    $response->assertSessionHasNoErrors();
});

test('notes field has maximum length validation', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post('/predictions', [
        'type' => 'race',
        'season' => 2024,
        'notes' => str_repeat('a', 1001), // Exceeds 1000 character limit
    ]);

    $response->assertSessionHasErrors(['notes']);
});

test('race round validation for race predictions', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Invalid race round (too low)
    $response = $this->post('/predictions', [
        'type' => 'race',
        'season' => 2024,
        'race_round' => 0,
    ]);

    $response->assertSessionHasErrors(['race_round']);

    // Invalid race round (too high)
    $response = $this->post('/predictions', [
        'type' => 'race',
        'season' => 2024,
        'race_round' => 26,
    ]);

    $response->assertSessionHasErrors(['race_round']);

    // Missing race round for race prediction
    $response = $this->post('/predictions', [
        'type' => 'race',
        'season' => 2024,
        'prediction_data' => [
            'driver_order' => Drivers::factory()->count(20)->create()->pluck('id')->toArray(),
        ],
    ]);

    $response->assertSessionHasErrors(['race_round']);

    // Race round should be prohibited for preseason predictions
    $response = $this->post('/predictions', [
        'type' => 'preseason',
        'season' => 2024,
        'race_round' => 1,
        'prediction_data' => [
            'team_order' => Teams::factory()->count(10)->create()->pluck('id')->toArray(),
            'driver_championship' => Drivers::factory()->count(20)->create()->pluck('id')->toArray(),
        ],
    ]);

    $response->assertSessionHasErrors(['race_round']);
});
