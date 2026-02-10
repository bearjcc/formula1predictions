<?php

use App\Models\Prediction;
use App\Services\F1ApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\mock;

uses(RefreshDatabase::class);

it('can access the home page with 200', function () {
    /** @var \Tests\TestCase $this */
    $response = $this->get('/');
    $response->assertOk();
});

describe('year-specific routes return 200 for valid years', function () {
    beforeEach(function () {
        mock(F1ApiService::class, function ($mock) {
            $mock->shouldReceive('getRacesForYear')->zeroOrMoreTimes()->andReturn([]);
        });
    });

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

            it("route {$testRoute} responds with 200", function () use ($testRoute) {
                /** @var \Tests\TestCase $this */
                $response = $this->get($testRoute);
                $response->assertOk();
            });
        }
    }

    it('prediction standings user route responds with 200', function () {
        /** @var \Tests\TestCase $this */
        $response = $this->get('/2023/standings/predictions/bearjcc');
        $response->assertOk();
    });

    it('race detail route responds with 200', function () {
        /** @var \Tests\TestCase $this */
        $response = $this->get('/2023/race/123');
        $response->assertOk();
    });
});

describe('invalid years return 404', function () {
    $invalidYears = ['1920', '1900', '2030', 'abc'];

    foreach ($invalidYears as $year) {
        it("returns 404 for /{$year}/races", function () use ($year) {
            /** @var \Tests\TestCase $this */
            $response = $this->get("/{$year}/races");
            $response->assertNotFound();
        });
    }

    it('returns 404 for /1920/standings/predictions/bearjcc', function () {
        /** @var \Tests\TestCase $this */
        $response = $this->get('/1920/standings/predictions/bearjcc');
        $response->assertNotFound();
    });

    it('returns 404 for /1920/race/123', function () {
        /** @var \Tests\TestCase $this */
        $response = $this->get('/1920/race/123');
        $response->assertNotFound();
    });
});

describe('non-year-specific routes return 200', function () {
    $routes = [
        '/countries',
        '/team/mercedes',
        '/driver/lewis-hamilton',
        '/circuit/silverstone',
        '/country/belgium',
        '/race/british-grand-prix',
    ];

    foreach ($routes as $route) {
        it("route {$route} responds with 200", function () use ($route) {
            /** @var \Tests\TestCase $this */
            $response = $this->get($route);
            $response->assertOk();
        });
    }
});

describe('authentication required routes', function () {
    it('redirects to login when not authenticated', function () {
        /** @var \Tests\TestCase $this */
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/settings/profile')->assertRedirect('/login');
        $this->get('/settings/password')->assertRedirect('/login');
        $this->get('/settings/appearance')->assertRedirect('/login');
        $this->get('/predictions/create')->assertRedirect('/login');

        $prediction = Prediction::factory()->create();
        $this->get("/predictions/{$prediction->id}/edit")->assertRedirect('/login');
    });
});

describe('route naming works correctly', function () {
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

test('current season races page loads successfully with mocked data', function () {
    /** @var \Tests\TestCase $this */
    // Keep this in sync with config('f1.current_season')
    $year = 2026;

    mock(F1ApiService::class, function ($mock) use ($year) {
        $mock->shouldReceive('getRacesForYear')
            ->with($year)
            ->andReturn([
                [
                    'round' => 1,
                    'raceName' => 'Test Race',
                    'circuit' => ['circuitName' => 'Test Circuit', 'country' => 'Test Country'],
                    'date' => "{$year}-03-15",
                    'time' => '14:00:00Z',
                    'status' => 'upcoming',
                    'results' => [],
                ],
            ]);
    });

    $response = $this->get("/{$year}/races");

    $response->assertOk();
    $response->assertSee("{$year} Races");
    $response->assertSeeLivewire('races.races-list');
});

test('current season races page shows loading state with no races', function () {
    /** @var \Tests\TestCase $this */
    // Keep this in sync with config('f1.current_season')
    $year = 2026;

    mock(F1ApiService::class, function ($mock) use ($year) {
        $mock->shouldReceive('getRacesForYear')
            ->with($year)
            ->andReturn([]);
    });

    $response = $this->get("/{$year}/races");

    $response->assertOk();
    $response->assertSee("{$year} Races");
    $response->assertSeeLivewire('races.races-list');
});
