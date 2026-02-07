<?php

declare(strict_types=1);

use App\Jobs\ScoreRacePredictionsJob;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\User;
use App\Services\F1ApiService;
use App\Services\ScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

use function Pest\Laravel\mock;

uses(RefreshDatabase::class);

test('job scores predictions when race is completed', function () {
    $race = Races::factory()->create([
        'season' => 2024,
        'round' => 1,
        'status' => 'completed',
        'race_name' => 'Monaco GP',
        'results' => [
            ['driver' => ['driverId' => 'max_verstappen'], 'status' => 'finished'],
            ['driver' => ['driverId' => 'lewis_hamilton'], 'status' => 'finished'],
        ],
    ]);
    $user = User::factory()->create();
    Prediction::factory()->create([
        'user_id' => $user->id,
        'race_id' => $race->id,
        'type' => 'race',
        'status' => 'submitted',
        'prediction_data' => [
            'driver_order' => ['max_verstappen', 'lewis_hamilton'],
            'fastest_lap' => 'max_verstappen',
        ],
    ]);

    $job = new ScoreRacePredictionsJob($race->id, false);
    $job->handle(app(F1ApiService::class), app(ScoringService::class));

    $prediction = Prediction::where('race_id', $race->id)->first();
    expect($prediction->status)->toBe('scored')
        ->and($prediction->score)->not->toBeNull();
});

test('job logs error when race not found', function () {
    Log::shouldReceive('error')
        ->once()
        ->with('Race not found for scoring job', ['race_id' => 99999]);

    $job = new ScoreRacePredictionsJob(99999, false);
    $job->handle(app(F1ApiService::class), app(ScoringService::class));
});

test('job skips scoring when race not completed and no force update', function () {
    $race = Races::factory()->create([
        'season' => 2024,
        'round' => 1,
        'status' => 'upcoming',
        'results' => null,
    ]);
    $user = User::factory()->create();
    Prediction::factory()->create([
        'user_id' => $user->id,
        'race_id' => $race->id,
        'type' => 'race',
        'status' => 'submitted',
    ]);

    mock(F1ApiService::class, function ($mock) {
        $mock->shouldReceive('getRaceResults')
            ->andReturn(['races' => ['results' => []]]);
    });

    Log::shouldReceive('info')->andReturn(null);
    Log::shouldReceive('warning')->andReturn(null);

    $job = new ScoreRacePredictionsJob($race->id, false);
    $job->handle(app(F1ApiService::class), app(ScoringService::class));

    $prediction = Prediction::where('race_id', $race->id)->first();
    expect($prediction->status)->toBe('submitted');
});

test('job updates race results from api when force update', function () {
    $race = Races::factory()->create([
        'season' => 2024,
        'round' => 1,
        'status' => 'upcoming',
        'results' => null,
    ]);

    $apiResults = [
        'races' => [
            'results' => [
                ['driver' => ['driverId' => 'max_verstappen'], 'status' => 'finished'],
            ],
        ],
    ];

    mock(F1ApiService::class, function ($mock) use ($apiResults) {
        $mock->shouldReceive('getRaceResults')
            ->with(2024, 1)
            ->andReturn($apiResults);
    });

    $job = new ScoreRacePredictionsJob($race->id, true);
    $job->handle(app(F1ApiService::class), app(ScoringService::class));

    $race->refresh();
    expect($race->status)->toBe('completed')
        ->and($race->results)->not->toBeNull();
});

test('job failed method logs error', function () {
    Log::shouldReceive('error')
        ->once()
        ->withArgs(function ($message, $context) {
            return str_contains($message, 'failed permanently')
                && ($context['race_id'] ?? 0) === 123
                && isset($context['error']);
        });

    $job = new ScoreRacePredictionsJob(123, false);
    $job->failed(new \RuntimeException('Test failure'));
});
