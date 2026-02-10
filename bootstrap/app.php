<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'validate.year' => \App\Http\Middleware\ValidateYear::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);

        // Trust Railway's reverse proxy so Laravel correctly detects HTTPS and
        // generates secure asset / route URLs when behind a load balancer.
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
                | Request::HEADER_X_FORWARDED_AWS_ELB,
        );

        $middleware->validateCsrfTokens(except: ['stripe/webhook']);
    })
    ->withSchedule(function (Schedule $schedule) {
        // Weekly sync of current F1 season data (races, drivers, teams) so the app
        // serves almost everything from the local database/cache and only hits the
        // external API once a week on Tuesday.
        $schedule->command('f1:sync-season')->weeklyOn(2, '03:00');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
