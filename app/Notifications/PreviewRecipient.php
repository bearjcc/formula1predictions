<?php

namespace App\Notifications;

use Illuminate\Notifications\Notifiable;

/**
 * Minimal notifiable for previewing emails: has name and email, mail channel only.
 */
class PreviewRecipient
{
    use Notifiable;

    public function __construct(
        public string $name,
        public string $email
    ) {}
}
