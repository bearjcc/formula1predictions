<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin promotion email
    |--------------------------------------------------------------------------
    | Set ADMIN_EMAIL in .env to the address that should be promoted to admin.
    | Run: php artisan app:promote-admin
    | (Or pass the email: php artisan app:promote-admin you@example.com)
    */
    'promotable_admin_email' => env('ADMIN_EMAIL'),
];
