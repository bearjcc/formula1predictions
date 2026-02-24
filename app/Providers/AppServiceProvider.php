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
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
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
        // Use custom MySqlConnection that uses PDO::query() for SHOW TABLES (avoids prepare).
        \Illuminate\Database\Connection::resolverFor('mysql', function ($connection, $database, $prefix, $config) {
            return new \App\Database\MySqlConnection($connection, $database, $prefix, $config);
        });
        \Illuminate\Database\Connection::resolverFor('mariadb', function ($connection, $database, $prefix, $config) {
            return new \App\Database\MySqlConnection($connection, $database, $prefix, $config);
        });
        // Replace db manager so mysql database config is always cast to string.
        $this->app->singleton('db', function ($app) {
            return new \App\Database\DatabaseManager($app, $app['db.factory']);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // #region Auth email customization (verification + password reset)
        VerifyEmail::toMailUsing(function (object $notifiable, string $url): MailMessage {
            return (new MailMessage)
                ->subject(__('Verify your email address'))
                ->markdown('emails.verify-email', ['url' => $url]);
        });

        ResetPassword::createUrlUsing(function (User $user, string $token): string {
            return url(route('password.reset', [
                'token' => $token,
                'email' => $user->getEmailForPasswordReset(),
            ], false));
        });

        ResetPassword::toMailUsing(function (object $notifiable, string $token): MailMessage {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new MailMessage)
                ->subject(__('Reset your password'))
                ->markdown('emails.reset-password', ['url' => $url]);
        });
        // #endregion

        // When DB_URL and DB_DATABASE are both set, strip url from mysql config and
        // inject parsed connection params so ConfigurationUrlParser never overwrites
        // database with a numeric value (Railway "near '10'" fix).
        $this->forceExplicitDatabaseWhenSet();

        // Use MySQL grammar that checks only BASE TABLE (avoids SYSTEM VERSIONED) so
        // migrations work on Railway and other MySQL/MariaDB where the default query can error.
        // Set for 'mysql' whenever that connection exists so migrate (and any mysql use) gets it
        // even if default was cached as non-mysql at build time.
        if (array_key_exists('mysql', config('database.connections', []))) {
            $connection = $this->app->make('db')->connection('mysql');
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

    /**
     * When DB_DATABASE is set, replace mysql config url with parsed params so
     * ConfigurationUrlParser never overwrites database (fixes Railway "near '10'").
     * Prefer MYSQL_PRIVATE_URL when DB_USE_PRIVATE_MYSQL=1 (bypasses public proxy).
     * When explicit vars exist (DB_HOST, DB_DATABASE, etc.), strip url so parser never runs.
     */
    private function forceExplicitDatabaseWhenSet(): void
    {
        $mysql = config('database.connections.mysql');
        if (! is_array($mysql)) {
            return;
        }

        $hasExplicitVars = ! empty($mysql['host'] ?? null)
            && ! empty($mysql['database'] ?? null)
            && ! empty($mysql['username'] ?? null);

        if ($hasExplicitVars && ! empty($mysql['url'] ?? null)) {
            config(['database.connections.mysql' => array_merge($mysql, ['url' => null])]);
            $this->ensureDatabaseIsString(config('database.connections.mysql'));

            return;
        }

        $url = $mysql['url'] ?? null;
        if (config('app.env') === 'production' && config('database.use_private_mysql')) {
            $privateUrl = config('database.mysql_private_url');
            if (! empty($privateUrl)) {
                $url = $privateUrl;
            }
        }
        if (empty($url)) {
            $this->ensureDatabaseIsString($mysql);

            return;
        }

        $explicitDb = $mysql['database'] ?? null;
        if ($explicitDb === null || $explicitDb === '') {
            return;
        }

        $parsed = parse_url($url);
        if ($parsed === false || ! isset($parsed['host'])) {
            return;
        }

        config(['database.connections.mysql' => array_merge($mysql, [
            'url' => null,
            'host' => $parsed['host'] ?? ($mysql['host'] ?? '127.0.0.1'),
            'port' => $parsed['port'] ?? ($mysql['port'] ?? '3306'),
            'username' => isset($parsed['user']) ? rawurldecode($parsed['user']) : ($mysql['username'] ?? 'root'),
            'password' => isset($parsed['pass']) ? rawurldecode($parsed['pass']) : ($mysql['password'] ?? ''),
            'database' => (string) $explicitDb,
        ])]);
    }

    /**
     * Ensure database config is string when using explicit vars (no url).
     * Prevents int from json_decode of numeric path leaking through cached config.
     */
    private function ensureDatabaseIsString(array $mysql): void
    {
        $db = $mysql['database'] ?? null;
        if ($db !== null && $db !== '' && ! is_string($db)) {
            config(['database.connections.mysql' => array_merge($mysql, ['database' => (string) $db])]);
        }
    }
}
