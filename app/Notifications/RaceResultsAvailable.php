<?php

namespace App\Notifications;

use App\Models\Races;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RaceResultsAvailable extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Races $race
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
        return (new MailMessage)
            ->subject("Race Results Available: {$this->race->display_name}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("The results for {$this->race->display_name} are now available.")
            ->line('Your predictions for this race have been scored and your points have been updated.')
            ->action('View Results', url("/{$this->race->season}/race/{$this->race->id}"))
            ->action('View Your Predictions', url('/predictions'))
            ->line('Thank you for participating in F1 Predictions!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'race_results_available',
            'race_id' => $this->race->id,
            'race_name' => $this->race->race_name,
            'season' => $this->race->season,
            'round' => $this->race->round,
            'message' => "Race results for {$this->race->display_name} are now available",
            'action_url' => "/{$this->race->season}/race/{$this->race->id}",
        ];
    }
}
