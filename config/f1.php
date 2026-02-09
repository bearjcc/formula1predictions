<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Current F1 Season
    |--------------------------------------------------------------------------
    |
    | The current Formula 1 season year. Used for navigation links, year
    | validation, and default year selection throughout the application.
    | Automatically uses the current year.
    |
    */

    'current_season' => (int) date('Y'),

    /*
    |--------------------------------------------------------------------------
    | Max Grid Size (2026+)
    |--------------------------------------------------------------------------
    |
    | 2026 season: 11 teams, 22 drivers. Used for validation and UI limits.
    |
    */
    'max_drivers' => (int) env('F1_MAX_DRIVERS', 22),
    'max_teams' => (int) env('F1_MAX_TEAMS', 11),

    /*
    |--------------------------------------------------------------------------
    | Default Appearance (theme)
    |--------------------------------------------------------------------------
    |
    | Default theme when user has not set a preference: 'light', 'dark', or
    | 'system' (follow OS). Set to 'dark' for F1-branded default.
    |
    */
    'default_appearance' => env('F1_DEFAULT_APPEARANCE', 'system'),

];
