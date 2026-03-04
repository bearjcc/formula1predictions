<?php

namespace App\Services\Scoring;

use App\Models\Drivers;
use App\Models\Prediction;
use App\Models\Standings;
use App\Models\Teams;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class ChampionshipScoringService
{
    public function calculateChampionshipScore(Prediction $prediction, int $season): int
    {
        if (! in_array($prediction->type, ['preseason', 'midseason'], true)) {
            return 0;
        }

        $constructorStandings = Standings::getConstructorStandings($season, null);

        if ($prediction->type === 'preseason') {
            $score = $this->scorePreseasonConstructorOrder(
                $prediction->getConstructorOrder(),
                $constructorStandings
            );

            $score += $this->scoreTeammateBattles($prediction->getTeammateBattles(), $season);

            $actuals = config("f1.season_actuals.{$season}", []);
            $score += $this->scoreCountPrediction(
                $prediction->getRedFlags(),
                $actuals['red_flags'] ?? null
            );
            $score += $this->scoreCountPrediction(
                $prediction->getSafetyCars(),
                $actuals['safety_cars'] ?? null
            );

            return $score;
        }

        $driverStandings = Standings::getDriverStandings($season, null);

        if ($driverStandings->isEmpty() && $constructorStandings->isEmpty()) {
            return 0;
        }

        $driverLookup = Drivers::pluck('driver_id', 'id');
        $teamLookup = Teams::pluck('team_id', 'id');

        $driverScore = $this->scoreChampionshipOrder(
            $prediction->getDriverChampionshipOrder(),
            $driverStandings,
            $season,
            fn (int $localId) => $driverLookup[$localId] ?? null
        );

        $teamScore = $this->scoreChampionshipOrder(
            $prediction->getConstructorOrder(),
            $constructorStandings,
            $season,
            fn (int $localId) => $teamLookup[$localId] ?? null
        );

        $score = $driverScore['score'] + $teamScore['score'];
        $correctCount = $driverScore['correct'] + $teamScore['correct'];
        $totalPredicted = $driverScore['total'] + $teamScore['total'];

        if ($totalPredicted > 0 && $correctCount === $totalPredicted) {
            $score += 50;
        }

        return $score;
    }

    /**
     * Preseason constructor order: diff 0->10, 1->8, 2->6, 3->4, 4->2, 5->0, 6->-2, 7->-4, 8->-6, 9->-8, 10->-10, 10+ -> -10.
     */
    private function getPreseasonConstructorPositionScore(int $diff): int
    {
        return match (min($diff, 10)) {
            0 => 10,
            1 => 8,
            2 => 6,
            3 => 4,
            4 => 2,
            5 => 0,
            6 => -2,
            7 => -4,
            8 => -6,
            9 => -8,
            default => -10,
        };
    }

    /**
     * @param  array<int>  $predictedTeamIds
     * @param  EloquentCollection<int, \App\Models\Standings>  $constructorStandings
     */
    private function scorePreseasonConstructorOrder(
        array $predictedTeamIds,
        EloquentCollection $constructorStandings
    ): int {
        $teamLookup = Teams::pluck('team_id', 'id');
        $entityToPosition = $constructorStandings->keyBy('entity_id')->map(fn ($s) => $s->position - 1)->all();
        $score = 0;

        foreach ($predictedTeamIds as $position => $localId) {
            $entityId = $teamLookup[$localId] ?? null;
            if ($entityId === null) {
                continue;
            }

            $actualPosition = $entityToPosition[$entityId] ?? null;
            if ($actualPosition === null) {
                continue;
            }

            $diff = abs($position - $actualPosition);
            $score += $this->getPreseasonConstructorPositionScore($diff);
        }

        return $score;
    }

    /**
     * @param  array<int, int>  $teammateBattles  team_id => driver_id (predicted to finish higher)
     */
    private function scoreTeammateBattles(array $teammateBattles, int $season): int
    {
        if ($teammateBattles === []) {
            return 0;
        }

        $driverStandings = Standings::getDriverStandings($season, null);
        $driverIdToPosition = [];
        $driverLookup = Drivers::pluck('driver_id', 'id');

        foreach ($driverStandings as $row) {
            $driverIdToPosition[$row->entity_id] = $row->position;
        }

        $score = 0;
        $teams = Teams::whereIn('id', array_keys($teammateBattles))->with('drivers')->get();

        foreach ($teams as $team) {
            $predictedDriverId = $teammateBattles[$team->id] ?? null;
            if ($predictedDriverId === null) {
                continue;
            }

            $predictedEntityId = $driverLookup[$predictedDriverId] ?? null;
            if ($predictedEntityId === null) {
                continue;
            }

            $teammateEntityIds = $team->drivers->pluck('driver_id', 'id')->all();
            $positions = [];

            foreach ($teammateEntityIds as $localId => $entityId) {
                $pos = $driverIdToPosition[$entityId] ?? null;
                if ($pos !== null) {
                    $positions[$localId] = $pos;
                }
            }

            if (count($positions) < 2) {
                continue;
            }

            $higherLocalId = array_search(min($positions), $positions, true);
            if ((int) $higherLocalId === (int) $predictedDriverId) {
                $score += 5;
            }
        }

        return $score;
    }

    private function scoreCountPrediction(?int $predicted, ?int $actual): int
    {
        if ($actual === null || $predicted === null) {
            return 0;
        }

        $diff = abs($predicted - $actual);

        return match ($diff) {
            0 => 15,
            1 => 10,
            2 => 5,
            default => 0,
        };
    }

    /**
     * @param  array<int>  $predictedLocalIds
     * @param  EloquentCollection<int, \App\Models\Standings>  $actualStandings
     * @param  callable(int): ?string  $resolveToEntityId
     * @return array{score: int, correct: int, total: int}
     */
    private function scoreChampionshipOrder(
        array $predictedLocalIds,
        EloquentCollection $actualStandings,
        int $season,
        callable $resolveToEntityId
    ): array {
        $entityToPosition = $actualStandings->keyBy('entity_id')->map(fn ($s) => $s->position - 1)->all();
        $score = 0;
        $correct = 0;

        foreach ($predictedLocalIds as $position => $localId) {
            $entityId = $resolveToEntityId($localId);
            if ($entityId === null) {
                continue;
            }

            $actualPosition = $entityToPosition[$entityId] ?? null;
            if ($actualPosition === null) {
                continue;
            }

            $diff = abs($position - $actualPosition);

            $positionScore = match ($diff) {
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

            $score += $positionScore;

            if ($diff === 0) {
                $correct++;
            }
        }

        return ['score' => $score, 'correct' => $correct, 'total' => count($predictedLocalIds)];
    }
}
