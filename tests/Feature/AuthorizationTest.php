<?php

use App\Models\Circuits;
use App\Models\Countries;
use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\Standings;
use App\Models\Teams;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin users can perform all actions', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $race = Races::factory()->create();
    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'race_id' => $race->id,
        'type' => 'race',
    ]);
    $driver = Drivers::factory()->create();
    $team = Teams::factory()->create();
    $circuit = Circuits::factory()->create();
    $country = Countries::factory()->create();
    $standing = Standings::factory()->create();

    // Test user management
    expect($admin->can('viewAny', User::class))->toBeTrue();
    expect($admin->can('view', $user))->toBeTrue();
    expect($admin->can('update', $user))->toBeTrue();
    expect($admin->can('delete', $user))->toBeTrue();

    // Test prediction management
    expect($admin->can('viewAny', Prediction::class))->toBeTrue();
    expect($admin->can('view', $prediction))->toBeTrue();
    expect($admin->can('update', $prediction))->toBeTrue();
    expect($admin->can('delete', $prediction))->toBeTrue();
    expect($admin->can('score', $prediction))->toBeTrue();

    // Test race management
    expect($admin->can('viewAny', Races::class))->toBeTrue();
    expect($admin->can('view', $race))->toBeTrue();
    expect($admin->can('create', Races::class))->toBeTrue();
    expect($admin->can('update', $race))->toBeTrue();
    expect($admin->can('delete', $race))->toBeTrue();

    // Test driver management
    expect($admin->can('viewAny', Drivers::class))->toBeTrue();
    expect($admin->can('view', $driver))->toBeTrue();
    expect($admin->can('create', Drivers::class))->toBeTrue();
    expect($admin->can('update', $driver))->toBeTrue();
    expect($admin->can('delete', $driver))->toBeTrue();

    // Test team management
    expect($admin->can('viewAny', Teams::class))->toBeTrue();
    expect($admin->can('view', $team))->toBeTrue();
    expect($admin->can('create', Teams::class))->toBeTrue();
    expect($admin->can('update', $team))->toBeTrue();
    expect($admin->can('delete', $team))->toBeTrue();

    // Test circuit management
    expect($admin->can('viewAny', Circuits::class))->toBeTrue();
    expect($admin->can('view', $circuit))->toBeTrue();
    expect($admin->can('create', Circuits::class))->toBeTrue();
    expect($admin->can('update', $circuit))->toBeTrue();
    expect($admin->can('delete', $circuit))->toBeTrue();

    // Test country management
    expect($admin->can('viewAny', Countries::class))->toBeTrue();
    expect($admin->can('view', $country))->toBeTrue();
    expect($admin->can('create', Countries::class))->toBeTrue();
    expect($admin->can('update', $country))->toBeTrue();
    expect($admin->can('delete', $country))->toBeTrue();

    // Test standings management
    expect($admin->can('viewAny', Standings::class))->toBeTrue();
    expect($admin->can('view', $standing))->toBeTrue();
    expect($admin->can('create', Standings::class))->toBeTrue();
    expect($admin->can('update', $standing))->toBeTrue();
    expect($admin->can('delete', $standing))->toBeTrue();

    // Test custom gates
    expect($admin->can('view-admin-dashboard'))->toBeTrue();
    expect($admin->can('manage-users'))->toBeTrue();
    expect($admin->can('manage-predictions'))->toBeTrue();
});

