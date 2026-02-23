<?php

namespace App\Console\Commands;

use App\Models\Prediction;
use App\Models\Races;
use Illuminate\Console\Command;

class LockPredictionsPastDeadline extends Command
{
    protected $signature = 'predictions:lock-past-deadline';

    protected $description = 'Lock all submitted race/sprint/preseason predictions past their deadline.';

    public function handle(): int
    {
        $now = now();
        $locked = 0;

        $submitted = Prediction::where('status', 'submitted')
            ->whereIn('type', ['race', 'sprint'])
            ->with('race')
            ->get();

        foreach ($submitted as $prediction) {
            $race = $prediction->race;
            if ($race === null) {
                continue;
            }

            $pastDeadline = false;
            if ($prediction->type === 'race' && $race->qualifying_start !== null) {
                $pastDeadline = $now->gte($race->getRacePredictionDeadline());
            } elseif ($prediction->type === 'sprint' && $race->sprint_qualifying_start !== null) {
                $deadline = $race->getSprintPredictionDeadline();
                $pastDeadline = $deadline !== null && $now->gte($deadline);
            }

            if ($pastDeadline && $prediction->lock()) {
                $locked++;
                $this->line("Locked prediction {$prediction->id} ({$prediction->type}) for race {$race->race_name}");
            }
        }

        $preseasonSubmitted = Prediction::where('status', 'submitted')
            ->where('type', 'preseason')
            ->get();

        foreach ($preseasonSubmitted as $prediction) {
            $deadline = Races::getPreseasonDeadlineForSeason($prediction->season);
            if ($deadline !== null && $now->gte($deadline) && $prediction->lock()) {
                $locked++;
                $this->line("Locked preseason prediction {$prediction->id} for season {$prediction->season}");
            }
        }

        $this->info("Locked {$locked} prediction(s) past deadline.");

        return Command::SUCCESS;
    }
}
