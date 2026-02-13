<?php

namespace App\Providers;

use App\Models\Circuits;
use App\Models\Countries;
use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\Standings;
use App\Models\Teams;
use App\Models\User;
use App\Policies\CircuitsPolicy;
use App\Policies\CountriesPolicy;
use App\Policies\DriversPolicy;
use App\Policies\PredictionPolicy;
use App\Policies\RacesPolicy;
use App\Policies\StandingsPolicy;
use App\Policies\TeamsPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Use MySQL grammar that checks only BASE TABLE (avoids SYSTEM VERSIONED) so
        // migrations work on Railway and other MySQL/MariaDB where the default query can error.
        if (config('database.default') === 'mysql') {
            $connection = $this->app->make('db')->connection();
            $connection->setSchemaGrammar(new \App\Database\Schema\Grammars\MySqlGrammar($connection));
        }

        // Rate limiters
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        // Checkout rate limiter (monetization) — re-enable with F1-031
        // RateLimiter::for('checkout', function (Request $request) {
        //     return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        // });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('predictions', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('admin', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        // Register policies
        Gate::policy(User::class, \App\Policies\UserPolicy::class);
        Gate::policy(Prediction::class, PredictionPolicy::class);
        Gate::policy(Races::class, RacesPolicy::class);
        Gate::policy(Drivers::class, DriversPolicy::class);
        Gate::policy(Teams::class, TeamsPolicy::class);
        Gate::policy(Circuits::class, CircuitsPolicy::class);
        Gate::policy(Countries::class, CountriesPolicy::class);
        Gate::policy(Standings::class, StandingsPolicy::class);

        // Define additional gates
        Gate::define('view-admin-dashboard', function (User $user) {
            return $user->hasRole('admin');
        });

        Gate::define('manage-users', function (User $user) {
            return $user->hasRole('admin');
        });

        Gate::define('manage-predictions', function (User $user) {
            return $user->hasRole('admin') || $user->hasRole('moderator');
        });

        Gate::define('view-predictions', function (User $user) {
            return true; // All authenticated users can view predictions
        });

        Gate::define('create-predictions', function (User $user) {
            return true; // All authenticated users can create predictions
        });
    }
}
