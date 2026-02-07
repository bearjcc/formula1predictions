<?php

namespace App\Console\Commands;

use App\Models\Prediction;
use Illuminate\Console\Command;

class LockPredictionsPastDeadline extends Command
{
    protected $signature = 'predictions:lock-past-deadline';

    protected $description = 'Lock all submitted race/sprint predictions past their deadline (1 hour before qualifying/sprint qualifying).';

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

        $this->info("Locked {$locked} prediction(s) past deadline.");

        return Command::SUCCESS;
    }
}
