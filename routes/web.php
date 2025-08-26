<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    Volt::route('predict/{slug}', 'predict.create')->name('predict.create');
    Volt::route('predict/{slug}', 'predict.edit')->name('predict.edit');
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

require __DIR__ . '/auth.php';
