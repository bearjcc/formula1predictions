<?php

use App\Mail\FeedbackReceived;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Volt\Volt as LivewireVolt;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('guest cannot access feedback page', function () {
    $response = $this->get(route('feedback'));

    $response->assertRedirect(route('login', absolute: false));
});

test('authenticated user can load feedback page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('feedback'));

    $response->assertOk();
    $response->assertSee(__('Feedback'), false);
    $response->assertSee(__('Send feedback'), false);
});

test('authenticated user can submit feedback and it is stored', function () {
    $user = User::factory()->create();

    LivewireVolt::actingAs($user)
        ->test('pages.feedback')
        ->set('subject', 'Feature idea')
        ->set('message', 'Please add dark mode.')
        ->call('submit');

    $this->assertDatabaseHas('feedback', [
        'user_id' => $user->id,
        'message' => 'Please add dark mode.',
        'subject' => 'Feature idea',
    ]);
});

test('authenticated user can submit feedback without subject', function () {
    $user = User::factory()->create();

    LivewireVolt::actingAs($user)
        ->test('pages.feedback')
        ->set('message', 'Just a quick note.')
        ->call('submit');

    $this->assertDatabaseHas('feedback', [
        'user_id' => $user->id,
        'message' => 'Just a quick note.',
    ]);
    $feedback = Feedback::where('user_id', $user->id)->first();
    expect($feedback->subject)->toBeNull();
});

test('feedback submission requires message', function () {
    $user = User::factory()->create();

    LivewireVolt::actingAs($user)
        ->test('pages.feedback')
        ->set('message', '')
        ->call('submit')
        ->assertHasErrors(['message']);

    $this->assertDatabaseCount('feedback', 0);
});

test('when MAIL_FEEDBACK_TO is set feedback email is sent', function () {
    Mail::fake();
    config(['mail.feedback_to' => 'owner@example.com']);

    $user = User::factory()->create();

    LivewireVolt::actingAs($user)
        ->test('pages.feedback')
        ->set('subject', 'Bug report')
        ->set('message', 'Something broke.')
        ->call('submit');

    $feedback = Feedback::where('user_id', $user->id)->first();
    expect($feedback)->not->toBeNull();

    Mail::assertSent(FeedbackReceived::class, function ($mail) use ($feedback) {
        return $mail->feedback->id === $feedback->id;
    });
});

test('when MAIL_FEEDBACK_TO is not set no feedback email is sent', function () {
    Mail::fake();
    config(['mail.feedback_to' => null]);

    $user = User::factory()->create();

    LivewireVolt::actingAs($user)
        ->test('pages.feedback')
        ->set('message', 'Stored only.')
        ->call('submit');

    $this->assertDatabaseCount('feedback', 1);
    Mail::assertNothingSent();
});
