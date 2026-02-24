<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\RacesController;
use App\Models\Circuits;
use App\Models\Countries;
use App\Models\Drivers;
use App\Models\Races;
use App\Models\Standings;
use App\Models\Teams;
use App\Services\F1ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/how-it-works', function () {
    return view('how-it-works');
})->name('how-it-works');

Route::get('/scoring', function () {
    return view('scoring');
})->name('scoring');


// Dev and testing demo routes
if (app()->environment(['local', 'testing'])) {
    Route::get('/components', function () {
        return view('components');
    })->name('components');

    Route::get('/draggable-demo', function () {
        return view('draggable-demo');
    })->name('draggable-demo');
}

Route::get('dashboard', App\Http\Controllers\DashboardController::class)
    ->middleware(['auth'])
    ->name('dashboard');

Route::get('/analytics', App\Livewire\Pages\Analytics::class)->middleware(['auth'])->name('analytics');

// Leaderboard routes (public)
Route::prefix('leaderboard')->name('leaderboard.')->group(function () {
    Route::get('/', [LeaderboardController::class, 'index'])->name('index');
    Route::get('/compare', [LeaderboardController::class, 'compare'])->name('compare');
    Route::get('/season/{season}', [LeaderboardController::class, 'season'])->name('season');
    Route::get('/race/{season}/{raceRound}', [LeaderboardController::class, 'race'])->name('race');
    Route::get('/user/{user}', [LeaderboardController::class, 'userStats'])->name('user-stats');
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    // Notifications route
    Volt::route('notifications', 'pages.notifications.index')->name('notifications.index');

    // Feedback (authenticated only; no public listing)
    Volt::route('feedback', 'pages.feedback')->name('feedback');

    Route::get('predict/create', function (Request $request) {
        $race = null;

        if ($request->filled('race_id')) {
            $raceId = (int) $request->input('race_id');
            $race = Races::find($raceId);
        } else {
            $nextRace = Races::nextAvailableForPredictions();
            if ($nextRace !== null) {
                return to_route('predict.create', ['race_id' => $nextRace->id]);
            }
        }

        // If the user already has a prediction for this race, send them to edit it instead
        if ($race !== null) {
            $existing = $request->user()->predictions()
                ->where('season', $race->season)
                ->where('race_round', $race->round)
                ->whereIn('type', ['race', 'sprint'])
                ->first();
            if ($existing !== null) {
                return to_route('predictions.edit', $existing)
                    ->with('info', 'You already have a prediction for this race.');
            }
        }

        return view('predictions.create-livewire', compact('race'));
    })->name('predict.create');

    Route::get('predict/preseason', function (Request $request) {
        $year = (int) $request->input('year', config('f1.current_season'));

        return view('predictions.create-livewire', [
            'race' => null,
            'preseason' => true,
            'year' => $year,
        ]);
    })->name('predict.preseason');

    Route::get('predictions/{prediction}/edit', function ($prediction) {
        return view('predictions.edit-livewire', compact('prediction'));
    })->name('predictions.edit');

    // Prediction routes
    Route::resource('predictions', PredictionController::class)->middleware('throttle:predictions');

    // Admin routes (admin middleware restricts to admin role)
    Route::prefix('admin')->name('admin.')->middleware(['admin', 'throttle:admin'])->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/predictions', [AdminController::class, 'predictions'])->name('predictions');
        Route::get('/races', [AdminController::class, 'races'])->name('races');
        Route::get('/scoring', [AdminController::class, 'scoring'])->name('scoring');
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');

        // Prediction management actions
        Route::post('/predictions/{prediction}/score', [AdminController::class, 'scorePrediction'])->name('predictions.score');
        Route::post('/predictions/{prediction}/lock', [AdminController::class, 'lockPrediction'])->name('predictions.lock');
        Route::post('/predictions/{prediction}/unlock', [AdminController::class, 'unlockPrediction'])->name('predictions.unlock');
        Route::delete('/predictions/{prediction}', [AdminController::class, 'deletePrediction'])->name('predictions.delete');

        // Scoring management
        Route::post('/races/{race}/score', [AdminController::class, 'scoreRace'])->name('races.score');
        Route::post('/races/{race}/queue-scoring', [AdminController::class, 'queueRaceScoring'])->name('races.queue-scoring');
        Route::post('/races/{race}/substitutions', [AdminController::class, 'handleDriverSubstitutions'])->name('races.substitutions');
        Route::post('/races/{race}/cancel', [AdminController::class, 'handleRaceCancellation'])->name('races.cancel');
        Route::post('/races/{race}/toggle-half-points', [AdminController::class, 'toggleHalfPoints'])->name('races.toggle-half-points');
        Route::get('/races/{race}/scoring-stats', [AdminController::class, 'getRaceScoringStats'])->name('races.scoring-stats');
        Route::post('/bulk-score', [AdminController::class, 'bulkScoreRaces'])->name('bulk-score');

        // Score overrides
        Route::post('/predictions/{prediction}/override-score', [AdminController::class, 'overridePredictionScore'])->name('predictions.override-score');
    });

    // Monetization (Stripe/Season Supporter) — deferred to later release; re-enable with F1-031
    // Route::prefix('checkout')->name('checkout.')->middleware('throttle:checkout')->group(function () {
    //     Route::post('/season-supporter', [\App\Http\Controllers\StripeCheckoutController::class, 'createCheckoutSession'])->name('season-supporter');
    //     Route::get('/success', [\App\Http\Controllers\StripeCheckoutController::class, 'success'])->name('success');
    //     Route::get('/cancel', [\App\Http\Controllers\StripeCheckoutController::class, 'cancel'])->name('cancel');
    //     Route::get('/portal', [\App\Http\Controllers\StripeCheckoutController::class, 'portal'])->name('portal');
    // });

});

