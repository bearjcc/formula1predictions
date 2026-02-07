<?php

namespace App\Services;

use App\Exceptions\F1ApiException;
use App\Models\Races;
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
        if ($races->isNotEmpty()) {
            return $this->racesCollectionToApiShape($races);
        }

        return $this->fetchAllRacesForYear($year);
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

        return Http::timeout(30)
            ->retry(3, 1000)
            ->withoutVerifying() // Disable SSL verification for development
            ->get($url);
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
     * Get available years (this could be enhanced with actual API data)
     */
    public function getAvailableYears(): array
    {
        return [2022, 2023, 2024, 2025];
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
        for ($year = 2020; $year <= 2025; $year++) {
            $this->clearCache($year);
        }

        // Clear general caches
        Cache::forget('f1_seasons');
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
