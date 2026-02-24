<?php

namespace App\Notifications;

use App\Models\Prediction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PredictionScored extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Prediction $prediction,
        public int $score,
        public float $accuracy
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
        $predictionType = match ($this->prediction->type) {
            'race' => 'Race Prediction',
            'preseason' => 'Preseason Prediction',
            'midseason' => 'Midseason Prediction',
            default => 'Prediction'
        };

        $message = (new MailMessage)
            ->subject("Prediction Scored: {$predictionType}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your {$predictionType} has been scored!");

        if ($this->prediction->type === 'race' && $this->prediction->race) {
            $message->line("Race: {$this->prediction->race->display_name}")
                ->action('View Race Results', url("/{$this->prediction->season}/race/{$this->prediction->race->id}"));
        }

        $message->line("Score: {$this->score} points")
            ->line('Accuracy: '.number_format($this->accuracy, 1).'%')
            ->action('View Your Predictions', url('/predictions'))
            ->line('Keep up the great work!');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'prediction_scored',
            'prediction_id' => $this->prediction->id,
            'prediction_type' => $this->prediction->type,
            'season' => $this->prediction->season,
            'race_name' => $this->prediction->race?->race_name,
            'score' => $this->score,
            'accuracy' => $this->accuracy,
            'message' => sprintf(
                'Your %s prediction for %s has been scored: %d points (%.1f%% accuracy)',
                $this->prediction->type,
                $this->prediction->race?->display_name ?? "{$this->prediction->season} season",
                $this->score,
                $this->accuracy
            ),
            'action_url' => '/predictions',
        ];
    }
}
