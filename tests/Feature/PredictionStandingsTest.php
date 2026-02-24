<?php

declare(strict_types=1);

use App\Models\Prediction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// F1-084: Prediction standings page shows real users (from factories), no demo users; filters change results.
// Does not hit real F1 API.

describe('prediction standings page', function () {
    test('returns 200 and shows real users with predictions in table', function () {
        /** @var \Tests\TestCase $this */
        $user = User::factory()->create(['name' => 'RealPredictor']);
        Prediction::factory()->scored(50, 75.0)->create([
            'user_id' => $user->id,
            'season' => 2024,
            'type' => 'race',
        ]);

        $response = $this->get('/2024/standings/predictions');

        $response->assertOk();
        $response->assertSee('Prediction Standings', false);
        $response->assertSee('2024', false);
        $response->assertSee('RealPredictor', false);
        $response->assertSee('Global Leaderboard', false);
        $response->assertDontSee('F1Expert', false);
        $response->assertDontSee('RacingFan2023', false);
        $response->assertDontSee('PredictorPro', false);
    });

    test('does not show demo or fake usernames when only real users have predictions', function () {
        /** @var \Tests\TestCase $this */
        $user = User::factory()->create(['name' => 'OnlyRealUser']);
        Prediction::factory()->scored(10)->create([
            'user_id' => $user->id,
            'season' => 2025,
            'type' => 'race',
        ]);

        $response = $this->get('/2025/standings/predictions');

        $response->assertOk();
        $response->assertSee('OnlyRealUser', false);
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

        $r2024 = $this->get('/2024/standings/predictions');
        $r2025 = $this->get('/2025/standings/predictions');

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

        $all = $this->get('/2024/standings/predictions');
        $all->assertOk();
        $all->assertSee('RaceOnlyUser', false);
        $all->assertSee('PreseasonOnlyUser', false);

        $race = $this->get('/2024/standings/predictions?type=race');
        $race->assertOk();
        $race->assertSee('RaceOnlyUser', false);
        $race->assertDontSee('PreseasonOnlyUser', false);

        $preseason = $this->get('/2024/standings/predictions?type=preseason');
        $preseason->assertOk();
        $preseason->assertSee('PreseasonOnlyUser', false);
        $preseason->assertDontSee('RaceOnlyUser', false);
    });

    test('sort filter changes leaderboard order', function () {
        /** @var \Tests\TestCase $this */
        $highTotal = User::factory()->create(['name' => 'HighTotalUser']);
        $highAvg = User::factory()->create(['name' => 'HighAvgUser']);
        foreach ([20, 20, 20, 20, 20] as $round => $score) {
            Prediction::factory()->scored($score, 50.0)->create([
                'user_id' => $highTotal->id,
                'season' => 2024,
                'type' => 'race',
                'race_round' => $round + 1,
            ]);
        }
        foreach ([30, 30] as $round => $score) {
            Prediction::factory()->scored($score, 60.0)->create([
                'user_id' => $highAvg->id,
                'season' => 2024,
                'type' => 'race',
                'race_round' => $round + 1,
            ]);
        }

        $byTotal = $this->get('/2024/standings/predictions?sortBy=total_score');
        $byTotal->assertOk();
        $body = $byTotal->getContent();
        $posHighTotal = strpos($body, 'HighTotalUser');
        $posHighAvg = strpos($body, 'HighAvgUser');
        expect($posHighTotal)->toBeLessThan($posHighAvg);

        $byAvg = $this->get('/2024/standings/predictions?sortBy=avg_score');
        $byAvg->assertOk();
        $bodyAvg = $byAvg->getContent();
        $posHighAvgFirst = strpos($bodyAvg, 'HighAvgUser');
        $posHighTotalSecond = strpos($bodyAvg, 'HighTotalUser');
        expect($posHighAvgFirst)->toBeLessThan($posHighTotalSecond);
    });
});
