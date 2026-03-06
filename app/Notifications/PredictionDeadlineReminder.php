<?php

namespace App\Notifications;

use App\Mail\PredictionDeadlineReminderMail;
use App\Models\Races;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
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
     * Resolve the prediction deadline for this reminder type.
     */
    public function getDeadline(): ?\Carbon\Carbon
    {
        return match ($this->deadlineType) {
            'qualifying', 'race' => $this->race->getRacePredictionDeadline(),
            'sprint' => $this->race->getSprintPredictionDeadline(),
            'preseason' => Races::getPreseasonDeadlineForSeason($this->race->season),
            default => null,
        };
    }

    /**
     * Action URL to submit prediction (preseason vs race/sprint).
     */
    public function getActionUrl(): string
    {
        if ($this->deadlineType === 'preseason') {
            return url(route('predict.preseason', ['year' => $this->race->season], absolute: false));
        }

        if ($this->race->id !== null) {
            return url(route('predict.create', ['race_id' => $this->race->id], absolute: false));
        }

        return url(route('predict.create', [], absolute: false));
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): PredictionDeadlineReminderMail
    {
        $deadlineText = match ($this->deadlineType) {
            'qualifying' => 'qualifying session',
            'race' => 'race start',
            'sprint' => 'sprint qualifying',
            'preseason' => 'season start',
            'midseason' => 'summer break',
            default => 'deadline',
        };

        $deadline = $this->getDeadline();
        $nzt = $deadline !== null
            ? $deadline->copy()->timezone('Pacific/Auckland')->format('M j, Y g:i A T')
            : null;
        $est = $deadline !== null
            ? $deadline->copy()->timezone('America/New_York')->format('M j, Y g:i A T')
            : null;

        $mailable = new PredictionDeadlineReminderMail(
            race: $this->race,
            deadlineType: $this->deadlineType,
            recipientName: $notifiable->name ?? 'there',
            displayName: $this->race->display_name,
            deadlineText: $deadlineText,
            deadlineNzt: $nzt,
            deadlineEst: $est,
            actionUrl: $this->getActionUrl(),
            actionText: 'Submit prediction',
        );

        $address = $notifiable->routeNotificationFor('mail');
        if ($address !== null) {
            $mailable->to($address);
        }

        return $mailable;
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
            'message' => "Don't forget to submit your predictions for {$this->race->display_name}",
            'action_url' => $this->deadlineType === 'preseason'
                ? '/predict/preseason?year='.$this->race->season
                : ($this->race->id !== null ? '/predict/create?race_id='.$this->race->id : '/predict/create'),
        ];
    }
}
