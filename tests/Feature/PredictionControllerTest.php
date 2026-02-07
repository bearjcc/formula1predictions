<?php

declare(strict_types=1);

use App\Models\Prediction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can view own prediction', function () {
    $user = User::factory()->create();
    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'type' => 'race',
        'season' => 2024,
    ]);

    $response = $this->actingAs($user)->get(route('predictions.show', $prediction));

    $response->assertOk();
    $response->assertViewIs('predictions.show');
    $response->assertSee($prediction->type);
});

test('user cannot view another users prediction', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $prediction = Prediction::factory()->create([
        'user_id' => $owner->id,
        'type' => 'race',
    ]);

    $this->actingAs($other)
        ->get(route('predictions.show', $prediction))
        ->assertForbidden();
});

test('guest cannot view prediction', function () {
    $prediction = Prediction::factory()->create();

    $this->get(route('predictions.show', $prediction))
        ->assertRedirect('/login');
});

test('user can update own draft prediction', function () {
    $user = User::factory()->create();
    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'type' => 'preseason',
        'season' => 2024,
        'status' => 'draft',
        'notes' => 'Original notes',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('predictions.update', $prediction), [
            'notes' => 'Updated notes',
        ]);

    $response->assertRedirect(route('predictions.show', $prediction));
    $response->assertSessionHas('success');

    $prediction->refresh();
    expect($prediction->notes)->toBe('Updated notes');
});

test('user cannot update another users prediction', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $prediction = Prediction::factory()->create([
        'user_id' => $owner->id,
        'type' => 'preseason',
        'status' => 'draft',
    ]);

    $this->actingAs($other)
        ->patch(route('predictions.update', $prediction), ['notes' => 'Hacked'])
        ->assertForbidden();
});

test('user can delete own draft prediction', function () {
    $user = User::factory()->create();
    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'type' => 'race',
        'status' => 'draft',
    ]);
    $id = $prediction->id;

    $response = $this->actingAs($user)
        ->delete(route('predictions.destroy', $prediction));

    $response->assertRedirect(route('predictions.index'));
    $response->assertSessionHas('success');
    expect(Prediction::find($id))->toBeNull();
});

test('user cannot delete another users prediction', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $prediction = Prediction::factory()->create([
        'user_id' => $owner->id,
        'type' => 'race',
    ]);

    $this->actingAs($other)
        ->delete(route('predictions.destroy', $prediction))
        ->assertForbidden();

    expect(Prediction::find($prediction->id))->not->toBeNull();
});
