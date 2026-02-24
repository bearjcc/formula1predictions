<?php

declare(strict_types=1);

use App\Models\Prediction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

function testUser(): User
{
    $user = User::factory()->create();
    assert($user instanceof User);

    return $user;
}

test('user can view own prediction', function () {
    $user = testUser();
    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'type' => 'race',
        'season' => 2024,
    ]);

    $response = actingAs($user)->get(route('predictions.show', $prediction));

    $response->assertOk();
    $response->assertViewIs('predictions.show');
    $response->assertSee($prediction->type);
});

test('user cannot view another users prediction', function () {
    $owner = testUser();
    $other = testUser();
    $prediction = Prediction::factory()->create([
        'user_id' => $owner->id,
        'type' => 'race',
    ]);

    actingAs($other)
        ->get(route('predictions.show', $prediction))
        ->assertForbidden();
});

test('guest cannot view prediction', function () {
    $prediction = Prediction::factory()->create();

    get(route('predictions.show', $prediction))
        ->assertRedirect('/login');
});

test('user can update own editable prediction', function () {
    $user = testUser();
    // midseason predictions are always editable (no deadline check), regardless of status
    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'type' => 'midseason',
        'season' => 2024,
        'status' => 'submitted',
        'notes' => 'Original notes',
    ]);

    $response = actingAs($user)
        ->patch(route('predictions.update', $prediction), [
            'notes' => 'Updated notes',
        ]);

    $response->assertRedirect(route('predictions.show', $prediction));
    $response->assertSessionHas('success');

    $prediction->refresh();
    expect($prediction->notes)->toBe('Updated notes');
});

test('user cannot update another users prediction', function () {
    $owner = testUser();
    $other = testUser();
    $prediction = Prediction::factory()->create([
        'user_id' => $owner->id,
        'type' => 'preseason',
        'status' => 'draft',
    ]);

    actingAs($other)
        ->patch(route('predictions.update', $prediction), ['notes' => 'Hacked'])
        ->assertForbidden();
});

test('user can delete own draft prediction', function () {
    $user = testUser();
    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'type' => 'race',
        'status' => 'draft',
    ]);
    $id = $prediction->id;

    $response = actingAs($user)
        ->delete(route('predictions.destroy', $prediction));

    $response->assertRedirect(route('predictions.index'));
    $response->assertSessionHas('success');
    expect(Prediction::find($id))->toBeNull();
});

test('user cannot delete another users prediction', function () {
    $owner = testUser();
    $other = testUser();
    $prediction = Prediction::factory()->create([
        'user_id' => $owner->id,
        'type' => 'race',
    ]);

    actingAs($other)
        ->delete(route('predictions.destroy', $prediction))
        ->assertForbidden();

    expect(Prediction::find($prediction->id))->not->toBeNull();
});

test('user cannot update a locked prediction', function () {
    $user = testUser();
    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'type' => 'race',
        'season' => 2024,
        'status' => 'locked',
        'locked_at' => now(),
        'notes' => 'Original notes',
    ]);

    actingAs($user)
        ->patch(route('predictions.update', $prediction), ['notes' => 'Attempted change'])
        ->assertForbidden();

    $prediction->refresh();
    expect($prediction->notes)->toBe('Original notes');
});

test('user cannot update a scored prediction', function () {
    $user = testUser();
    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'type' => 'race',
        'season' => 2024,
        'status' => 'scored',
        'locked_at' => now(),
        'scored_at' => now(),
        'notes' => 'Original notes',
    ]);

    actingAs($user)
        ->patch(route('predictions.update', $prediction), ['notes' => 'Attempted change'])
        ->assertForbidden();

    $prediction->refresh();
    expect($prediction->notes)->toBe('Original notes');
});

test('user cannot delete a locked prediction', function () {
    $user = testUser();
    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'type' => 'race',
        'status' => 'locked',
        'locked_at' => now(),
    ]);
    $id = $prediction->id;

    actingAs($user)
        ->delete(route('predictions.destroy', $prediction))
        ->assertForbidden();

    expect(Prediction::find($id))->not->toBeNull();
});

test('user cannot delete a scored prediction', function () {
    $user = testUser();
    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'type' => 'race',
        'status' => 'scored',
        'locked_at' => now(),
        'scored_at' => now(),
    ]);
    $id = $prediction->id;

    actingAs($user)
        ->delete(route('predictions.destroy', $prediction))
        ->assertForbidden();

    expect(Prediction::find($id))->not->toBeNull();
});

test('user can edit a submitted prediction before deadline', function () {
    $user = testUser();
    // midseason has no deadline, so isEditable() always returns true for non-locked/scored
    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'type' => 'midseason',
        'season' => 2024,
        'status' => 'submitted',
        'notes' => 'Original notes',
    ]);

    $response = actingAs($user)
        ->get(route('predictions.edit', $prediction));

    $response->assertOk();
});

test('predict create redirects to edit when prediction already exists for race', function () {
    $user = testUser();
    $race = \App\Models\Races::factory()->create(['season' => 2025, 'round' => 1, 'status' => 'upcoming']);
    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'type' => 'race',
        'season' => 2025,
        'race_round' => 1,
        'race_id' => $race->id,
        'status' => 'submitted',
    ]);

    $response = actingAs($user)
        ->get(route('predict.create', ['race_id' => $race->id]));

    $response->assertRedirect(route('predictions.edit', $prediction));
});

test('mass-assigning score or status via create is rejected', function () {
    $user = testUser();
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
