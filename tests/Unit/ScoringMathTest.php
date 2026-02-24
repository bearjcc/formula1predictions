<?php

/**
 * Pure unit tests for ScoringService scoring tables.
 *
 * These tests cover the lookup/math functions that have zero database or
 * application-bootstrap dependencies. They run without RefreshDatabase and
 * without any factory calls, so they are extremely fast.
 *
 * Canonical scoring rules are documented in README.md § Scoring.
 */

declare(strict_types=1);

use App\Services\ScoringService;

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/**
 * Call a private method on ScoringService via reflection.
 */
function callPrivate(string $method, mixed ...$args): mixed
{
    $service = new ScoringService(
        app(\App\Services\F1ApiService::class)
    );

    $closure = function (...$innerArgs) use ($method) {
        return $this->{$method}(...$innerArgs);
    };

    $bound = $closure->bindTo($service, $service);

    return $bound(...$args);
}

// ---------------------------------------------------------------------------
// getPositionScore — race position diff table
// ---------------------------------------------------------------------------

describe('getPositionScore (race)', function () {
    test('exact match gives 25 points', function () {
        expect(callPrivate('getPositionScore', 0, 2024))->toBe(25);
    });

    test('1 off gives 18 points', function () {
        expect(callPrivate('getPositionScore', 1, 2024))->toBe(18);
    });

    test('2 off gives 15 points', function () {
        expect(callPrivate('getPositionScore', 2, 2024))->toBe(15);
    });

    test('3 off gives 12 points', function () {
        expect(callPrivate('getPositionScore', 3, 2024))->toBe(12);
    });

    test('4 off gives 10 points', function () {
        expect(callPrivate('getPositionScore', 4, 2024))->toBe(10);
    });

    test('5 off gives 8 points', function () {
        expect(callPrivate('getPositionScore', 5, 2024))->toBe(8);
    });

    test('6 off gives 6 points', function () {
        expect(callPrivate('getPositionScore', 6, 2024))->toBe(6);
    });

    test('7 off gives 4 points', function () {
        expect(callPrivate('getPositionScore', 7, 2024))->toBe(4);
    });

    test('8 off gives 2 points', function () {
        expect(callPrivate('getPositionScore', 8, 2024))->toBe(2);
    });

    test('9 off gives 1 point', function () {
        expect(callPrivate('getPositionScore', 9, 2024))->toBe(1);
    });

    test('10 off gives 0 points', function () {
        expect(callPrivate('getPositionScore', 10, 2024))->toBe(0);
    });

    test('11 off gives -1 point', function () {
        expect(callPrivate('getPositionScore', 11, 2024))->toBe(-1);
    });

    test('15 off gives -8 points', function () {
        expect(callPrivate('getPositionScore', 15, 2024))->toBe(-8);
    });

    test('19 off gives -18 points', function () {
        expect(callPrivate('getPositionScore', 19, 2024))->toBe(-18);
    });

    test('20+ off (max diff) gives -25 points', function () {
        expect(callPrivate('getPositionScore', 20, 2024))->toBe(-25);
        expect(callPrivate('getPositionScore', 50, 2024))->toBe(-25);
    });

    test('score decreases monotonically as diff increases', function () {
        $prev = PHP_INT_MAX;
        for ($i = 0; $i <= 20; $i++) {
            $score = callPrivate('getPositionScore', $i, 2024);
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
        expect(callPrivate('getSprintPositionScore', 0, 2024))->toBe(8);
    });

    test('1 off gives 7 points', function () {
        expect(callPrivate('getSprintPositionScore', 1, 2024))->toBe(7);
    });

    test('2 off gives 6 points', function () {
        expect(callPrivate('getSprintPositionScore', 2, 2024))->toBe(6);
    });

    test('7 off gives 1 point', function () {
        expect(callPrivate('getSprintPositionScore', 7, 2024))->toBe(1);
    });

    test('8+ off gives 0 points (no negatives in sprint)', function () {
        expect(callPrivate('getSprintPositionScore', 8, 2024))->toBe(0);
        expect(callPrivate('getSprintPositionScore', 20, 2024))->toBe(0);
    });

    test('sprint scores are never negative', function () {
        for ($i = 0; $i <= 25; $i++) {
            expect(callPrivate('getSprintPositionScore', $i, 2024))->toBeGreaterThanOrEqual(0);
        }
    });
});

// ---------------------------------------------------------------------------
// getPreseasonConstructorPositionScore — preseason constructor diff table
// ---------------------------------------------------------------------------

describe('getPreseasonConstructorPositionScore', function () {
    test('exact match gives 10 points', function () {
        expect(callPrivate('getPreseasonConstructorPositionScore', 0))->toBe(10);
    });

    test('1 off gives 8 points', function () {
        expect(callPrivate('getPreseasonConstructorPositionScore', 1))->toBe(8);
    });

    test('4 off gives 2 points', function () {
        expect(callPrivate('getPreseasonConstructorPositionScore', 4))->toBe(2);
    });

    test('5 off gives 0 points', function () {
        expect(callPrivate('getPreseasonConstructorPositionScore', 5))->toBe(0);
    });

    test('6 off gives -2 points', function () {
        expect(callPrivate('getPreseasonConstructorPositionScore', 6))->toBe(-2);
    });

    test('10+ off gives -10 points (floor)', function () {
        expect(callPrivate('getPreseasonConstructorPositionScore', 10))->toBe(-10);
        expect(callPrivate('getPreseasonConstructorPositionScore', 20))->toBe(-10);
    });
});

// ---------------------------------------------------------------------------
// scoreCountPrediction — count-based predictions (e.g. DNF count)
// ---------------------------------------------------------------------------

describe('scoreCountPrediction', function () {
    test('exact match gives 15 points', function () {
        expect(callPrivate('scoreCountPrediction', 3, 3))->toBe(15);
    });

    test('1 off gives 10 points', function () {
        expect(callPrivate('scoreCountPrediction', 2, 3))->toBe(10);
        expect(callPrivate('scoreCountPrediction', 4, 3))->toBe(10);
    });

    test('2 off gives 5 points', function () {
        expect(callPrivate('scoreCountPrediction', 1, 3))->toBe(5);
        expect(callPrivate('scoreCountPrediction', 5, 3))->toBe(5);
    });

    test('3+ off gives 0 points', function () {
        expect(callPrivate('scoreCountPrediction', 0, 3))->toBe(0);
        expect(callPrivate('scoreCountPrediction', 6, 3))->toBe(0);
    });

    test('null predicted gives 0', function () {
        expect(callPrivate('scoreCountPrediction', null, 3))->toBe(0);
    });

    test('null actual gives 0', function () {
        expect(callPrivate('scoreCountPrediction', 3, null))->toBe(0);
    });

    test('both null gives 0', function () {
        expect(callPrivate('scoreCountPrediction', null, null))->toBe(0);
    });
});
