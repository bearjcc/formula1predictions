<?php

/**
 * Pure unit tests for scoring table math.
 *
 * These tests cover the lookup/math functions on RaceScoringService and
 * ChampionshipScoringService. They run without RefreshDatabase and without
 * any factory calls.
 *
 * Canonical scoring rules are documented in README.md § Scoring.
 */

declare(strict_types=1);

use App\Services\Scoring\ChampionshipScoringService;
use App\Services\Scoring\RaceScoringService;

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/**
 * Call a private method on RaceScoringService via reflection.
 */
function callRacePrivate(string $method, mixed ...$args): mixed
{
    $service = app(RaceScoringService::class);
    $closure = function (...$innerArgs) use ($method) {
        return $this->{$method}(...$innerArgs);
    };

    return $closure->bindTo($service, $service)(...$args);
}

/**
 * Call a private method on ChampionshipScoringService via reflection.
 */
function callChampionshipPrivate(string $method, mixed ...$args): mixed
{
    $service = app(ChampionshipScoringService::class);
    $closure = function (...$innerArgs) use ($method) {
        return $this->{$method}(...$innerArgs);
    };

    return $closure->bindTo($service, $service)(...$args);
}

// ---------------------------------------------------------------------------
// getPositionScore — race position diff table
// ---------------------------------------------------------------------------

describe('getPositionScore (race)', function () {
    test('exact match gives 25 points', function () {
        expect(callRacePrivate('getPositionScore', 0, 2024))->toBe(25);
    });

    test('1 off gives 18 points', function () {
        expect(callRacePrivate('getPositionScore', 1, 2024))->toBe(18);
    });

    test('2 off gives 15 points', function () {
        expect(callRacePrivate('getPositionScore', 2, 2024))->toBe(15);
    });

    test('3 off gives 12 points', function () {
        expect(callRacePrivate('getPositionScore', 3, 2024))->toBe(12);
    });

    test('4 off gives 10 points', function () {
        expect(callRacePrivate('getPositionScore', 4, 2024))->toBe(10);
    });

    test('5 off gives 8 points', function () {
        expect(callRacePrivate('getPositionScore', 5, 2024))->toBe(8);
    });

    test('6 off gives 6 points', function () {
        expect(callRacePrivate('getPositionScore', 6, 2024))->toBe(6);
    });

    test('7 off gives 4 points', function () {
        expect(callRacePrivate('getPositionScore', 7, 2024))->toBe(4);
    });

    test('8 off gives 2 points', function () {
        expect(callRacePrivate('getPositionScore', 8, 2024))->toBe(2);
    });

    test('9 off gives 1 point', function () {
        expect(callRacePrivate('getPositionScore', 9, 2024))->toBe(1);
    });

    test('10 off gives 0 points', function () {
        expect(callRacePrivate('getPositionScore', 10, 2024))->toBe(0);
    });

    test('11 off gives -1 point', function () {
        expect(callRacePrivate('getPositionScore', 11, 2024))->toBe(-1);
    });

    test('15 off gives -8 points', function () {
        expect(callRacePrivate('getPositionScore', 15, 2024))->toBe(-8);
    });

    test('19 off gives -18 points', function () {
        expect(callRacePrivate('getPositionScore', 19, 2024))->toBe(-18);
    });

    test('20+ off (max diff) gives -25 points', function () {
        expect(callRacePrivate('getPositionScore', 20, 2024))->toBe(-25);
        expect(callRacePrivate('getPositionScore', 50, 2024))->toBe(-25);
    });

    test('score decreases monotonically as diff increases', function () {
        $prev = PHP_INT_MAX;
        for ($i = 0; $i <= 20; $i++) {
            $score = callRacePrivate('getPositionScore', $i, 2024);
            expect($score)->toBeLessThanOrEqual($prev);
            $prev = $score;
        }
    });
});

// ---------------------------------------------------------------------------
// getSprintPositionScore — sprint position diff table
// ---------------------------------------------------------------------------

