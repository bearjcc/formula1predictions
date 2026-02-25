<?php

namespace App\Listeners;

use App\Mail\NewUserRegistered;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;

class SendNewUserRegisteredNotification
{
    /**
     * Send admin an email when a new user registers (only when ADMIN_EMAIL is set).
     */
    public function handle(Registered $event): void
    {
        $adminEmail = config('admin.promotable_admin_email');
        if (empty($adminEmail)) {
            return;
        }

        Mail::to($adminEmail)->send(new NewUserRegistered($event->user));
    }
}
