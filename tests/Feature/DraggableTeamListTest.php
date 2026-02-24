<?php

declare(strict_types=1);

use App\Livewire\Predictions\DraggableTeamList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('draggable team list component can be rendered', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $teams = [
        ['id' => 1, 'team_name' => 'Mercedes', 'nationality' => 'German'],
        ['id' => 2, 'team_name' => 'Red Bull Racing', 'nationality' => 'Austrian'],
    ];

    Livewire::test(DraggableTeamList::class, [
        'teams' => $teams,
        'title' => '2024 Constructor Championship',
    ])
        ->assertSee('2024 Constructor Championship')
        ->assertSee('Constructor order (drag to reorder)')
        ->assertSee('Drag constructors to reorder your predictions');
});

test('draggable team list initializes with correct team order', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $teams = [
        ['id' => 1, 'team_name' => 'Mercedes', 'nationality' => 'German'],
        ['id' => 2, 'team_name' => 'Red Bull', 'nationality' => 'Austrian'],
        ['id' => 3, 'team_name' => 'Ferrari', 'nationality' => 'Italian'],
    ];

    $component = Livewire::test(DraggableTeamList::class, [
        'teams' => $teams,
    ]);

    expect($component->get('teamOrder'))->toBe([1, 2, 3]);
});

test('draggable team list can update team order', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $teams = [
        ['id' => 1, 'team_name' => 'Mercedes', 'nationality' => 'German'],
        ['id' => 2, 'team_name' => 'Red Bull', 'nationality' => 'Austrian'],
        ['id' => 3, 'team_name' => 'Ferrari', 'nationality' => 'Italian'],
    ];

    $component = Livewire::test(DraggableTeamList::class, ['teams' => $teams]);

    $newOrder = [3, 1, 2];
    $component->call('updateTeamOrder', $newOrder);

    expect($component->get('teamOrder'))->toBe($newOrder);
});

test('draggable team list returns correct prediction data', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $teams = [
        ['id' => 1, 'team_name' => 'Mercedes', 'nationality' => 'German'],
        ['id' => 2, 'team_name' => 'Red Bull', 'nationality' => 'Austrian'],
    ];

    $component = Livewire::test(DraggableTeamList::class, ['teams' => $teams]);
    $component->set('teamOrder', [2, 1]);

    $predictionData = $component->get('teamOrder');

    expect($predictionData)->toBe([2, 1]);
});

test('draggable team list handles empty teams gracefully', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $component = Livewire::test(DraggableTeamList::class, [
        'teams' => [],
    ]);

    expect($component->get('teamOrder'))->toBe([]);
});
