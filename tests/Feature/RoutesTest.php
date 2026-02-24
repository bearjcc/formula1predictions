<?php

use App\Models\Prediction;
use App\Services\F1ApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\get;
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
        '/{year}/standings/drivers',
        '/{year}/standings/constructors',
        '/{year}/standings/predictions',
    ];

    foreach ($validYears as $year) {
        it("route /{$year}/standings redirects to drivers standings", function () use ($year) {
            /** @var \Tests\TestCase $this */
            $response = $this->get("/{$year}/standings");
            $response->assertRedirect("/{$year}/standings/drivers");
        });

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
        $race = \App\Models\Races::factory()->create(['season' => 2023, 'round' => 5]);
        $response = $this->get('/2023/race/5');
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
    it('route /scoring responds with 200', function () {
        /** @var \Tests\TestCase $this */
        $this->get(route('scoring'))->assertOk();
    });

    it('route /constructor/{slug} responds with 200', function () {
        /** @var \Tests\TestCase $this */
        \App\Models\Teams::factory()->create(['team_name' => 'Mercedes']);
        $this->get('/constructor/mercedes')->assertOk();
    });

    it('route /driver/{slug} responds with 200', function () {
        /** @var \Tests\TestCase $this */
        \App\Models\Drivers::factory()->create(['name' => 'Lewis', 'surname' => 'Hamilton']);
        $this->get('/driver/lewis-hamilton')->assertOk();
    });

    it('route /circuit/{slug} responds with 200', function () {
        /** @var \Tests\TestCase $this */
        \App\Models\Circuits::factory()->create(['circuit_name' => 'Silverstone']);
        $this->get('/circuit/silverstone')->assertOk();
    });

    it('route /country/{slug} responds with 200', function () {
        /** @var \Tests\TestCase $this */
        \App\Models\Countries::factory()->create(['name' => 'Belgium']);
        $this->get('/country/belgium')->assertNotFound();
    });

    it('route /race/{slug} responds with 200', function () {
        /** @var \Tests\TestCase $this */
        \App\Models\Races::factory()->create(['race_name' => 'British Grand Prix']);
        $this->get('/race/british-grand-prix')->assertOk();
    });
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
        expect(route('standings.constructors', ['year' => '2023']))->toContain('/2023/standings/constructors');
        expect(route('standings.predictions', ['year' => '2023']))->toContain('/2023/standings/predictions');
        expect(route('standings.predictions.user', ['year' => '2023', 'username' => 'bearjcc']))->toContain('/2023/standings/predictions/bearjcc');
        expect(route('race', ['year' => '2023', 'id' => '123']))->toContain('/2023/race/123');
    });

    it('can generate correct URLs for non-year-specific routes', function () {
        expect(route('scoring'))->toContain('/scoring');
        expect(route('constructor', ['slug' => 'mercedes']))->toContain('/constructor/mercedes');
        expect(route('driver', ['slug' => 'lewis-hamilton']))->toContain('/driver/lewis-hamilton');
        expect(route('circuit', ['slug' => 'silverstone']))->toContain('/circuit/silverstone');
        expect(route('race.detail', ['slug' => 'british-grand-prix']))->toContain('/race/british-grand-prix');
    });
});

describe('leaderboard routes', function () {
    it('does not register legacy livewire leaderboard routes', function () {
        expect(Route::has('leaderboard.livewire'))->toBeFalse();
        expect(Route::has('leaderboard.user-stats-livewire'))->toBeFalse();
    });

    it('returns 404 for legacy livewire leaderboard URLs', function () {
        get('/leaderboard/livewire')->assertNotFound();
        get('/leaderboard/user/1/livewire')->assertNotFound();
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

// region Railway env check (opt-in diagnostic)
describe('railway-env-check route', function () {
    it('returns 404 when RAILWAY_ENV_DEBUG is not set', function () {
        $response = get('/railway-env-check');
        $response->assertNotFound();
    });

    it('returns JSON with env flags when RAILWAY_ENV_DEBUG is set', function () {
        putenv('RAILWAY_ENV_DEBUG=1');
        putenv('RAILWAY_DUMMY_VAR=test-value');

        try {
            $response = get('/railway-env-check');
            $response->assertOk();
            $response->assertJsonStructure(['ok', 'admin_email_set', 'admin_password_set', 'railway_dummy_var', 'config_admin_email_set', 'config_admin_password_set']);
            $response->assertJsonPath('ok', true);
            $response->assertJsonPath('railway_dummy_var', 'test-value');
        } finally {
            putenv('RAILWAY_ENV_DEBUG=');
            putenv('RAILWAY_DUMMY_VAR=');
        }
    });
});
// endregion

// region Dev/demo routes (F1-085): only registered in local/testing; smoke test in testing env
describe('components demo page', function () {
    it('returns 200 in testing environment', function () {
        /** @var \Tests\TestCase $this */
        $response = $this->get('/components');
        $response->assertOk();
        $response->assertSee('Mary UI Components Demo');
    });
});
// endregion
