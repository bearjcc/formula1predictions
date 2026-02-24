<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;

Artisan::command('mail:test {to : Email address to send test to}', function (string $to): int {
    $this->info("Sending test email to {$to}...");
    Mail::raw('Test email from F1 Predictions. If you got this, Resend is working.', function ($m) use ($to): void {
        $m->to($to)->subject('F1 Predictions – Resend test');
    });
    $this->info('Sent.');

    return 0;
})->purpose('Send a test email (e.g. to verify Resend)');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('predictions:lock-past-deadline')->everyFifteenMinutes();
