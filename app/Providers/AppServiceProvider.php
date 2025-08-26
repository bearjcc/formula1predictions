<?php

namespace App\Providers;

use App\Models\{Circuits, Countries, Drivers, Prediction, Races, Standings, Teams, User};
use App\Policies\{CircuitsPolicy, CountriesPolicy, DriversPolicy, PredictionPolicy, RacesPolicy, StandingsPolicy, TeamsPolicy};
use Illuminate\Support\Facades\Gate;
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
