<?php

use App\Mail\FeedbackReceived;
use App\Models\Feedback;
use Illuminate\Support\Facades\Mail;
use Livewire\Volt\Component;

new class extends Component {
    public string $subject = '';
    public string $message = '';

    /**
     * Submit feedback. Store in database and optionally email site owner.
     */
    public function submit(): void
    {
        $validated = $this->validate([
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:10000'],
        ]);

        $feedback = Feedback::create([
            'user_id' => auth()->id(),
            'message' => $validated['message'],
            'subject' => $validated['subject'] ?: null,
        ]);

        $to = config('mail.feedback_to');
        if (! empty($to)) {
            Mail::to($to)->send(new FeedbackReceived($feedback));
        }

        $this->reset(['subject', 'message']);
        $this->dispatch('feedback-sent');
    }
}; ?>

@layout('components.layouts.app')
@layoutData(['title' => __('Feedback'), 'headerSubtitle' => __('Send us your feedback or suggestions')])

<div class="max-w-xl">
    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
        <p class="text-zinc-600 dark:text-zinc-400 mb-6">
            {{ __('We’d love to hear from you. Use the form below to send a message. Only you and the site owner can see it; there is no public listing.') }}
        </p>

        <form wire:submit="submit" class="space-y-6">
            <x-mary-input
                wire:model="subject"
                :label="__('Subject (optional)')"
                type="text"
                maxlength="255"
                placeholder="{{ __('Brief topic, e.g. Feature request') }}"
                class="input-bordered"
            />

            <div>
                <x-mary-textarea
                    wire:model="message"
                    :label="__('Message')"
                    placeholder="{{ __('Your feedback...') }}"
                    rows="5"
                    class="textarea-bordered"
                    required
                />
            </div>

            <div class="flex items-center gap-4">
                <x-mary-button type="submit" class="btn-primary">
                    {{ __('Send feedback') }}
                </x-mary-button>
                <x-action-message on="feedback-sent" class="text-green-600 dark:text-green-400">
                    {{ __('Thanks! Your feedback has been sent.') }}
                </x-action-message>
            </div>
        </form>
    </div>
</div>
