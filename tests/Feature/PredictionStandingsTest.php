<?php

declare(strict_types=1);

use App\Livewire\GlobalLeaderboard;
use App\Models\Prediction;
use App\Models\Races;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

// F1-084: prediction standings now resolve to the canonical leaderboard page.
describe('prediction standings page', function () {
    test('redirects to the canonical leaderboard for the selected season', function () {
        /** @var \Tests\TestCase $this */
        $response = $this->get('/2024/standings/predictions');

        $response->assertRedirect(route('leaderboard.index', ['season' => 2024]));
    });

    test('redirect preserves leaderboard filters in the query string', function () {
        /** @var \Tests\TestCase $this */
        $response = $this->get('/2025/standings/predictions?type=race&sortBy=avg_score&page=2');

        $response->assertRedirect(route('leaderboard.index', [
            'season' => 2025,
            'type' => 'race',
            'sortBy' => 'avg_score',
            'page' => 2,
        ]));
    });

    test('canonical leaderboard shows real users with predictions in table', function () {
        /** @var \Tests\TestCase $this */
        $user = User::factory()->create(['name' => 'RealPredictor']);
        Prediction::factory()->scored(50)->create([
            'user_id' => $user->id,
            'season' => 2024,
            'type' => 'race',
        ]);

        $response = $this->get(route('leaderboard.index', ['season' => 2024]));

        $response->assertOk();
        $response->assertSee('Prediction Leaderboard', false);
        $response->assertSee('2024', false);
        $response->assertSee('RealPredictor', false);
        $response->assertDontSee('F1Expert', false);
        $response->assertDontSee('RacingFan2023', false);
        $response->assertDontSee('PredictorPro', false);
    });

    test('season filter shows only users with predictions for that season', function () {
        /** @var \Tests\TestCase $this */
        $user2024 = User::factory()->create(['name' => 'User2024']);
        $user2025 = User::factory()->create(['name' => 'User2025']);
        Prediction::factory()->scored(30)->create([
            'user_id' => $user2024->id,
            'season' => 2024,
            'type' => 'race',
        ]);
        Prediction::factory()->scored(30)->create([
            'user_id' => $user2025->id,
            'season' => 2025,
            'type' => 'race',
        ]);

        $r2024 = $this->get(route('leaderboard.index', ['season' => 2024]));
        $r2025 = $this->get(route('leaderboard.index', ['season' => 2025]));

        $r2024->assertOk();
        $r2024->assertSee('User2024', false);
        $r2024->assertDontSee('User2025', false);

        $r2025->assertOk();
        $r2025->assertSee('User2025', false);
        $r2025->assertDontSee('User2024', false);
    });

    test('type filter shows only matching prediction types', function () {
        /** @var \Tests\TestCase $this */
        $raceUser = User::factory()->create(['name' => 'RaceOnlyUser']);
        $preseasonUser = User::factory()->create(['name' => 'PreseasonOnlyUser']);
        Prediction::factory()->scored(20)->create([
            'user_id' => $raceUser->id,
            'season' => 2024,
            'type' => 'race',
        ]);
        Prediction::factory()->scored(20)->create([
            'user_id' => $preseasonUser->id,
            'season' => 2024,
            'type' => 'preseason',
        ]);

        $all = $this->get(route('leaderboard.index', ['season' => 2024]));
        $all->assertOk();
        $all->assertSee('RaceOnlyUser', false);
        $all->assertSee('PreseasonOnlyUser', false);

        $race = $this->get(route('leaderboard.index', ['season' => 2024, 'type' => 'race']));
        $race->assertOk();
        $race->assertSee('RaceOnlyUser', false);
        $race->assertDontSee('PreseasonOnlyUser', false);

        $preseason = $this->get(route('leaderboard.index', ['season' => 2024, 'type' => 'preseason']));
        $preseason->assertOk();
        $preseason->assertSee('PreseasonOnlyUser', false);
        $preseason->assertDontSee('RaceOnlyUser', false);
    });

    test('sort filter changes leaderboard order', function () {
        /** @var \Tests\TestCase $this */
        $highTotal = User::factory()->create(['name' => 'HighTotalUser']);
        $highAvg = User::factory()->create(['name' => 'HighAvgUser']);
        foreach ([20, 20, 20, 20, 20] as $round => $score) {
            Prediction::factory()->scored($score)->create([
                'user_id' => $highTotal->id,
                'season' => 2024,
                'type' => 'race',
                'race_round' => $round + 1,
            ]);
        }
        foreach ([30, 30] as $round => $score) {
            Prediction::factory()->scored($score)->create([
                'user_id' => $highAvg->id,
                'season' => 2024,
                'type' => 'race',
                'race_round' => $round + 1,
            ]);
        }

        $byTotal = $this->get(route('leaderboard.index', ['season' => 2024, 'sortBy' => 'total_score']));
        $byTotal->assertOk();
        $body = $byTotal->getContent();
        $posHighTotal = strpos($body, 'HighTotalUser');
        $posHighAvg = strpos($body, 'HighAvgUser');
        expect($posHighTotal)->toBeLessThan($posHighAvg);

        $byAvg = $this->get(route('leaderboard.index', ['season' => 2024, 'sortBy' => 'avg_score']));
        $byAvg->assertOk();
        $bodyAvg = $byAvg->getContent();
        $posHighAvgFirst = strpos($bodyAvg, 'HighAvgUser');
        $posHighTotalSecond = strpos($bodyAvg, 'HighTotalUser');
        expect($posHighAvgFirst)->toBeLessThan($posHighTotalSecond);
    });

    test('scored predictions with points are not counted as perfect unless they meet perfect bonus rules', function () {
        $race = Races::factory()->create([
            'season' => 2026,
            'round' => 1,
            'status' => 'completed',
            'results' => [
                ['driver' => ['driverId' => 'max_verstappen'], 'status' => 'FINISHED', 'fastestLap' => true],
                ['driver' => ['driverId' => 'lando_norris'], 'status' => 'FINISHED'],
                ['driver' => ['driverId' => 'charles_leclerc'], 'status' => 'FINISHED'],
            ],
        ]);

        foreach (range(1, 4) as $index) {
            $user = User::factory()->create(['name' => "Predictor {$index}"]);

            Prediction::factory()->create([
                'user_id' => $user->id,
                'race_id' => $race->id,
                'season' => 2026,
                'race_round' => 1,
                'type' => 'race',
                'status' => 'scored',
                'score' => 25,
                'scored_at' => now(),
                'prediction_data' => [
                    'driver_order' => ['max_verstappen', 'charles_leclerc', 'lando_norris'],
                    'fastest_lap' => 'lando_norris',
                    'dnf_predictions' => [],
                ],
            ]);
        }

        $component = Livewire::test(GlobalLeaderboard::class, ['season' => 2026]);

        expect($component->get('proStats')['total_predictions'])->toBe(4)
            ->and($component->get('proStats')['perfect_predictions'])->toBe(0);

        foreach ($component->get('leaderboard') as $row) {
            expect($row['perfect_predictions'])->toBe(0);
        }
    });
});
