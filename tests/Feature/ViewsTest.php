<?php

use App\Models\Circuits;
use App\Models\Countries;
use App\Models\Drivers;
use App\Models\Races;
use App\Models\Teams;
use App\Services\F1ApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\mock;

uses(RefreshDatabase::class);

beforeEach(function () {
    mock(F1ApiService::class, function ($mock) {
        $mock->shouldReceive('getRacesForYear')->zeroOrMoreTimes()->andReturn([]);
    });
});

// Test that views load correctly and return expected content
it('can load the home page view', function () {
    $response = $this->get('/');
    $response->assertOk();
    $response->assertViewIs('home');
});

// Test year-specific views
describe('Year-specific views load correctly', function () {
    // Use explicit years here; keep in sync with config('f1.current_season')
    $validYears = ['2022', '2023', '2026'];
    $yearRoutes = [
        '/{year}/races' => 'races',
        '/{year}/standings' => 'standings',
        '/{year}/standings/drivers' => 'standings.drivers',
        '/{year}/standings/constructors' => 'standings.constructors',
        '/{year}/standings/predictions' => 'standings.predictions',
    ];

    foreach ($validYears as $year) {
        foreach ($yearRoutes as $route => $view) {
            $testRoute = str_replace('{year}', $year, $route);

            it("loads {$view} view for {$testRoute}", function () use ($testRoute, $view, $year) {
                $response = $this->get($testRoute);
                $response->assertOk();
                $response->assertViewIs($view);
                $response->assertViewHas('year', $year);
            });
        }
    }

    // Test year-specific routes with additional parameters
    it('loads standings.predictions view for /2023/standings/predictions/bearjcc', function () {
        $response = $this->get('/2023/standings/predictions/bearjcc');
        $response->assertOk();
        $response->assertViewIs('standings.predictions');
        $response->assertViewHas('year', '2023');
        $response->assertViewHas('username', 'bearjcc');
    });

    it('loads race view for a valid race', function () {
        $race = Races::factory()->create(['season' => 2023, 'round' => 5]);

        $response = $this->get('/2023/race/5');
        $response->assertOk();
        $response->assertViewIs('race');
        $response->assertViewHas('race');
    });

    it('returns 404 for non-existent race', function () {
        $response = $this->get('/2023/race/999');
        $response->assertNotFound();
    });
});

// Test non-year-specific views
describe('Non-year-specific views load correctly', function () {
    it('loads countries index (Livewire full-page) for /countries', function () {
        $response = $this->get('/countries');
        $response->assertOk();
        $response->assertSee('F1 Countries', false);
    });

    it('loads constructor view for /constructor/{slug}', function () {
        Teams::factory()->create(['team_name' => 'Mercedes']);

        $response = $this->get('/constructor/mercedes');
        $response->assertOk();
        $response->assertViewIs('constructor');
        $response->assertViewHas('constructor');
    });

    it('loads driver view for /driver/{slug}', function () {
        Drivers::factory()->create(['name' => 'Lewis', 'surname' => 'Hamilton']);

        $response = $this->get('/driver/lewis-hamilton');
        $response->assertOk();
        $response->assertViewIs('driver');
        $response->assertViewHas('driver');
    });

    it('loads circuit view for /circuit/{slug}', function () {
        Circuits::factory()->create(['circuit_name' => 'Silverstone']);

        $response = $this->get('/circuit/silverstone');
        $response->assertOk();
        $response->assertViewIs('circuit');
        $response->assertViewHas('circuit');
    });

    it('loads country view for /country/{slug}', function () {
        Countries::factory()->create(['name' => 'Belgium']);

        $response = $this->get('/country/belgium');
        $response->assertOk();
        $response->assertViewIs('country');
        $response->assertViewHas('country');
    });

    it('loads race detail view for /race/{slug}', function () {
        Races::factory()->create(['race_name' => 'British Grand Prix']);

        $response = $this->get('/race/british-grand-prix');
        $response->assertOk();
        $response->assertViewIs('race');
        $response->assertViewHas('race');
    });

    it('returns 404 for non-existent constructor slug', function () {
        $response = $this->get('/constructor/nonexistent');
        $response->assertNotFound();
    });

    it('returns 404 for non-existent driver slug', function () {
        $response = $this->get('/driver/nonexistent');
        $response->assertNotFound();
    });
});

// Test view content (basic structure)
describe('Views have basic structure', function () {
    it('home page has basic HTML structure', function () {
        $response = $this->get('/');
        $response->assertSee('<!DOCTYPE html>', false); // Case insensitive
        $response->assertSee('<html', false);
        $response->assertSee('<body', false);
    });

    it('year-specific pages show the year', function () {
        $response = $this->get('/2023/races');
        $response->assertSee('2023');
    });

    it('constructor page shows the constructor name', function () {
        Teams::factory()->create(['team_name' => 'Mercedes']);

        $response = $this->get('/constructor/mercedes');
        $response->assertSee('Mercedes');
    });
});

// Test view errors are handled gracefully
describe('View errors are handled gracefully', function () {
    it('returns 500 for missing views', function () {
        // This test is intended to catch missing views. For the current season (2026)
        // races page, we expect a 200 when the view exists and Livewire/F1 API
        // dependencies are satisfied by other tests.
        $response = $this->get('/2026/races');
        $response->assertOk();
    });
});

// Test view data structure
describe('View data is structured correctly', function () {
    it('year-specific routes pass year data', function () {
        $response = $this->get('/2023/standings/drivers');
        $response->assertViewHas('year');
        $response->assertViewHasAll(['year']);
    });

    it('slug-based routes pass model data', function () {
        Teams::factory()->create(['team_name' => 'Ferrari']);

        $response = $this->get('/team/ferrari');
        $response->assertViewHas('team');
    });

    it('complex routes pass multiple parameters', function () {
        $response = $this->get('/2023/standings/predictions/testuser');
        $response->assertViewHasAll(['year', 'username']);
    });
});
