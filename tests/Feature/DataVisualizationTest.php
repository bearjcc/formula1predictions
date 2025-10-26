<?php

use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\Standings;
use App\Models\Teams;
use App\Models\User;
use App\Services\ChartDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('chart data service can generate driver standings progression', function () {
    $chartService = new ChartDataService();
    
    // Create test data
    $race = Races::factory()->create([
        'season' => 2024,
        'round' => 1,
        'race_name' => 'Test Grand Prix',
        'results' => [
            ['driver_id' => 1, 'position' => 0],
            ['driver_id' => 2, 'position' => 1],
        ]
    ]);
    
    $driver1 = Drivers::factory()->create(['id' => 1, 'name' => 'Lewis', 'surname' => 'Hamilton']);
    $driver2 = Drivers::factory()->create(['id' => 2, 'name' => 'Max', 'surname' => 'Verstappen']);
    
    $data = $chartService->getDriverStandingsProgression(2024);
    
    expect($data)->toHaveCount(1)
        ->and($data[0]['race'])->toBe('Test Grand Prix')
        ->and($data[0]['Lewis Hamilton'])->toBe(1)
        ->and($data[0]['Max Verstappen'])->toBe(2);
});

test('chart data service can generate team standings progression', function () {
    $chartService = new ChartDataService();
    
    // Create test data
    $team1 = Teams::factory()->create(['team_name' => 'Mercedes']);
    $team2 = Teams::factory()->create(['team_name' => 'Red Bull']);
    
    $driver1 = Drivers::factory()->create(['team_id' => $team1->id]);
    $driver2 = Drivers::factory()->create(['team_id' => $team2->id]);
    
    $race = Races::factory()->create([
        'season' => 2024,
        'round' => 1,
        'race_name' => 'Test Grand Prix',
        'results' => [
            ['driver_id' => $driver1->id, 'position' => 0],
            ['driver_id' => $driver2->id, 'position' => 1],
        ]
    ]);
    
    $data = $chartService->getTeamStandingsProgression(2024);
    
    expect($data)->toHaveCount(1)
        ->and($data[0]['race'])->toBe('Test Grand Prix')
        ->and($data[0]['Mercedes'])->toBe(1)
        ->and($data[0]['Red Bull'])->toBe(2);
});

test('chart data service can generate driver points progression', function () {
    $chartService = new ChartDataService();
    
    // Create test data
    $race = Races::factory()->create([
        'season' => 2024,
        'round' => 1,
        'race_name' => 'Test Grand Prix',
        'results' => [
            ['driver_id' => 1, 'position' => 0], // 25 points
            ['driver_id' => 2, 'position' => 1], // 18 points
        ]
    ]);
    
    $driver1 = Drivers::factory()->create(['id' => 1, 'name' => 'Lewis', 'surname' => 'Hamilton']);
    $driver2 = Drivers::factory()->create(['id' => 2, 'name' => 'Max', 'surname' => 'Verstappen']);
    
    $data = $chartService->getDriverPointsProgression(2024);
    
    expect($data)->toHaveCount(1)
        ->and($data[0]['race'])->toBe('Test Grand Prix')
        ->and($data[0]['Lewis Hamilton'])->toBe(25)
        ->and($data[0]['Max Verstappen'])->toBe(18);
});

test('chart data service can generate prediction accuracy by type', function () {
    $chartService = new ChartDataService();
    
    // Create test data
    $user = User::factory()->create();
    
    Prediction::factory()->create([
        'user_id' => $user->id,
        'season' => 2024,
        'type' => 'race',
        'accuracy' => 80.0,
        'score' => 85,
    ]);
    
    Prediction::factory()->create([
        'user_id' => $user->id,
        'season' => 2024,
        'type' => 'preseason',
        'accuracy' => 70.0,
        'score' => 75,
    ]);
    
    $data = $chartService->getPredictionAccuracyByType(2024);
    
    expect($data)->toHaveCount(2)
        ->and($data[0]['type'])->toBe('Race')
        ->and($data[0]['avg_accuracy'])->toBe(80.0)
        ->and($data[1]['type'])->toBe('Preseason')
        ->and($data[1]['avg_accuracy'])->toBe(70.0);
});

test('chart data service can generate driver consistency analysis', function () {
    $chartService = new ChartDataService();
    
    // Create test data
    $driver = Drivers::factory()->create(['name' => 'Lewis', 'surname' => 'Hamilton']);
    
    $race1 = Races::factory()->create([
        'season' => 2024,
        'round' => 1,
        'results' => [
            ['driver_id' => $driver->id, 'position' => 1], // 2nd place for test driver
        ],
    ]);

    $race2 = Races::factory()->create([
        'season' => 2024,
        'round' => 2,
        'results' => [
            ['driver_id' => $driver->id, 'position' => 2], // 3rd place for test driver
        ],
    ]);
    
    $data = $chartService->getDriverConsistencyAnalysis(2024);
    
    expect($data)->toHaveCount(1)
        ->and($data[0]['driver'])->toBe('Lewis Hamilton')
        ->and($data[0]['avg_position'])->toBe(2.5) // (2 + 3) / 2 where positions are 2nd and 3rd
        ->and($data[0]['races'])->toBe(2)
        ->and($data[0]['consistency_score'])->toBeGreaterThan(0);
});

test('standings chart component renders correctly', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $response = $this->get('/analytics');
    $response->assertStatus(200);
    $response->assertSeeLivewire('charts.standings-chart');
});

test('points progression chart component renders correctly', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $response = $this->get('/analytics');
    $response->assertStatus(200);
    $response->assertSeeLivewire('charts.points-progression-chart');
});

test('driver consistency chart component renders correctly', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $response = $this->get('/analytics');
    $response->assertStatus(200);
    $response->assertSeeLivewire('charts.driver-consistency-chart');
});

test('prediction accuracy chart component renders correctly', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $response = $this->get('/analytics');
    $response->assertStatus(200);
    $response->assertSeeLivewire('charts.prediction-accuracy-chart');
});

test('chart components can change chart type', function () {
    Livewire::test('charts.standings-chart')
        ->set('chartType', 'team')
        ->assertSet('chartType', 'team');
});

test('chart components can change season', function () {
    Livewire::test('charts.standings-chart')
        ->set('season', 2023)
        ->assertSet('season', 2023);
});

test('analytics page shows enhanced statistics', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $response = $this->get('/analytics');
    $response->assertStatus(200);
    
    // Check for enhanced sections
    $response->assertSee('Prediction Type Analysis');
    $response->assertSee('Race Result Distribution');
    $response->assertSee('Driver Consistency Analysis');
    $response->assertSee('Points Progression');
});