// Stripe webhook — deferred to later release (F1-031)
// Route::post('/stripe/webhook', [\App\Http\Controllers\StripeWebhookController::class, 'handleWebhook'])
//     ->name('stripe.webhook');

// F1 API Routes
Route::prefix('api/f1')->middleware(['auth', 'throttle:api'])->group(function () {
    Route::get('/races/{year}', [RacesController::class, 'index'])->name('f1.races');
    Route::get('/races/{year}/{round}', [RacesController::class, 'show'])->name('f1.race');
    Route::delete('/cache/{year}', [RacesController::class, 'clearCache'])->name('f1.clear-cache')->middleware('admin');
});

// F1 API test route (local and testing environments only)
if (app()->environment(['local', 'testing'])) {
    Route::get('/api/f1/test', [RacesController::class, 'testApi'])->name('f1.test');
}

Route::get('/railway-env-check', function () {
    if (! env('RAILWAY_ENV_DEBUG')) {
        abort(404);
    }

    $adminEmail = config('admin.email');
    $adminPassword = config('admin.password');

    return response()->json([
        'ok' => true,
        'admin_email_set' => filled(env('ADMIN_EMAIL')),
        'admin_password_set' => filled(env('ADMIN_PASSWORD')),
        'railway_dummy_var' => env('RAILWAY_DUMMY_VAR'),
        'config_admin_email_set' => filled($adminEmail),
        'config_admin_password_set' => filled($adminPassword),
    ]);
});

