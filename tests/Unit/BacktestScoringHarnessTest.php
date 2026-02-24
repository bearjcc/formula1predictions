<?php

declare(strict_types=1);

use App\Models\Prediction;
use App\Models\Races;
use Tests\Support\BacktestScoringHarness;

describe('BacktestScoringHarness position tables', function () {
    test('productionPositionScore matches documented table', function () {
        for ($diff = 0; $diff <= 20; $diff++) {
            $expected = match ($diff) {
                0 => 25,
                1 => 18,
                2 => 15,
                3 => 12,
                4 => 10,
                5 => 8,
                6 => 6,
                7 => 4,
                8 => 2,
                9 => 1,
                10 => 0,
                11 => -1,
                12 => -2,
                13 => -4,
                14 => -6,
                15 => -8,
                16 => -10,
                17 => -12,
                18 => -15,
                19 => -18,
                default => -25,
            };

            expect(BacktestScoringHarness::productionPositionScore($diff))->toBe($expected);
        }
    });

    test('linearPositionScore decays linearly without negatives', function () {
        expect(BacktestScoringHarness::linearPositionScore(0))->toBe(25);
        expect(BacktestScoringHarness::linearPositionScore(1))->toBe(23);
        expect(BacktestScoringHarness::linearPositionScore(10))->toBe(5);
        expect(BacktestScoringHarness::linearPositionScore(13))->toBe(0);
        expect(BacktestScoringHarness::linearPositionScore(20))->toBe(0);
    });

    test('flatterPositionScore matches documented table', function () {
        expect(BacktestScoringHarness::flatterPositionScore(0))->toBe(20);
        expect(BacktestScoringHarness::flatterPositionScore(1))->toBe(16);
        expect(BacktestScoringHarness::flatterPositionScore(2))->toBe(12);
        expect(BacktestScoringHarness::flatterPositionScore(3))->toBe(9);
        expect(BacktestScoringHarness::flatterPositionScore(4))->toBe(6);
        expect(BacktestScoringHarness::flatterPositionScore(5))->toBe(4);

        foreach ([6, 7, 8, 9] as $diff) {
            expect(BacktestScoringHarness::flatterPositionScore($diff))->toBe(2);
        }

        expect(BacktestScoringHarness::flatterPositionScore(10))->toBe(0);
    });
});

describe('BacktestScoringHarness computeScore', function () {
    test('perfect race prediction includes position, fastest lap, and perfect bonus', function () {
        $prediction = new Prediction([
            'type' => 'race',
            'season' => 2024,
            'prediction_data' => [
                'driver_order' => ['hamilton', 'verstappen', 'norris'],
                'fastest_lap' => 'norris',
            ],
        ]);

        $race = new Races([
            'season' => 2024,
            'results' => [
                [
                    'driver' => ['driverId' => 'hamilton'],
                    'status' => 'FINISHED',
                ],
                [
                    'driver' => ['driverId' => 'verstappen'],
                    'status' => 'FINISHED',
                ],
                [
                    'driver' => ['driverId' => 'norris'],
                    'status' => 'FINISHED',
                    'fastestLap' => true,
                ],
            ],
        ]);

        $harness = new BacktestScoringHarness();

        $score = $harness->computeScore($prediction, $race);

        // 3 exact positions (3 * 25) + 10 fastest lap + 50 perfect bonus.
        expect($score)->toBe(25 * 3 + 10 + 50);
    });

    test('non-race prediction type scores zero', function () {
        $prediction = new Prediction([
            'type' => 'preseason',
            'season' => 2024,
            'prediction_data' => [],
        ]);

        $race = new Races([
            'season' => 2024,
            'results' => [],
        ]);

        $harness = new BacktestScoringHarness();

        expect($harness->computeScore($prediction, $race))->toBe(0);
    });

    test('empty race results score zero', function () {
        $prediction = new Prediction([
            'type' => 'race',
            'season' => 2024,
            'prediction_data' => [
                'driver_order' => ['hamilton'],
                'fastest_lap' => 'hamilton',
            ],
        ]);

        $race = new Races([
            'season' => 2024,
            'results' => [],
        ]);

        $harness = new BacktestScoringHarness();

        expect($harness->computeScore($prediction, $race))->toBe(0);
    });
});

describe('BacktestScoringHarness compareVariants', function () {
    test('returns consistent keys and rank changes for alternative variant', function () {
        $race = new Races([
            'season' => 2024,
            'results' => [
                [
                    'driver' => ['driverId' => 'hamilton'],
                    'status' => 'FINISHED',
                ],
                [
                    'driver' => ['driverId' => 'verstappen'],
                    'status' => 'FINISHED',
                ],
            ],
        ]);

        $p1 = new Prediction([
            'type' => 'race',
            'season' => 2024,
            'prediction_data' => [
                'driver_order' => ['hamilton', 'verstappen'],
            ],
        ]);
        $p1->id = 1;
        $p1->setRelation('race', $race);

        $p2 = new Prediction([
            'type' => 'race',
            'season' => 2024,
            'prediction_data' => [
                'driver_order' => ['verstappen', 'hamilton'],
            ],
        ]);
        $p2->id = 2;
        $p2->setRelation('race', $race);

        $harness = new BacktestScoringHarness();

        $result = $harness->compareVariants([$p1, $p2], 'linear');

        expect($result['production_scores'])->toHaveKeys([1, 2]);
        expect($result['alternative_scores'])->toHaveKeys([1, 2]);
        expect($result['score_deltas'])->toHaveKeys([1, 2]);
        expect($result['rank_changes'])->toHaveKeys([1, 2]);

        // Perfect prediction should not rank worse under the alternative scoring.
        expect($result['rank_changes'][1])->toBeLessThanOrEqual(0);
    });
}
);

