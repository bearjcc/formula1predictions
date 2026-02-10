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
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('home');
})->name('home');

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
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/analytics', App\Livewire\Pages\Analytics::class)->middleware(['auth'])->name('analytics');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    // Notifications route
    Volt::route('notifications', 'pages.notifications.index')->name('notifications.index');

    Route::get('predict/create', function () {
        return view('predictions.create-livewire');
    })->name('predict.create');

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

    // Leaderboard routes
    Route::prefix('leaderboard')->name('leaderboard.')->group(function () {
        Route::get('/', [LeaderboardController::class, 'index'])->name('index');
        Route::get('/compare', [LeaderboardController::class, 'compare'])->name('compare');
        Route::get('/livewire', function () {
            return view('leaderboard.index-livewire');
        })->name('livewire');
        Route::get('/season/{season}', [LeaderboardController::class, 'season'])->name('season');
        Route::get('/race/{season}/{raceRound}', [LeaderboardController::class, 'race'])->name('race');
        Route::get('/user/{user}', [LeaderboardController::class, 'userStats'])->name('user-stats');
        Route::get('/user/{user}/livewire', function ($user) {
            return view('leaderboard.user-stats-livewire', ['user' => $user]);
        })->name('user-stats-livewire');
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

// Year specific routes
Route::middleware(['validate.year'])->group(function () {
    Route::get('/{year}/races', function ($year) {
        return view('races', ['year' => $year]);
    })->name('races');

    Route::get('/{year}/standings', function ($year) {
        return view('standings', ['year' => $year]);
    })->name('standings');

    Route::get('/{year}/standings/drivers', function ($year) {
        $driverStandings = Standings::getDriverStandings((int) $year, null);
        $entityIds = $driverStandings->pluck('entity_id')->unique()->filter()->values();
        $numericIds = $entityIds->filter(fn ($id) => is_numeric($id))->map(fn ($id) => (int) $id)->values();
        $driversByEntityId = collect();
        if ($numericIds->isNotEmpty()) {
            Drivers::whereIn('id', $numericIds)->with('team')->get()->each(function ($d) use (&$driversByEntityId) {
                $driversByEntityId[$d->id] = $d;
            });
        }
        $stringIds = $entityIds->filter(fn ($id) => ! is_numeric($id))->values();
        if ($stringIds->isNotEmpty()) {
            Drivers::whereIn('driver_id', $stringIds)->with('team')->get()->each(function ($d) use (&$driversByEntityId) {
                $driversByEntityId[$d->driver_id] = $d;
            });
        }
        $driverRows = $driverStandings->map(function ($s) use ($driversByEntityId) {
            $driver = $driversByEntityId[$s->entity_id] ?? null;

            return [
                'position' => $s->position,
                'driver_name' => $s->entity_name,
                'nationality' => $driver?->nationality ?? null,
                'team_name' => $driver?->team?->team_name ?? null,
                'points' => $s->points,
                'wins' => (int) ($s->wins ?? 0),
                'podiums' => (int) ($s->podiums ?? 0),
            ];
        })->all();

        return view('standings.drivers', ['year' => (int) $year, 'driverRows' => $driverRows]);
    })->name('standings.drivers');

    // team standings
    Route::get('/{year}/standings/teams', function ($year) {
        $teamStandings = Standings::getConstructorStandings((int) $year, null);
        $entityIds = $teamStandings->pluck('entity_id')->unique()->filter()->values();
        $numericIds = $entityIds->filter(fn ($id) => is_numeric($id))->map(fn ($id) => (int) $id)->values();
        $teamsByEntityId = collect();
        if ($numericIds->isNotEmpty()) {
            Teams::whereIn('id', $numericIds)->get()->each(function ($t) use (&$teamsByEntityId) {
                $teamsByEntityId[$t->id] = $t;
                $teamsByEntityId[$t->team_id] = $t;
            });
        }
        $stringIds = $entityIds->filter(fn ($id) => ! is_numeric($id))->values();
        if ($stringIds->isNotEmpty()) {
            Teams::whereIn('team_id', $stringIds)->get()->each(function ($t) use (&$teamsByEntityId) {
                $teamsByEntityId[$t->team_id] = $t;
                $teamsByEntityId[$t->id] = $t;
            });
        }
        $teamIds = collect($teamsByEntityId)->pluck('id')->unique()->filter()->values();
        $driverNamesByTeamId = $teamIds->isNotEmpty()
            ? Drivers::whereIn('team_id', $teamIds)->get()->groupBy('team_id')->map(fn ($drivers) => $drivers->map(fn ($d) => trim($d->name.' '.$d->surname))->values()->all())
            : collect();
        $teamRows = $teamStandings->map(function ($s) use ($teamsByEntityId, $driverNamesByTeamId) {
            $team = $teamsByEntityId[$s->entity_id] ?? null;
            $teamId = $team?->id;
            $driverNames = $teamId ? ($driverNamesByTeamId[$teamId] ?? []) : [];

            return [
                'position' => $s->position,
                'team_name' => $s->entity_name,
                'nationality' => $team?->nationality ?? null,
                'driver_names' => $driverNames,
                'points' => $s->points,
                'wins' => (int) ($s->wins ?? 0),
                'podiums' => (int) ($s->podiums ?? 0),
            ];
        })->all();

        return view('standings.teams', ['year' => (int) $year, 'teamRows' => $teamRows]);
    })->name('standings.teams');

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

Route::get('/team/{slug}', function ($slug) {
    $team = Teams::with('drivers')->get()->first(fn ($t) => $t->slug === $slug);
    abort_unless($team, 404);

    return view('team', ['team' => $team]);
})->name('team');

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