// Year specific routes
Route::middleware(['validate.year'])->group(function () {
    Route::get('/{year}/races', function ($year) {
        return view('races', ['year' => $year]);
    })->name('races');

    Route::get('/{year}/standings', function ($year) {
        return to_route('standings.drivers', ['year' => $year], 302);
    })->name('standings');

    Route::get('/{year}/standings/drivers', function (F1ApiService $f1, $year) {
        $season = (int) $year;
        $driverStandings = Standings::getDriverStandings($season, null);
        $standingsByEntityId = $driverStandings->keyBy('entity_id');

        // Only show drivers who are in this season: from standings (entity_id) or from API championship list
        $entityIds = $driverStandings->pluck('entity_id')->unique()->filter()->values();
        $allDrivers = collect();
        if ($entityIds->isNotEmpty()) {
            $byDriverId = Drivers::whereIn('driver_id', $entityIds)->with('team')->get();
            $numericIds = $entityIds->filter(fn ($id) => ctype_digit((string) $id))->values();
            $byId = $numericIds->isNotEmpty()
                ? Drivers::whereIn('id', $numericIds->map(fn ($id) => (int) $id))->with('team')->get()
                : collect();
            $allDrivers = $byDriverId->merge($byId)->unique('id')->values();
        }
        $driverIdToTeamName = [];
        $apiDriverStats = [];
        try {
            $data = $f1->fetchDriversChampionship($season);
            $entries = $data['drivers_championship'] ?? [];
            if ($allDrivers->isEmpty() && $entries !== []) {
                $apiDriverIds = collect($entries)->pluck('driverId')->filter()->unique()->values()->all();
                $allDrivers = Drivers::whereIn('driver_id', $apiDriverIds)->with('team')->get();
            }
            $teamIds = collect($entries)->pluck('teamId')->filter()->unique()->all();
            $teams = Teams::whereIn('team_id', $teamIds)->pluck('team_name', 'team_id');
            foreach ($entries as $entry) {
                $driverId = $entry['driverId'] ?? null;
                $teamId = $entry['teamId'] ?? null;
                if ($driverId !== null && $teamId !== null && isset($teams[$teamId])) {
                    $driverIdToTeamName[$driverId] = $teams[$teamId];
                }
                if ($driverId !== null) {
                    $apiDriverStats[$driverId] = [
                        'points' => (float) ($entry['points'] ?? 0),
                        'wins' => (int) ($entry['wins'] ?? 0),
                        'podiums' => (int) ($entry['podiums'] ?? 0),
                    ];
                }
            }
        } catch (\Throwable) {
            // API may not have data for future/past years; use empty fallback
        }

        $countriesByName = Countries::all()->keyBy('name');
        $rows = $allDrivers->map(function ($driver) use ($standingsByEntityId, $driverIdToTeamName, $countriesByName, $apiDriverStats) {
            $s = $standingsByEntityId->get((string) $driver->id) ?? $standingsByEntityId->get($driver->driver_id ?? '');
            $apiStats = $driver->driver_id ? ($apiDriverStats[$driver->driver_id] ?? null) : null;
            $points = $s ? (float) $s->points : ($apiStats['points'] ?? 0.0);
            $wins = $s ? (int) ($s->wins ?? 0) : ($apiStats['wins'] ?? 0);
            $podiums = $s ? (int) ($s->podiums ?? 0) : ($apiStats['podiums'] ?? 0);
            $driverName = trim($driver->name.' '.$driver->surname);
            $teamName = $driver->team?->team_name
                ?? ($driver->driver_id ? ($driverIdToTeamName[$driver->driver_id] ?? null) : null);
            $country = $driver->nationality ? $countriesByName->get($driver->nationality) : null;

            return [
                'sort_name' => trim($driver->surname.' '.$driver->name),
                'driver_name' => $driverName,
                'driver_slug' => $driver->slug,
                'nationality' => $driver->nationality,
                'country_flag_url' => $country ? $country->flag_url : '',
                'team_name' => $teamName,
                'team_display_name' => $driver->team?->display_name ?? Teams::displayNameFor($teamName),
                'team_slug' => $driver->team?->slug,
                'points' => $points,
                'wins' => $wins,
                'podiums' => $podiums,
            ];
        });
        $driverRows = $rows->sort(function ($a, $b) {
            $byPoints = (int) round($b['points'] * 100) - (int) round($a['points'] * 100);

            return $byPoints !== 0 ? $byPoints : strcasecmp($a['sort_name'], $b['sort_name']);
        })->values()->map(function ($row, $index) {
            unset($row['sort_name']);

            return array_merge(['position' => $index + 1], $row);
        })->all();

        $seasonStarted = Races::seasonHasStarted($season);
        $seasonEnded = Races::seasonHasEnded($season);

        return view('standings.drivers', [
            'year' => $season,
            'driverRows' => $driverRows,
            'seasonStarted' => $seasonStarted,
            'seasonEnded' => $seasonEnded,
        ]);
    })->name('standings.drivers');

    // constructor standings
    Route::get('/{year}/standings/constructors', function (F1ApiService $f1, $year) {
        $season = (int) $year;
        $teamStandings = Standings::getConstructorStandings($season, null);
        $standingsByEntityId = $teamStandings->keyBy('entity_id');
        $entityIds = $teamStandings->pluck('entity_id')->unique()->filter()->values();
        $allTeams = collect();
        if ($entityIds->isNotEmpty()) {
            $byTeamId = Teams::whereIn('team_id', $entityIds)->with('drivers')->get();
            $numericIds = $entityIds->filter(fn ($id) => ctype_digit((string) $id))->values();
            $byId = $numericIds->isNotEmpty()
                ? Teams::whereIn('id', $numericIds->map(fn ($id) => (int) $id))->with('drivers')->get()
                : collect();
            $allTeams = $byTeamId->merge($byId)->unique('id')->values();
        }
        if ($allTeams->isEmpty()) {
            try {
                $data = $f1->fetchConstructorsChampionship($season);
                $entries = $data['constructors_championship'] ?? [];
                $apiTeamIds = collect($entries)->pluck('teamId')->filter()->unique()->values()->all();
                if ($apiTeamIds !== []) {
                    $allTeams = Teams::whereIn('team_id', $apiTeamIds)->with('drivers')->get();
                }
            } catch (\Throwable) {
                // leave empty
            }
        }
        $countriesByName = Countries::all()->keyBy('name');
        $rows = $allTeams->map(function ($team) use ($standingsByEntityId, $countriesByName) {
            $s = $standingsByEntityId->get((string) $team->id) ?? $standingsByEntityId->get($team->team_id ?? '');
            $points = $s ? (float) $s->points : 0.0;
            $wins = $s ? (int) ($s->wins ?? 0) : 0;
            $podiums = $s ? (int) ($s->podiums ?? 0) : 0;
            $driverNames = $team->drivers->map(fn ($d) => trim($d->name.' '.$d->surname))->values()->all();
            $country = $team->nationality ? $countriesByName->get($team->nationality) : null;

            return [
                'sort_name' => $team->team_name,
                'team_name' => $team->team_name,
                'team_display_name' => $team->display_name,
                'team_slug' => $team->slug,
                'nationality' => $team->nationality,
                'country_flag_url' => $country ? $country->flag_url : '',
                'driver_names' => $driverNames,
                'points' => $points,
                'wins' => $wins,
                'podiums' => $podiums,
            ];
        });
        $teamRows = $rows->sort(function ($a, $b) {
            $byPoints = (int) round($b['points'] * 100) - (int) round($a['points'] * 100);

            return $byPoints !== 0 ? $byPoints : strcasecmp($a['sort_name'], $b['sort_name']);
        })->values()->map(function ($row, $index) {
            unset($row['sort_name']);

            return array_merge(['position' => $index + 1], $row);
        })->all();

        $seasonStarted = Races::seasonHasStarted($season);
        $seasonEnded = Races::seasonHasEnded($season);

        return view('standings.constructors', [
            'year' => $season,
            'teamRows' => $teamRows,
            'seasonStarted' => $seasonStarted,
            'seasonEnded' => $seasonEnded,
        ]);
    })->name('standings.constructors');

    Route::get('/{year}/standings/teams', function ($year) {
        return to_route('standings.constructors', ['year' => $year], 301);
    });

    // prediction standings
    Route::get('/{year}/standings/predictions', function ($year) {
        return view('standings.predictions', ['year' => $year]);
    })->name('standings.predictions');

    Route::get('/{year}/standings/predictions/{username}', function ($year, $username) {
        return view('standings.predictions', ['year' => $year, 'username' => $username]);
    })->name('standings.predictions.user');

    Route::get('/{year}/race/{id}', function ($year, $id) {
        $race = Races::where('season', $year)->where('round', $id)->first();
        abort_unless($race, 404);

        return view('race', ['race' => $race]);
    })->name('race');
});

