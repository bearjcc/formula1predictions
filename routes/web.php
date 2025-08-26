<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\RacesController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/components', function () {
    return view('components');
})->name('components');

Route::get('/draggable-demo', function () {
    return view('draggable-demo');
})->name('draggable-demo');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/analytics', function () {
    return view('livewire.pages.analytics');
})->middleware(['auth'])->name('analytics');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    Route::get('predict/create', function () {
        return view('predictions.create-livewire');
    })->name('predict.create');

    Route::get('predictions/{prediction}/edit', function ($prediction) {
        return view('predictions.edit-livewire', compact('prediction'));
    })->name('predictions.edit');

    // Prediction routes
    Route::resource('predictions', PredictionController::class);

    // Admin routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/predictions', [AdminController::class, 'predictions'])->name('predictions');
        Route::get('/races', [AdminController::class, 'races'])->name('races');
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');

        // Prediction management actions
        Route::post('/predictions/{prediction}/score', [AdminController::class, 'scorePrediction'])->name('predictions.score');
        Route::post('/predictions/{prediction}/lock', [AdminController::class, 'lockPrediction'])->name('predictions.lock');
        Route::post('/predictions/{prediction}/unlock', [AdminController::class, 'unlockPrediction'])->name('predictions.unlock');
        Route::delete('/predictions/{prediction}', [AdminController::class, 'deletePrediction'])->name('predictions.delete');
    });

    // Leaderboard routes
    Route::prefix('leaderboard')->name('leaderboard.')->group(function () {
        Route::get('/', [LeaderboardController::class, 'index'])->name('index');
        Route::get('/season/{season}', [LeaderboardController::class, 'season'])->name('season');
        Route::get('/race/{season}/{raceRound}', [LeaderboardController::class, 'race'])->name('race');
        Route::get('/user/{user}', [LeaderboardController::class, 'userStats'])->name('user-stats');
    });
});

// F1 API Test Routes
Route::prefix('api/f1')->group(function () {
    Route::get('/test', [RacesController::class, 'testApi'])->name('f1.test');
    Route::get('/races/{year}', [RacesController::class, 'index'])->name('f1.races');
    Route::get('/races/{year}/{round}', [RacesController::class, 'show'])->name('f1.race');
    Route::delete('/cache/{year}', [RacesController::class, 'clearCache'])->name('f1.clear-cache');
});

// Year specific routes
Route::middleware(['validate.year'])->group(function () {
    Route::get('/{year}/races', function ($year) {
        return view('races', ['year' => $year]);
    })->name('races');

    Route::get('/{year}/standings', function ($year) {
        return view('standings', ['year' => $year]);
    })->name('standings');

    Route::get('/{year}/standings/drivers', function ($year) {
        return view('standings.drivers', ['year' => $year]);
    })->name('standings.drivers');

    // team standings
    Route::get('/{year}/standings/teams', function ($year) {
        return view('standings.teams', ['year' => $year]);
    })->name('standings.teams');

    // prediction standings
    Route::get('/{year}/standings/predictions', function ($year) {
        return view('standings.predictions', ['year' => $year]);
    })->name('standings.predictions');

    Route::get('/{year}/standings/predictions/{username}', function ($year, $username) {
        return view('standings.predictions', ['year' => $year, 'username' => $username]);
    })->name('standings.predictions.user');

    Route::get('/{year}/race/{id}', function ($year, $id) {
        return view('race', ['year' => $year, 'id' => $id]);
    })->name('race');
});

// non year specific routes
Route::get('/countries', function () {
    return view('countries');
})->name('countries');

Route::get('/team/{slug}', function ($slug) {
    return view('team', ['slug' => $slug]);
})->name('team');

Route::get('/driver/{slug}', function ($slug) {
    return view('driver', ['slug' => $slug]);
})->name('driver');

Route::get('/circuit/{slug}', function ($slug) {
    return view('circuit', ['slug' => $slug]);
})->name('circuit');

Route::get('/country/{slug}', function ($slug) {
    return view('country', ['slug' => $slug]);
})->name('country');

Route::get('/race/{slug}', function ($slug) {
    return view('race', ['slug' => $slug]);
})->name('race.detail');

require __DIR__.'/auth.php';
