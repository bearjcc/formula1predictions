<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FeedbackController as AdminFeedbackController;
use App\Http\Controllers\Admin\NewsController as AdminNewsController;
use App\Http\Controllers\Admin\PredictionsController as AdminPredictionsController;
use App\Http\Controllers\Admin\RacesController as AdminRacesController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\UsersController as AdminUsersController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\RacesController;
use App\Http\Controllers\StandingsController;
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

// Public news and RSS (feed route before /news/{news} to avoid "feed" as id)
Route::get('/news/feed', [NewsController::class, 'feed'])->name('news.feed');
Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/{news}', [NewsController::class, 'show'])->name('news.show')->whereNumber('news');

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

    Route::get('predict/create', [PredictionController::class, 'createForm'])->name('predict.create');
    Route::get('predict/preseason', [PredictionController::class, 'preseasonForm'])->name('predict.preseason');

    // Prediction routes (edit uses controller for policy authorization)
    Route::resource('predictions', PredictionController::class)->middleware('throttle:predictions');

    // Admin routes (admin middleware restricts to admin role)
    Route::prefix('admin')->name('admin.')->middleware(['admin', 'throttle:admin'])->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('/users', [AdminUsersController::class, 'index'])->name('users');
        Route::post('/users/{user}/promote-admin', [AdminUsersController::class, 'promote'])->name('users.promote-admin');
        Route::post('/users/{user}/demote-admin', [AdminUsersController::class, 'demote'])->name('users.demote-admin');

        Route::get('/predictions', [AdminPredictionsController::class, 'index'])->name('predictions');

        Route::get('/races', [AdminRacesController::class, 'index'])->name('races');
        Route::get('/scoring', [AdminRacesController::class, 'scoring'])->name('scoring');

        Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings');

        Route::get('/feedback', [AdminFeedbackController::class, 'index'])->name('feedback');
        Route::delete('/feedback/{feedback}', [AdminFeedbackController::class, 'destroy'])->name('feedback.delete');

        // News (admin CRUD)
        Route::get('/news', [AdminNewsController::class, 'index'])->name('news.index');
        Route::get('/news/create', [AdminNewsController::class, 'create'])->name('news.create');
        Route::post('/news', [AdminNewsController::class, 'store'])->name('news.store');
        Route::get('/news/{news}/edit', [AdminNewsController::class, 'edit'])->name('news.edit');
        Route::put('/news/{news}', [AdminNewsController::class, 'update'])->name('news.update');
        Route::delete('/news/{news}', [AdminNewsController::class, 'destroy'])->name('news.destroy');

        // Prediction management actions
        Route::post('/predictions/{prediction}/score', [AdminPredictionsController::class, 'score'])->name('predictions.score');
        Route::post('/predictions/{prediction}/lock', [AdminPredictionsController::class, 'lock'])->name('predictions.lock');
        Route::post('/predictions/{prediction}/unlock', [AdminPredictionsController::class, 'unlock'])->name('predictions.unlock');
        Route::delete('/predictions/{prediction}', [AdminPredictionsController::class, 'destroy'])->name('predictions.delete');

        // Scoring management
        Route::post('/races/{race}/score', [AdminRacesController::class, 'score'])->name('races.score');
        Route::post('/races/{race}/queue-scoring', [AdminRacesController::class, 'queueScoring'])->name('races.queue-scoring');
        Route::post('/races/{race}/substitutions', [AdminRacesController::class, 'handleDriverSubstitutions'])->name('races.substitutions');
        Route::post('/races/{race}/cancel', [AdminRacesController::class, 'cancel'])->name('races.cancel');
        Route::post('/races/{race}/toggle-half-points', [AdminRacesController::class, 'toggleHalfPoints'])->name('races.toggle-half-points');
        Route::get('/races/{race}/scoring-stats', [AdminRacesController::class, 'scoringStats'])->name('races.scoring-stats');
        Route::post('/bulk-score', [AdminRacesController::class, 'bulkScore'])->name('bulk-score');

        // Score overrides
        Route::post('/predictions/{prediction}/override-score', [AdminPredictionsController::class, 'overrideScore'])->name('predictions.override-score');
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
    Route::get('/{year}/races', [StandingsController::class, 'races'])->name('races');

    Route::get('/{year}/standings', [StandingsController::class, 'standings'])->name('standings');

    Route::get('/{year}/standings/drivers', [StandingsController::class, 'driverStandings'])
        ->name('standings.drivers');

    // constructor standings
    Route::get('/{year}/standings/constructors', [StandingsController::class, 'constructorStandings'])
        ->name('standings.constructors');

    Route::get('/{year}/standings/teams', function ($year) {
        return to_route('standings.constructors', ['year' => $year], 301);
    });

    // prediction standings
    Route::get('/{year}/standings/predictions', [StandingsController::class, 'predictionStandings'])
        ->name('standings.predictions');

    Route::get('/{year}/standings/predictions/{username}', [StandingsController::class, 'predictionStandings'])
        ->name('standings.predictions.user');

    Route::get('/{year}/race/{id}', [StandingsController::class, 'yearRaceDetail'])
        ->name('race');
});

Route::get('/constructor/{slug}', [StandingsController::class, 'constructorDetail'])
    ->name('constructor');

Route::redirect('/team/{slug}', '/constructor/{slug}', 301);

Route::get('/driver/{slug}', [StandingsController::class, 'driverDetail'])
    ->name('driver');

Route::get('/circuit/{slug}', [StandingsController::class, 'circuitDetail'])
    ->name('circuit');

Route::get('/race/{slug}', [StandingsController::class, 'raceDetail'])
    ->name('race.detail');

require __DIR__.'/auth.php';
