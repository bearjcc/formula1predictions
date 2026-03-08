<?php

namespace App\Services;

use App\Models\Races;

class RaceResultSyncService
{
    public function __construct(
        private readonly F1ApiService $f1ApiService,
    ) {}

    /**
     * Fetch the latest race results and persist them to the local race record.
     *
     * @return array{result_count: int, status: string}
     */
    public function sync(Races $race): array
    {
        $apiResults = $this->f1ApiService->getRaceResults($race->season, $race->round);
        $results = data_get($apiResults, 'races.results', []);

        if (! is_array($results) || $results === []) {
            throw new \RuntimeException('No race results were available from the F1 API.');
        }

        $race->forceFill([
            'results' => $results,
            'status' => 'completed',
        ])->save();

        return [
            'result_count' => count($results),
            'status' => (string) $race->status,
        ];
    }
}
