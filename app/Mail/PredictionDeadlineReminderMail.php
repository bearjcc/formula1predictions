<?php

namespace App\Mail;

use App\Models\Races;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PredictionDeadlineReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Races $race,
        public string $deadlineType,
        public string $recipientName,
        public string $displayName,
        public string $deadlineText,
        public ?string $deadlineNzt,
        public ?string $deadlineEst,
        public string $actionUrl,
        public string $actionText = 'Submit prediction'
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Prediction Deadline Reminder: {$this->displayName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.deadline-reminder',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
