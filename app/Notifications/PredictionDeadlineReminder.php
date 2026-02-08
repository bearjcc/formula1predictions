<?php

namespace App\Notifications;

use App\Models\Races;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PredictionDeadlineReminder extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Races $race,
        public string $deadlineType = 'qualifying'
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $deadlineText = match ($this->deadlineType) {
            'qualifying' => 'qualifying session',
            'race' => 'race start',
            'preseason' => 'season start',
            'midseason' => 'summer break',
            default => 'deadline'
        };

        return (new MailMessage)
            ->subject("Prediction Deadline Reminder: {$this->race->race_name}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Don't forget to submit your predictions for {$this->race->race_name}!")
            ->line("The deadline is before the {$deadlineText}.")
            ->action('Submit Prediction', url('/predictions/create'))
            ->action('View Race Details', url("/{$this->race->season}/race/{$this->race->id}"))
            ->line('Good luck with your predictions!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'prediction_deadline_reminder',
            'race_id' => $this->race->id,
            'race_name' => $this->race->race_name,
            'season' => $this->race->season,
            'round' => $this->race->round,
            'deadline_type' => $this->deadlineType,
            'message' => "Don't forget to submit your predictions for {$this->race->race_name}",
            'action_url' => '/predictions/create',
        ];
    }
}
