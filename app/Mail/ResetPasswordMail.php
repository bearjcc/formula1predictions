<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param  string  $token  Password reset token (sensitive).
     */
    public function __construct(
        public User $user,
        #[\SensitiveParameter]
        public string $token
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Reset your password'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $this->user->getEmailForPasswordReset(),
        ], false));

        $expireMinutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

        return new Content(
            view: 'emails.reset-password',
            with: [
                'resetUrl' => $resetUrl,
                'expireMinutes' => $expireMinutes,
                'subject' => __('Reset your password'),
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
