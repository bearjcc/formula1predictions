<?php

namespace App\Notifications;

/**
 * Same as PredictionDeadlineReminder but mail-only and sync (no queue, no database).
 * Use for previews so no notification row is stored and the email sends immediately.
 */
class PredictionDeadlineReminderPreview extends PredictionDeadlineReminder
{
    public function via(object $notifiable): array
    {
        return ['mail'];
    }
}
