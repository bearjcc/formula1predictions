<?php

use App\Models\Circuits;
use App\Models\Drivers;
use App\Models\Races;
use App\Models\Teams;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// --- Team Detail Page ---

test('team detail page displays real team data', function () {
    $team = Teams::factory()->create([
        'team_name' => 'Red Bull Racing',
        'nationality' => 'Austrian',
        'base_location' => 'Milton Keynes',
        'founded' => 2005,
        'world_championships' => 6,
        'race_wins' => 118,
        'podiums' => 285,
        'pole_positions' => 95,
        'team_principal' => 'Christian Horner',
    ]);

    $response = $this->get('/constructor/red-bull-racing');

    $response->assertOk();
    $response->assertSee('Red Bull Racing');
    $response->assertSee('Milton Keynes');
    $response->assertSee('Austrian');
    $response->assertSee('2005');
    $response->assertSee('Christian Horner');
    $response->assertSee('118');
});

test('team detail page shows associated drivers', function () {
    $team = Teams::factory()->create(['team_name' => 'Ferrari']);
    Drivers::factory()->create([
        'name' => 'Charles',
        'surname' => 'Leclerc',
        'team_id' => $team->id,
        'driver_number' => 16,
    ]);

    $response = $this->get('/constructor/ferrari');

    $response->assertOk();
    $response->assertSee('Charles Leclerc');
    $response->assertSee('Driver #16');
});

test('team detail page returns 404 for non-existent slug', function () {
    $response = $this->get('/constructor/non-existent-team');
    $response->assertNotFound();
});

// --- Driver Detail Page ---

test('driver detail page displays real driver data', function () {
    $team = Teams::factory()->create(['team_name' => 'Red Bull Racing']);
    Drivers::factory()->create([
        'name' => 'Max',
        'surname' => 'Verstappen',
        'nationality' => 'Dutch',
        'driver_number' => 1,
        'date_of_birth' => '1997-09-30',
        'world_championships' => 4,
        'race_wins' => 63,
        'podiums' => 111,
        'pole_positions' => 40,
        'team_id' => $team->id,
    ]);

    $response = $this->get('/driver/max-verstappen');

    $response->assertOk();
    $response->assertSee('Max Verstappen');
    $response->assertSee('Dutch');
    $response->assertSee('Driver #1');
    $response->assertSee('Red Bull Racing');
    $response->assertSee('September 30, 1997');
    $response->assertSee('63');
});

test('driver detail page returns 404 for non-existent slug', function () {
    $response = $this->get('/driver/non-existent-driver');
    $response->assertNotFound();
});

// --- Circuit Detail Page ---

test('circuit detail page displays real circuit data', function () {
    Circuits::factory()->create([
        'circuit_name' => 'Silverstone Circuit',
        'country' => 'United Kingdom',
        'locality' => 'Silverstone',
        'circuit_length' => 5.891,
        'laps' => 52,
        'first_grand_prix' => '1950',
        'lap_record_driver' => 'Max Verstappen',
        'lap_record_time' => '1:27.097',
        'lap_record_year' => 2024,
    ]);

    $response = $this->get('/circuit/silverstone-circuit');

    $response->assertOk();
    $response->assertSee('Silverstone Circuit');
    $response->assertSee('United Kingdom');
    $response->assertSee('5.891');
    $response->assertSee('52');
    $response->assertSee('1950');
    $response->assertSee('Max Verstappen');
    $response->assertSee('1:27.097');
});

test('circuit detail page returns 404 for non-existent slug', function () {
    $response = $this->get('/circuit/non-existent-circuit');
    $response->assertNotFound();
});

// --- Race Detail Page (slug route) ---

test('race detail page via slug displays real race data', function () {
    Races::factory()->create([
        'race_name' => 'British Grand Prix',
        'season' => 2025,
        'round' => 12,
        'circuit_name' => 'Silverstone Circuit',
        'country' => 'United Kingdom',
        'date' => '2025-07-06',
        'laps' => 52,
        'status' => 'upcoming',
    ]);

    $response = $this->get('/race/british-grand-prix');

    $response->assertOk();
    $response->assertSee('British Grand Prix');
    $response->assertSee('Silverstone Circuit');
    $response->assertSee('United Kingdom');
    $response->assertSee('52');
    $response->assertSee('2025');
});

test('race detail page via slug returns 404 for non-existent slug', function () {
    $response = $this->get('/race/non-existent-race');
    $response->assertNotFound();
});

// --- Race Detail Page (year/round route) ---

test('race detail page via year and round displays real race data', function () {
    Races::factory()->create([
        'race_name' => 'Australian Grand Prix',
        'season' => 2025,
        'round' => 1,
        'circuit_name' => 'Albert Park',
        'country' => 'Australia',
        'date' => '2025-03-16',
        'laps' => 58,
        'status' => 'completed',
        'results' => json_encode([
            ['position' => 1, 'driver' => 'Oscar Piastri', 'team' => 'McLaren', 'points' => 25],
            ['position' => 2, 'driver' => 'Lando Norris', 'team' => 'McLaren', 'points' => 18],
            ['position' => 3, 'driver' => 'Max Verstappen', 'team' => 'Red Bull', 'points' => 15],
        ]),
    ]);

    $response = $this->get('/2025/race/1');

    $response->assertOk();
    $response->assertSee('Australian Grand Prix');
    $response->assertSee('Albert Park');
    $response->assertSee('Australia');
    $response->assertSee('Oscar Piastri');
    $response->assertSee('McLaren');
});

test('race detail page via year and round returns 404 for non-existent race', function () {
    $response = $this->get('/2025/race/99');
    $response->assertNotFound();
});

// --- Detail pages do NOT show hardcoded Red Bull / Verstappen / Silverstone data ---

test('team detail page does not show hardcoded Red Bull data for other teams', function () {
    Teams::factory()->create([
        'team_name' => 'McLaren',
        'team_principal' => 'Andrea Stella',
        'nationality' => 'British',
    ]);

    $response = $this->get('/constructor/mclaren');

    $response->assertOk();
    $response->assertSee('McLaren');
    $response->assertSee('Andrea Stella');
    $response->assertDontSee('Christian Horner');
    $response->assertDontSee('Max Verstappen');
});

test('driver detail page does not show hardcoded Verstappen data for other drivers', function () {
    Drivers::factory()->create([
        'name' => 'Lewis',
        'surname' => 'Hamilton',
        'nationality' => 'British',
        'driver_number' => 44,
        'date_of_birth' => '1985-01-07',
    ]);

    $response = $this->get('/driver/lewis-hamilton');

    $response->assertOk();
    $response->assertSee('Lewis Hamilton');
    $response->assertSee('British');
    $response->assertDontSee('Dutch');
    $response->assertDontSee('September 30, 1997');
});

test('circuit detail page does not show hardcoded Silverstone data for other circuits', function () {
    Circuits::factory()->create([
        'circuit_name' => 'Monza',
        'country' => 'Italy',
        'locality' => 'Monza',
        'circuit_length' => 5.793,
    ]);

    $response = $this->get('/circuit/monza');

    $response->assertOk();
    $response->assertSee('Monza');
    $response->assertSee('Italy');
    $response->assertDontSee('United Kingdom');
});
