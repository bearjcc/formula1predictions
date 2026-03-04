<?php

namespace App\Services;

use App\Models\Prediction;
use App\Models\Races;
use App\Models\User;

class PredictionLifecycle
{
    public function canEdit(Prediction $prediction): bool
    {
        if (in_array($prediction->status, ['locked', 'scored', 'cancelled'], true)) {
            return false;
        }

        if (in_array($prediction->type, ['race', 'sprint'], true) && $prediction->race) {
            return $prediction->type === 'sprint'
                ? $prediction->race->allowsSprintPredictions()
                : $prediction->race->allowsPredictions();
        }

        if ($prediction->type === 'preseason') {
            $deadline = Races::getPreseasonDeadlineForSeason($prediction->season);

            return $deadline !== null && now()->lt($deadline);
        }

        return true;
    }

    public function submit(Prediction $prediction): bool
    {
        if (! $this->canEdit($prediction)) {
            return false;
        }

        $prediction->forceFill([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return $prediction->save();
    }

    public function canCreate(User $user, string $type, int $season, ?int $raceRound = null, ?Races $race = null): bool
    {
        if (in_array($type, ['race', 'sprint'], true) && $raceRound !== null) {
            $race ??= Races::where('season', $season)->where('round', $raceRound)->first();
            if (! $race) {
                return false;
            }

            if ($type === 'sprint' && ! $race->hasSprint()) {
                return false;
            }

            $duplicateExists = $user->predictions()
                ->where('type', $type)
                ->where('season', $season)
                ->where('race_round', $raceRound)
                ->exists();

            if ($duplicateExists) {
                return false;
            }

            return $type === 'sprint'
                ? $race->allowsSprintPredictions()
                : $race->allowsPredictions();
        }

        if ($type === 'preseason') {
            $deadline = Races::getPreseasonDeadlineForSeason($season);

            if ($deadline === null || ! now()->lt($deadline)) {
                return false;
            }

            $already = $user->predictions()
                ->where('type', 'preseason')
                ->where('season', $season)
                ->exists();

            return ! $already;
        }

        return true;
    }

    public function predictionDeadline(string $type, int $season, ?Races $race = null): ?\Carbon\Carbon
    {
        if ($type === 'preseason') {
            return Races::getPreseasonDeadlineForSeason($season);
        }

        if ($race === null || ! in_array($type, ['race', 'sprint'], true)) {
            return null;
        }

        return $type === 'sprint'
            ? $race->getSprintPredictionDeadline()
            : $race->getRacePredictionDeadline();
    }
}
