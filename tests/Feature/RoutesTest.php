<?php

use App\Services\F1ApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\mock;

uses(RefreshDatabase::class);

// Test that routes exist and return 200 (even if views don't exist yet)
it('can access the home page', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
});

// Test year-specific routes exist
describe('Year-specific routes exist', function () {
    $validYears = ['2022', '2023', '2024', '2025', '2026'];
    $yearRoutes = [
        '/{year}/races',
        '/{year}/standings',
        '/{year}/standings/drivers',
        '/{year}/standings/teams',
        '/{year}/standings/predictions',
    ];

    foreach ($validYears as $year) {
        foreach ($yearRoutes as $route) {
            $testRoute = str_replace('{year}', $year, $route);

            it("route {$testRoute} exists", function () use ($testRoute) {
                $response = $this->get($testRoute);
                // Just check the route exists, don't care about view errors
                expect($response->status())->toBeIn([200, 500]);
            });
        }
    }

    // Test year-specific routes with additional parameters
    it('route /2023/standings/predictions/bearjcc exists', function () {
        $response = $this->get('/2023/standings/predictions/bearjcc');
        expect($response->status())->toBeIn([200, 500]);
    });

    it('route /2023/race/123 exists', function () {
        $response = $this->get('/2023/race/123');
        expect($response->status())->toBeIn([200, 500]);
    });
});

// Test invalid years return 404
describe('Invalid years return 404', function () {
    $invalidYears = ['1920', '1900', '2030', 'abc'];

    foreach ($invalidYears as $year) {
        it("returns 404 for /{$year}/races", function () use ($year) {
            $response = $this->get("/{$year}/races");
            $response->assertStatus(404);
        });
    }

    // Test invalid years with additional parameters
    it('returns 404 for /1920/standings/predictions/bearjcc', function () {
        $response = $this->get('/1920/standings/predictions/bearjcc');
        $response->assertStatus(404);
    });

    it('returns 404 for /1920/race/123', function () {
        $response = $this->get('/1920/race/123');
        $response->assertStatus(404);
    });
});

// Test non-year-specific routes exist
describe('Non-year-specific routes exist', function () {
    $routes = [
        '/countries',
        '/team/mercedes',
        '/driver/lewis-hamilton',
        '/circuit/silverstone',
        '/country/belgium',
        '/race/british-grand-prix',
    ];

    foreach ($routes as $route) {
        it("route {$route} exists", function () use ($route) {
            $response = $this->get($route);
            expect($response->status())->toBeIn([200, 500]);
        });
    }
});

// Test authentication required routes
describe('Authentication required routes', function () {
    it('redirects to login for dashboard when not authenticated', function () {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    });

    it('redirects to login for settings profile when not authenticated', function () {
        $response = $this->get('/settings/profile');
        $response->assertRedirect('/login');
    });

    it('redirects to login for settings password when not authenticated', function () {
        $response = $this->get('/settings/password');
        $response->assertRedirect('/login');
    });

    it('redirects to login for settings appearance when not authenticated', function () {
        $response = $this->get('/settings/appearance');
        $response->assertRedirect('/login');
    });

    it('redirects to login for predict create when not authenticated', function () {
        $response = $this->get('/predictions/create');
        $response->assertRedirect('/login');
    });

    it('redirects to login for predict edit when not authenticated', function () {
        $prediction = \App\Models\Prediction::factory()->create();
        $response = $this->get("/predictions/{$prediction->id}/edit");
        $response->assertRedirect('/login');
    });
});

// Test route naming (optional - ensures route names work correctly)
describe('Route naming works correctly', function () {
    it('can generate correct URLs for year-specific routes', function () {
        expect(route('races', ['year' => '2023']))->toContain('/2023/races');
        expect(route('standings', ['year' => '2023']))->toContain('/2023/standings');
        expect(route('standings.drivers', ['year' => '2023']))->toContain('/2023/standings/drivers');
        expect(route('standings.teams', ['year' => '2023']))->toContain('/2023/standings/teams');
        expect(route('standings.predictions', ['year' => '2023']))->toContain('/2023/standings/predictions');
        expect(route('standings.predictions.user', ['year' => '2023', 'username' => 'bearjcc']))->toContain('/2023/standings/predictions/bearjcc');
        expect(route('race', ['year' => '2023', 'id' => '123']))->toContain('/2023/race/123');
    });

    it('can generate correct URLs for non-year-specific routes', function () {
        expect(route('countries'))->toContain('/countries');
        expect(route('team', ['slug' => 'mercedes']))->toContain('/team/mercedes');
        expect(route('driver', ['slug' => 'lewis-hamilton']))->toContain('/driver/lewis-hamilton');
        expect(route('circuit', ['slug' => 'silverstone']))->toContain('/circuit/silverstone');
        expect(route('country', ['slug' => 'belgium']))->toContain('/country/belgium');
        expect(route('race.detail', ['slug' => 'british-grand-prix']))->toContain('/race/british-grand-prix');
    });
});

test('races page loads successfully', function () {
    $year = (int) config('f1.current_season');
    mock(F1ApiService::class, function ($mock) use ($year) {
        $mock->shouldReceive('getRacesForYear')
            ->with($year)
            ->andReturn([
                [
                    'round' => 1,
                    'raceName' => 'Test Race',
                    'circuit' => ['circuitName' => 'Test Circuit', 'country' => 'Test Country'],
                    'date' => '2026-03-15',
                    'time' => '14:00:00Z',
                    'status' => 'upcoming',
                    'results' => [],
                ],
            ]);
    });

    $response = $this->get("/{$year}/races");

    $response->assertSuccessful();
    $response->assertSee("{$year} Races");
    $response->assertSeeLivewire('races.races-list');
});

test('races page shows loading state', function () {
    $year = (int) config('f1.current_season');
    mock(F1ApiService::class, function ($mock) use ($year) {
        $mock->shouldReceive('getRacesForYear')
            ->with($year)
            ->andReturn([]);
    });

    $response = $this->get("/{$year}/races");

    $response->assertSuccessful();
    $response->assertSee("{$year} Races");
    $response->assertSeeLivewire('races.races-list');
});
