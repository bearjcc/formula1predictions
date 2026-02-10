<?php

namespace App\Services;

use App\Exceptions\F1ApiException;
use App\Models\Drivers;
use App\Models\Races;
use App\Models\Teams;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class F1ApiService
{
    private const BASE_URL = 'https://f1api.dev/api';

    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get race results for a specific year and round.
     * Uses DB first; only calls external API when no local data.
     *
     * @throws F1ApiException when API is unreachable or returns non-2xx
     */
    public function getRaceResults(int $year, int $round): array
    {
        $race = Races::where('season', $year)->where('round', $round)->first();
        if ($race !== null) {
            return ['races' => $this->raceModelToApiShape($race)];
        }

        $endpoint = "/{$year}/{$round}/race";
        try {
            $response = $this->makeApiCall($endpoint);
            if (! $response->successful()) {
                throw new F1ApiException(
                    'Failed to fetch race data',
                    $response->status(),
                    $endpoint,
                    $year
                );
            }
            $data = $response->json();
            if (isset($data['races'])) {
                $this->syncRaceFromApi($data['races'], $year, $round);
            }

            return $data;
        } catch (F1ApiException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new F1ApiException(
                'F1 API connection failed: '.$e->getMessage(),
                null,
                $endpoint,
                $year,
                $e instanceof Exception ? $e : null
            );
        }
    }

    /**
     * Get all races for a specific year.
     * Uses DB first; only calls external API when no local data for that season.
     */
    public function getRacesForYear(int $year): array
    {
        $races = Races::where('season', $year)->orderBy('round')->get();
        if ($races->isEmpty()) {
            try {
                // Prefer the single-shot season schedule endpoint to avoid
                // dozens of per-round API calls, especially for future
                // seasons like 2026 where data may be incomplete.
                $syncedCount = $this->syncScheduleToRaces($year);

                if ($syncedCount === 0) {
                    // If the schedule endpoint is available but returns no
                    // races yet, treat it as "no data" instead of a hard
                    // failure so the UI can render a friendly empty state.
                    return [];
                }

                $races = Races::where('season', $year)->orderBy('round')->get();
            } catch (F1ApiException $e) {
                Log::warning('F1 API season schedule sync failed', array_merge(
                    ['year' => $year, 'message' => $e->getMessage()],
                    $e->getLogContext()
                ));

                // Gracefully degrade to an empty list so we don\'t block
                // the races page behind a 500 when the upstream API is
                // flaky or future-season data is not published yet.
                return [];
            } catch (Throwable $e) {
                Log::warning('F1 API season schedule sync failed', [
                    'year' => $year,
                    'message' => $e->getMessage(),
                ]);

                return [];
            }
        }

        return $this->racesCollectionToApiShape($races);
    }

    /**
     * Fetch all races for a year by making multiple API calls
     *
     * @throws F1ApiException when API fails and no races could be loaded
     */
    private function fetchAllRacesForYear(int $year): array
    {
        $races = [];
        $maxRounds = 24; // F1 typically has 20-24 races per season
        $hadApiFailure = false;

        for ($round = 1; $round <= $maxRounds; $round++) {
            try {
                $raceData = $this->getRaceResults($year, $round);
                if (isset($raceData['races'])) {
                    $race = $raceData['races'];
                    $race['status'] = $this->determineRaceStatus($race);
                    $races[] = $race;
                }
            } catch (F1ApiException $e) {
                if ($e->statusCode === 404) {
                    break;
                }
                $hadApiFailure = true;
                Log::warning('F1 API fetch failed for race', array_merge(
                    ['round' => $round, 'message' => $e->getMessage()],
                    $e->getLogContext()
                ));
            } catch (Throwable $e) {
                $hadApiFailure = true;
                Log::warning('F1 API fetch failed for race', [
                    'year' => $year,
                    'round' => $round,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        if (count($races) === 0 && $hadApiFailure) {
            throw new F1ApiException('Unable to load race data', null, null, $year);
        }

        return $races;
    }

    /**
     * Determine the status of a race based on its date and results
     */
    private function determineRaceStatus(array $race): string
    {
        $time = $race['time'] ?? '00:00:00';
        $time = is_string($time) ? $time : '00:00:00';
        $raceDate = Carbon::parse($race['date'].' '.$time);
        $now = Carbon::now();

        // If race has results, it's completed
        if (! empty($race['results'])) {
            return 'completed';
        }

        // If race date is in the future, it's upcoming
        if ($raceDate->isFuture()) {
            return 'upcoming';
        }

        // If race date is today or very recent but no results, it might be ongoing or cancelled
        if ($raceDate->isToday() || $raceDate->diffInDays($now) <= 1) {
            return 'ongoing';
        }

        // If race date is past but no results, it might be cancelled
        return 'cancelled';
    }

    /**
     * Convert a Races model to the API-shaped array expected by views and consumers.
     *
     * @return array<string, mixed>
     */
    private function raceModelToApiShape(Races $race): array
    {
        $circuit = array_filter([
            'circuitName' => $race->circuit_name,
            'country' => $race->country,
            'url' => $race->circuit_url,
            'locality' => $race->locality,
            'circuitLength' => $race->circuit_length !== null ? (string) $race->circuit_length : null,
            'circuitId' => $race->circuit_api_id,
        ], fn ($v) => $v !== null && $v !== '');

        return [
            'round' => $race->round,
            'date' => $race->date->format('Y-m-d'),
            'time' => $race->time ? $race->time->format('H:i:s') : '00:00:00',
            'raceName' => $race->race_name,
            'circuit' => $circuit,
            'status' => $race->status,
            'results' => $race->getResultsArray(),
        ];
    }

    /**
     * Convert a collection of Races models to API-shaped array for getRacesForYear.
     *
     * @return list<array<string, mixed>>
     */
    private function racesCollectionToApiShape(Collection $races): array
    {
        return $races->map(fn (Races $race) => $this->raceModelToApiShape($race))->values()->all();
    }

    /**
     * Persist API race payload into the races table (create or update by season/round).
     *
     * @param  array<string, mixed>  $apiRace
     */
    private function syncRaceFromApi(array $apiRace, int $season, int $round): Races
    {
        $circuit = $apiRace['circuit'] ?? [];
        $circuitLength = null;
        if (isset($circuit['circuitLength'])) {
            $circuitLength = is_numeric($circuit['circuitLength']) ? (float) $circuit['circuitLength'] : (float) preg_replace('/[^0-9.]/', '', (string) $circuit['circuitLength']);
        }

        $date = isset($apiRace['date']) ? Carbon::parse($apiRace['date']) : null;
        $time = null;
        if (isset($apiRace['time'])) {
            $parsed = Carbon::parse($apiRace['time']);
            $time = $parsed->format('H:i:s');
        }

        $status = $this->determineRaceStatus($apiRace);

        $attributes = [
            'race_name' => $apiRace['raceName'] ?? 'Unknown',
            'date' => $date,
            'time' => $time,
            'circuit_api_id' => $circuit['circuitId'] ?? null,
            'circuit_name' => $circuit['circuitName'] ?? null,
            'circuit_url' => $circuit['url'] ?? null,
            'country' => $circuit['country'] ?? null,
            'locality' => $circuit['locality'] ?? null,
            'circuit_length' => $circuitLength,
            'laps' => $apiRace['laps'] ?? null,
            'weather' => $apiRace['weather'] ?? null,
            'temperature' => $apiRace['temperature'] ?? null,
            'status' => $status,
            'results' => $apiRace['results'] ?? null,
        ];

        return Races::updateOrCreate(
            ['season' => $season, 'round' => $round],
            $attributes
        );
    }

    /**
     * Make an API call to the F1 API
     */
    private function makeApiCall(string $endpoint): Response
    {
        $url = self::BASE_URL.$endpoint;

        $request = Http::timeout(30)
            ->retry(3, 1000);

        if (app()->environment('local')) {
            $request = $request->withoutVerifying();
        }

        return $request->get($url);
    }

    /**
     * Fetch full season schedule from /{year} (includes qualifying and sprint times).
     *
     * @return array<string, mixed>
     */
    public function fetchSeasonSchedule(int $year): array
    {
        $response = $this->makeApiCall("/{$year}");
        if (! $response->successful()) {
            throw new F1ApiException("Failed to fetch season schedule for {$year}", $response->status(), "/{$year}", $year);
        }

        return $response->json();
    }

    /**
     * Sync full season from F1 API schedule: create or update all races for the year.
     * Populates race_name, date, time, circuit fields, qualifying_start, sprint_qualifying_start, has_sprint.
     *
     * @return array{created: int, updated: int}
     */
    public function syncSeasonRacesFromSchedule(int $year): array
    {
        $data = $this->fetchSeasonSchedule($year);
        $races = $data['races'] ?? [];
        $created = 0;
        $updated = 0;

        foreach ($races as $apiRace) {
            $round = isset($apiRace['round']) ? (int) $apiRace['round'] : null;
            if ($round === null) {
                continue;
            }

            $race = $this->upsertRaceFromScheduleEntry($apiRace, $year, $round);
            if ($race->wasRecentlyCreated) {
                $created++;
            } else {
                $updated++;
            }
        }

        return ['created' => $created, 'updated' => $updated];
    }

    /**
     * Map one API schedule race entry to Races model and updateOrCreate.
     *
     * @param  array<string, mixed>  $apiRace
     */
    private function upsertRaceFromScheduleEntry(array $apiRace, int $season, int $round): Races
    {
        $schedule = $apiRace['schedule'] ?? [];
        $raceSchedule = $schedule['race'] ?? [];
        $qualy = $schedule['qualy'] ?? [];
        $sprintQualy = $schedule['sprintQualy'] ?? [];
        $circuit = $apiRace['circuit'] ?? [];

        $date = ! empty($raceSchedule['date']) ? Carbon::parse($raceSchedule['date']) : null;
        $time = null;
        if (! empty($raceSchedule['time'])) {
            $parsed = Carbon::parse($raceSchedule['time']);
            $time = $parsed->format('H:i:s');
        }

        $qualifyingStart = null;
        if (! empty($qualy['date']) && ! empty($qualy['time'])) {
            $qualifyingStart = Carbon::parse($qualy['date'].' '.$qualy['time']);
        }
        $sprintQualifyingStart = null;
        if (! empty($sprintQualy['date']) && ! empty($sprintQualy['time'])) {
            $sprintQualifyingStart = Carbon::parse($sprintQualy['date'].' '.$sprintQualy['time']);
        }
        $hasSprint = $sprintQualifyingStart !== null;

        $circuitLength = null;
        if (isset($circuit['circuitLength'])) {
            $raw = $circuit['circuitLength'];
            $circuitLength = is_numeric($raw) ? (float) $raw : (float) preg_replace('/[^0-9.]/', '', (string) $raw);
        }

        $status = ! empty($apiRace['winner']) ? 'completed' : 'upcoming';

        $attributes = [
            'race_name' => $apiRace['raceName'] ?? 'Unknown',
            'date' => $date,
            'time' => $time,
            'qualifying_start' => $qualifyingStart,
            'sprint_qualifying_start' => $sprintQualifyingStart,
            'has_sprint' => $hasSprint,
            'circuit_api_id' => $circuit['circuitId'] ?? null,
            'circuit_name' => $circuit['circuitName'] ?? null,
            'circuit_url' => $circuit['url'] ?? null,
            'country' => $circuit['country'] ?? null,
            'locality' => $circuit['city'] ?? null,
            'circuit_length' => $circuitLength,
            'laps' => $apiRace['laps'] ?? null,
            'status' => $status,
        ];

        return Races::updateOrCreate(
            ['season' => $season, 'round' => $round],
            $attributes
        );
    }

    /**
     * Sync qualifying and sprint qualifying times from F1 API season schedule to races table.
     * Creates missing races from schedule, then updates qualifying_start, sprint_qualifying_start, has_sprint.
     *
     * @return int Number of races created + updated
     */
    public function syncScheduleToRaces(int $year): int
    {
        $result = $this->syncSeasonRacesFromSchedule($year);

        return $result['created'] + $result['updated'];
    }

    /**
     * Fetch drivers championship data for a season (uses drivers-championship endpoint).
     *
     * @return array<string, mixed>
     */
    public function fetchDriversChampionship(int $year): array
    {
        $response = $this->makeApiCall("/{$year}/drivers-championship");
        if (! $response->successful()) {
            throw new F1ApiException("Failed to fetch drivers championship for {$year}", $response->status(), "/{$year}/drivers-championship", $year);
        }

        return $response->json();
    }

    /**
     * Fetch drivers for a season from the year-specific drivers endpoint (/{year}/drivers).
     * Use for future seasons (e.g. 2026) when drivers-championship may not be populated yet.
     *
     * @return array{drivers: array<int, array<string, mixed>>, total: int}
     */
    public function fetchDriversForYear(int $year): array
    {
        $limit = 30;
        $offset = 0;
        $allDrivers = [];
        $total = 0;
        do {
            $response = $this->makeApiCall("/{$year}/drivers?limit={$limit}&offset={$offset}");
            if (! $response->successful()) {
                throw new F1ApiException("Failed to fetch drivers for {$year}", $response->status(), "/{$year}/drivers", $year);
            }
            $data = $response->json();
            $chunk = $data['drivers'] ?? [];
            $allDrivers = array_merge($allDrivers, $chunk);
            $total = (int) ($data['total'] ?? 0);
            $offset += $limit;
        } while (count($chunk) === $limit && $offset < $total);

        return ['drivers' => $allDrivers, 'total' => count($allDrivers)];
    }

    /**
     * Fetch constructors championship data for a season (uses constructors-championship endpoint).
     *
     * @return array<string, mixed>
     */
    public function fetchConstructorsChampionship(int $year): array
    {
        $response = $this->makeApiCall("/{$year}/constructors-championship");
        if (! $response->successful()) {
            throw new F1ApiException("Failed to fetch constructors championship for {$year}", $response->status(), "/{$year}/constructors-championship", $year);
        }

        return $response->json();
    }

    /**
     * Sync teams (constructors) for a season from constructors championship. Upserts by team_id.
     *
     * @return int Number of teams created or updated
     */
    public function syncTeamsForSeason(int $year): int
    {
        try {
            $data = $this->fetchConstructorsChampionship($year);
        } catch (F1ApiException $e) {
            Log::warning('Could not sync teams for season', ['year' => $year, 'message' => $e->getMessage()]);

            return 0;
        }

        $entries = $data['constructors_championship'] ?? [];
        $count = 0;
        foreach ($entries as $entry) {
            $teamId = $entry['teamId'] ?? null;
            if ($teamId === null) {
                continue;
            }
            $team = $entry['team'] ?? [];
            $founded = isset($team['firstAppareance']) ? (int) $team['firstAppareance'] : null;

            Teams::updateOrCreate(
                ['team_id' => $teamId],
                [
                    'team_name' => $team['teamName'] ?? $teamId,
                    'nationality' => $team['country'] ?? null,
                    'url' => $team['url'] ?? null,
                    'founded' => $founded,
                    'world_championships' => $team['constructorsChampionships'] ?? 0,
                    'is_active' => true,
                ]
            );
            $count++;
        }

        return $count;
    }

    /**
     * Sync drivers for a season from drivers championship (or from /{year}/drivers when championship has no data).
     * Upserts by driver_id; links to team when present.
     *
     * @return int Number of drivers created or updated
     */
    public function syncDriversForSeason(int $year): int
    {
        $entries = [];
        try {
            $data = $this->fetchDriversChampionship($year);
            $entries = $data['drivers_championship'] ?? [];
        } catch (F1ApiException $e) {
            Log::info('Drivers championship not available for year, trying year/drivers endpoint', ['year' => $year, 'message' => $e->getMessage()]);
        }

        if ($entries !== []) {
            return $this->syncDriversFromChampionshipEntries($entries);
        }

        try {
            $data = $this->fetchDriversForYear($year);
            $drivers = $data['drivers'] ?? [];
        } catch (F1ApiException $e) {
            Log::warning('Could not sync drivers for season', ['year' => $year, 'message' => $e->getMessage()]);

            return 0;
        }

        return $this->syncDriversFromYearDriversList($drivers);
    }

    /**
     * @param  array<int, array<string, mixed>>  $entries  drivers_championship entries
     */
    private function syncDriversFromChampionshipEntries(array $entries): int
    {
        $count = 0;
        foreach ($entries as $entry) {
            $driverId = $entry['driverId'] ?? null;
            if ($driverId === null) {
                continue;
            }
            $driver = $entry['driver'] ?? [];
            $teamId = $entry['teamId'] ?? null;
            $teamDbId = $teamId ? Teams::where('team_id', $teamId)->value('id') : null;

            $birthday = null;
            if (! empty($driver['birthday'])) {
                try {
                    $birthday = Carbon::parse($driver['birthday'])->format('Y-m-d');
                } catch (Throwable) {
                    // ignore invalid date
                }
            }

            Drivers::updateOrCreate(
                ['driver_id' => $driverId],
                [
                    'name' => $driver['name'] ?? 'Unknown',
                    'surname' => $driver['surname'] ?? '',
                    'nationality' => $driver['nationality'] ?? null,
                    'url' => $driver['url'] ?? null,
                    'driver_number' => isset($driver['number']) ? (string) $driver['number'] : null,
                    'date_of_birth' => $birthday,
                    'team_id' => $teamDbId,
                    'is_active' => true,
                ]
            );
            $count++;
        }

        return $count;
    }

    /**
     * Sync from /{year}/drivers response (flat driver list). Birthday format DD/MM/YYYY.
     *
     * @param  array<int, array<string, mixed>>  $drivers
     */
    private function syncDriversFromYearDriversList(array $drivers): int
    {
        $count = 0;
        foreach ($drivers as $d) {
            $driverId = $d['driverId'] ?? null;
            if ($driverId === null) {
                continue;
            }
            $teamId = $d['teamId'] ?? null;
            $teamDbId = $teamId ? Teams::where('team_id', $teamId)->value('id') : null;

            $birthday = null;
            if (! empty($d['birthday'])) {
                try {
                    $birthday = Carbon::parse($d['birthday'])->format('Y-m-d');
                } catch (Throwable) {
                    // ignore invalid date
                }
            }

            Drivers::updateOrCreate(
                ['driver_id' => $driverId],
                [
                    'name' => $d['name'] ?? 'Unknown',
                    'surname' => $d['surname'] ?? '',
                    'nationality' => $d['nationality'] ?? null,
                    'url' => $d['url'] ?? null,
                    'driver_number' => isset($d['number']) ? (string) $d['number'] : null,
                    'date_of_birth' => $birthday,
                    'team_id' => $teamDbId,
                    'is_active' => true,
                ]
            );
            $count++;
        }

        return $count;
    }

    /**
     * Clear cache for a specific year
     */
    public function clearCache(int $year): void
    {
        Cache::forget("f1_races_{$year}");

        // Clear individual race caches
        for ($round = 1; $round <= 24; $round++) {
            Cache::forget("f1_race_{$year}_{$round}");
        }
    }

    /**
     * Get available years from F1 API /seasons. Newest first. Falls back to default list on API failure.
     *
     * @return list<int>
     */
    public function getAvailableYears(): array
    {
        $cacheKey = 'f1_available_years';

        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            try {
                $data = $this->getSeasons();
                $championships = $data['championships'] ?? [];
                $years = [];
                foreach ($championships as $c) {
                    $year = isset($c['year']) ? (int) $c['year'] : null;
                    if ($year !== null && $year >= 2000) {
                        $years[] = $year;
                    }
                }
                $years = array_values(array_unique($years));
                rsort($years, SORT_NUMERIC);

                return $years;
            } catch (Throwable $e) {
                Log::warning('F1 API getSeasons failed, using default available years', [
                    'message' => $e->getMessage(),
                ]);

                return [2022, 2023, 2024, 2025, 2026];
            }
        });
    }

    /**
     * Get all drivers with pagination support
     */
    public function getDrivers(int $limit = 30, int $offset = 0): array
    {
        $cacheKey = "f1_drivers_{$limit}_{$offset}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($limit, $offset) {
            $response = $this->makeApiCall("/drivers?limit={$limit}&offset={$offset}");

            if (! $response->successful()) {
                throw new Exception('Failed to fetch drivers data: '.$response->status());
            }

            return $response->json();
        });
    }

    /**
     * Get a specific driver by ID
     */
    public function getDriver(string $driverId): array
    {
        $cacheKey = "f1_driver_{$driverId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($driverId) {
            $response = $this->makeApiCall("/drivers/{$driverId}");

            if (! $response->successful()) {
                throw new Exception("Failed to fetch driver {$driverId}: ".$response->status());
            }

            return $response->json();
        });
    }

    /**
     * Get all teams with pagination support
     */
    public function getTeams(int $limit = 30, int $offset = 0): array
    {
        $cacheKey = "f1_teams_{$limit}_{$offset}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($limit, $offset) {
            $response = $this->makeApiCall("/teams?limit={$limit}&offset={$offset}");

            if (! $response->successful()) {
                throw new Exception('Failed to fetch teams data: '.$response->status());
            }

            return $response->json();
        });
    }

    /**
     * Get a specific team by ID
     */
    public function getTeam(string $teamId): array
    {
        $cacheKey = "f1_team_{$teamId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($teamId) {
            $response = $this->makeApiCall("/teams/{$teamId}");

            if (! $response->successful()) {
                throw new Exception("Failed to fetch team {$teamId}: ".$response->status());
            }

            return $response->json();
        });
    }

    /**
     * Get all circuits with pagination support
     */
    public function getCircuits(int $limit = 30, int $offset = 0): array
    {
        $cacheKey = "f1_circuits_{$limit}_{$offset}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($limit, $offset) {
            $response = $this->makeApiCall("/circuits?limit={$limit}&offset={$offset}");

            if (! $response->successful()) {
                throw new Exception('Failed to fetch circuits data: '.$response->status());
            }

            return $response->json();
        });
    }

    /**
     * Get a specific circuit by ID
     */
    public function getCircuit(string $circuitId): array
    {
        $cacheKey = "f1_circuit_{$circuitId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($circuitId) {
            $response = $this->makeApiCall("/circuits/{$circuitId}");

            if (! $response->successful()) {
                throw new Exception("Failed to fetch circuit {$circuitId}: ".$response->status());
            }

            return $response->json();
        });
    }

    /**
     * Get driver standings for a specific year
     */
    public function getDriverStandings(int $year): array
    {
        $cacheKey = "f1_driver_standings_{$year}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($year) {
            $response = $this->makeApiCall("/{$year}/drivers/standings");

            if (! $response->successful()) {
                throw new Exception("Failed to fetch driver standings for {$year}: ".$response->status());
            }

            return $response->json();
        });
    }

    /**
     * Get constructor standings for a specific year
     */
    public function getConstructorStandings(int $year): array
    {
        $cacheKey = "f1_constructor_standings_{$year}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($year) {
            $response = $this->makeApiCall("/{$year}/constructors/standings");

            if (! $response->successful()) {
                throw new Exception("Failed to fetch constructor standings for {$year}: ".$response->status());
            }

            return $response->json();
        });
    }

    /**
     * Get all seasons available
     */
    public function getSeasons(): array
    {
        $cacheKey = 'f1_seasons';

        return Cache::remember($cacheKey, self::CACHE_TTL * 24, function () { // Cache for 24 hours
            $response = $this->makeApiCall('/seasons');

            if (! $response->successful()) {
                throw new Exception('Failed to fetch seasons data: '.$response->status());
            }

            return $response->json();
        });
    }

    /**
     * Get qualifying results for a specific year and round
     */
    public function getQualifyingResults(int $year, int $round): array
    {
        $cacheKey = "f1_qualifying_{$year}_{$round}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($year, $round) {
            $response = $this->makeApiCall("/{$year}/{$round}/qualifying");

            if (! $response->successful()) {
                throw new Exception("Failed to fetch qualifying data for {$year}/{$round}: ".$response->status());
            }

            return $response->json();
        });
    }

    /**
     * Get sprint results for a specific year and round
     */
    public function getSprintResults(int $year, int $round): array
    {
        $cacheKey = "f1_sprint_{$year}_{$round}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($year, $round) {
            $response = $this->makeApiCall("/{$year}/{$round}/sprint");

            if (! $response->successful()) {
                throw new Exception("Failed to fetch sprint data for {$year}/{$round}: ".$response->status());
            }

            return $response->json();
        });
    }

    /**
     * Clear all caches
     */
    public function clearAllCache(): void
    {
        // Clear year-specific caches
        for ($year = 2020; $year <= config('f1.current_season'); $year++) {
            $this->clearCache($year);
        }

        // Clear general caches
        Cache::forget('f1_seasons');
        Cache::forget('f1_available_years');
        Cache::forget('f1_drivers_30_0');
        Cache::forget('f1_teams_30_0');
        Cache::forget('f1_circuits_30_0');
    }

    /**
     * Test the API connection
     */
    public function testConnection(): bool
    {
        try {
            $response = $this->makeApiCall('/2024/1/race');

            return $response->successful();
        } catch (Exception $e) {
            Log::error('F1 API connection test failed: '.$e->getMessage());

            return false;
        }
    }
}