describe('getSprintPositionScore (sprint)', function () {
    test('exact match gives 8 points', function () {
        expect(callRacePrivate('getSprintPositionScore', 0, 2024))->toBe(8);
    });

    test('1 off gives 7 points', function () {
        expect(callRacePrivate('getSprintPositionScore', 1, 2024))->toBe(7);
    });

    test('2 off gives 6 points', function () {
        expect(callRacePrivate('getSprintPositionScore', 2, 2024))->toBe(6);
    });

    test('7 off gives 1 point', function () {
        expect(callRacePrivate('getSprintPositionScore', 7, 2024))->toBe(1);
    });

    test('8+ off gives 0 points (no negatives in sprint)', function () {
        expect(callRacePrivate('getSprintPositionScore', 8, 2024))->toBe(0);
        expect(callRacePrivate('getSprintPositionScore', 20, 2024))->toBe(0);
    });

    test('sprint scores are never negative', function () {
        for ($i = 0; $i <= 25; $i++) {
            expect(callRacePrivate('getSprintPositionScore', $i, 2024))->toBeGreaterThanOrEqual(0);
        }
    });
});

// ---------------------------------------------------------------------------
// getPreseasonConstructorPositionScore — preseason constructor diff table
// ---------------------------------------------------------------------------

describe('getPreseasonConstructorPositionScore', function () {
    test('exact match gives 10 points', function () {
        expect(callChampionshipPrivate('getPreseasonConstructorPositionScore', 0))->toBe(10);
    });

    test('1 off gives 8 points', function () {
        expect(callChampionshipPrivate('getPreseasonConstructorPositionScore', 1))->toBe(8);
    });

    test('4 off gives 2 points', function () {
        expect(callChampionshipPrivate('getPreseasonConstructorPositionScore', 4))->toBe(2);
    });

    test('5 off gives 0 points', function () {
        expect(callChampionshipPrivate('getPreseasonConstructorPositionScore', 5))->toBe(0);
    });

    test('6 off gives -2 points', function () {
        expect(callChampionshipPrivate('getPreseasonConstructorPositionScore', 6))->toBe(-2);
    });

    test('10+ off gives -10 points (floor)', function () {
        expect(callChampionshipPrivate('getPreseasonConstructorPositionScore', 10))->toBe(-10);
        expect(callChampionshipPrivate('getPreseasonConstructorPositionScore', 20))->toBe(-10);
    });
});

// ---------------------------------------------------------------------------
// scoreCountPrediction — count-based predictions (e.g. DNF count)
// ---------------------------------------------------------------------------

describe('scoreCountPrediction', function () {
    test('exact match gives 15 points', function () {
        expect(callChampionshipPrivate('scoreCountPrediction', 3, 3))->toBe(15);
    });

    test('1 off gives 10 points', function () {
        expect(callChampionshipPrivate('scoreCountPrediction', 2, 3))->toBe(10);
        expect(callChampionshipPrivate('scoreCountPrediction', 4, 3))->toBe(10);
    });

    test('2 off gives 5 points', function () {
        expect(callChampionshipPrivate('scoreCountPrediction', 1, 3))->toBe(5);
        expect(callChampionshipPrivate('scoreCountPrediction', 5, 3))->toBe(5);
    });

    test('3+ off gives 0 points', function () {
        expect(callChampionshipPrivate('scoreCountPrediction', 0, 3))->toBe(0);
        expect(callChampionshipPrivate('scoreCountPrediction', 6, 3))->toBe(0);
    });

    test('null predicted gives 0', function () {
        expect(callChampionshipPrivate('scoreCountPrediction', null, 3))->toBe(0);
    });

    test('null actual gives 0', function () {
        expect(callChampionshipPrivate('scoreCountPrediction', 3, null))->toBe(0);
    });

    test('both null gives 0', function () {
        expect(callChampionshipPrivate('scoreCountPrediction', null, null))->toBe(0);
    });
});