test('regular users can view public data but cannot manage', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $race = Races::factory()->create();
    $prediction = Prediction::factory()->create([
        'user_id' => $otherUser->id,
        'race_id' => $race->id,
        'type' => 'race',
    ]);
    $driver = Drivers::factory()->create();
    $team = Teams::factory()->create();
    $circuit = Circuits::factory()->create();
    $country = Countries::factory()->create();
    $standing = Standings::factory()->create();

    // Test user management - users can only view/update their own profile
    expect($user->can('viewAny', User::class))->toBeFalse();
    expect($user->can('view', $user))->toBeTrue();
    expect($user->can('view', $otherUser))->toBeFalse();
    expect($user->can('update', $user))->toBeTrue();
    expect($user->can('update', $otherUser))->toBeFalse();
    expect($user->can('delete', $user))->toBeFalse();
    expect($user->can('delete', $otherUser))->toBeFalse();

    // Test prediction management - users can only manage their own predictions
    expect($user->can('viewAny', Prediction::class))->toBeTrue();
    expect($user->can('view', $prediction))->toBeFalse(); // Not their prediction
    expect($user->can('update', $prediction))->toBeFalse();
    expect($user->can('delete', $prediction))->toBeFalse();
    expect($user->can('score', $prediction))->toBeFalse();

    // Test public data viewing
    expect($user->can('viewAny', Races::class))->toBeTrue();
    expect($user->can('view', $race))->toBeTrue();
    expect($user->can('viewAny', Drivers::class))->toBeTrue();
    expect($user->can('view', $driver))->toBeTrue();
    expect($user->can('viewAny', Teams::class))->toBeTrue();
    expect($user->can('view', $team))->toBeTrue();
    expect($user->can('viewAny', Circuits::class))->toBeTrue();
    expect($user->can('view', $circuit))->toBeTrue();
    expect($user->can('viewAny', Countries::class))->toBeTrue();
    expect($user->can('view', $country))->toBeTrue();
    expect($user->can('viewAny', Standings::class))->toBeTrue();
    expect($user->can('view', $standing))->toBeTrue();

    // Test management restrictions
    expect($user->can('create', Races::class))->toBeFalse();
    expect($user->can('update', $race))->toBeFalse();
    expect($user->can('delete', $race))->toBeFalse();
    expect($user->can('create', Drivers::class))->toBeFalse();
    expect($user->can('update', $driver))->toBeFalse();
    expect($user->can('delete', $driver))->toBeFalse();
    expect($user->can('create', Teams::class))->toBeFalse();
    expect($user->can('update', $team))->toBeFalse();
    expect($user->can('delete', $team))->toBeFalse();
    expect($user->can('create', Circuits::class))->toBeFalse();
    expect($user->can('update', $circuit))->toBeFalse();
    expect($user->can('delete', $circuit))->toBeFalse();
    expect($user->can('create', Countries::class))->toBeFalse();
    expect($user->can('update', $country))->toBeFalse();
    expect($user->can('delete', $country))->toBeFalse();
    expect($user->can('create', Standings::class))->toBeFalse();
    expect($user->can('update', $standing))->toBeFalse();
    expect($user->can('delete', $standing))->toBeFalse();

    // Test custom gates
    expect($user->can('view-admin-dashboard'))->toBeFalse();
    expect($user->can('manage-users'))->toBeFalse();
    expect($user->can('manage-predictions'))->toBeFalse();
    expect($user->can('view-predictions'))->toBeTrue();
    expect($user->can('create-predictions'))->toBeTrue();
});

test('users can manage their own predictions', function () {
    $user = User::factory()->create();
    $race = Races::factory()->create();
    $ownPrediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'race_id' => $race->id,
        'type' => 'race',
        'status' => 'draft',
    ]);
    $submittedPrediction = Prediction::factory()->submitted()->create([
        'user_id' => $user->id,
        'race_id' => $race->id,
        'type' => 'race',
    ]);

    // Users can view their own predictions
    expect($user->can('view', $ownPrediction))->toBeTrue();
    expect($user->can('view', $submittedPrediction))->toBeTrue();

    // Users can update any prediction that is still editable (not locked/scored, before deadline)
    expect($user->can('update', $ownPrediction))->toBeTrue();
    expect($user->can('update', $submittedPrediction))->toBeTrue();

    // Users can delete any prediction that is still editable (not locked/scored, before deadline)
    expect($user->can('delete', $ownPrediction))->toBeTrue();
    expect($user->can('delete', $submittedPrediction))->toBeTrue();

    // Users can submit draft predictions
    expect($user->can('submit', $ownPrediction))->toBeTrue();
    expect($user->can('submit', $submittedPrediction))->toBeFalse();

    // Users cannot score predictions
    expect($user->can('score', $ownPrediction))->toBeFalse();
    expect($user->can('score', $submittedPrediction))->toBeFalse();
});

test('system users can score predictions', function () {
    $systemUser = User::factory()->create(['email' => 'smartbot@example.com']);
    $race = Races::factory()->create();
    $prediction = Prediction::factory()->create([
        'race_id' => $race->id,
        'type' => 'race',
    ]);

    expect($systemUser->can('score', $prediction))->toBeTrue();
});

test('moderators can manage predictions', function () {
    $moderator = User::factory()->admin()->create();
    $race = Races::factory()->create();
    $prediction = Prediction::factory()->create([
        'race_id' => $race->id,
        'type' => 'race',
    ]);

    expect($moderator->can('manage-predictions'))->toBeTrue();
    expect($moderator->can('view', $prediction))->toBeTrue();
    expect($moderator->can('update', $prediction))->toBeTrue();
    expect($moderator->can('delete', $prediction))->toBeTrue();
});

test('role checking methods work correctly', function () {
    $admin = User::factory()->admin()->create();
    $bot = User::factory()->create(['email' => 'smartbot@example.com']);
    $user = User::factory()->create();

    // Test hasRole method
    expect($admin->hasRole('admin'))->toBeTrue();
    expect($admin->hasRole('moderator'))->toBeTrue(); // moderator maps to admin
    expect($bot->hasRole('system'))->toBeTrue();
    expect($user->hasRole('admin'))->toBeFalse();

    // Test hasAnyRole method
    expect($admin->hasAnyRole(['admin', 'moderator']))->toBeTrue();
    expect($bot->hasAnyRole(['system']))->toBeTrue();
    expect($user->hasAnyRole(['admin', 'moderator']))->toBeFalse();

    // Test hasAllRoles method
    expect($admin->hasAllRoles(['admin']))->toBeTrue();
    expect($admin->hasAllRoles(['admin', 'moderator']))->toBeTrue(); // both map to is_admin
    expect($user->hasAllRoles(['admin']))->toBeFalse();
});