// non year specific routes
Route::get('/countries', App\Livewire\Pages\CountriesIndex::class)->name('countries');

Route::get('/constructor/{slug}', function ($slug) {
    $constructor = Teams::with('drivers')->get()->first(fn ($t) => $t->slug === $slug);
    abort_unless($constructor, 404);

    return view('constructor', ['constructor' => $constructor]);
})->name('constructor');

Route::redirect('/team/{slug}', '/constructor/{slug}', 301);

Route::get('/driver/{slug}', function ($slug) {
    $driver = Drivers::with('team')->get()->first(fn ($d) => $d->slug === $slug);
    abort_unless($driver, 404);

    return view('driver', ['driver' => $driver]);
})->name('driver');

Route::get('/circuit/{slug}', function ($slug) {
    $circuit = Circuits::all()->first(fn ($c) => $c->slug === $slug);
    abort_unless($circuit, 404);

    return view('circuit', ['circuit' => $circuit]);
})->name('circuit');

Route::get('/country/{slug}', function ($slug) {
    $country = Countries::all()->first(fn ($c) => $c->slug === $slug);
    abort_unless($country, 404);

    return view('country', ['country' => $country]);
})->name('country');

Route::get('/race/{slug}', function ($slug) {
    $race = Races::all()->first(fn ($r) => $r->slug === $slug);
    abort_unless($race, 404);

    return view('race', ['race' => $race]);
})->name('race.detail');

require __DIR__.'/auth.php';
