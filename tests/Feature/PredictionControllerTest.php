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

test('user cannot update a locked prediction', function () {
    $user = User::factory()->create();
    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'type' => 'race',
        'season' => 2024,
        'status' => 'locked',
        'locked_at' => now(),
        'notes' => 'Original notes',
    ]);

    $this->actingAs($user)
        ->patch(route('predictions.update', $prediction), ['notes' => 'Attempted change'])
        ->assertForbidden();

    $prediction->refresh();
    expect($prediction->notes)->toBe('Original notes');
});

test('user cannot update a scored prediction', function () {
    $user = User::factory()->create();
    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'type' => 'race',
        'season' => 2024,
        'status' => 'scored',
        'locked_at' => now(),
        'scored_at' => now(),
        'notes' => 'Original notes',
    ]);

    $this->actingAs($user)
        ->patch(route('predictions.update', $prediction), ['notes' => 'Attempted change'])
        ->assertForbidden();

    $prediction->refresh();
    expect($prediction->notes)->toBe('Original notes');
});

test('user cannot delete a locked prediction', function () {
    $user = User::factory()->create();
    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'type' => 'race',
        'status' => 'locked',
        'locked_at' => now(),
    ]);
    $id = $prediction->id;

    $this->actingAs($user)
        ->delete(route('predictions.destroy', $prediction))
        ->assertForbidden();

    expect(Prediction::find($id))->not->toBeNull();
});

test('user cannot delete a scored prediction', function () {
    $user = User::factory()->create();
    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'type' => 'race',
        'status' => 'scored',
        'locked_at' => now(),
        'scored_at' => now(),
    ]);
    $id = $prediction->id;

    $this->actingAs($user)
        ->delete(route('predictions.destroy', $prediction))
        ->assertForbidden();

    expect(Prediction::find($id))->not->toBeNull();
});

test('mass-assigning score or status via create is rejected', function () {
    $user = User::factory()->create();
    $prediction = Prediction::create([
        'user_id' => $user->id,
        'type' => 'preseason',
        'season' => 2024,
        'race_round' => 1,
        'race_id' => null,
        'prediction_data' => ['team_order' => [1], 'driver_championship' => [1]],
        'notes' => null,
        'score' => 500,
        'accuracy' => 99.9,
        'status' => 'scored',
        'submitted_at' => now(),
        'locked_at' => now(),
        'scored_at' => now(),
    ]);

    $prediction->refresh();
    expect($prediction->score)->toBe(0)
        ->and($prediction->status)->toBe('draft')
        ->and($prediction->accuracy)->toBeNull()
        ->and($prediction->submitted_at)->toBeNull()
        ->and($prediction->locked_at)->toBeNull()
        ->and($prediction->scored_at)->toBeNull();
});
