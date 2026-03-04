<?php

namespace App\Http\Controllers;

use App\Models\Circuits;
use App\Models\Countries;
use App\Models\Drivers;
use App\Models\Races;
use App\Models\Standings;
use App\Models\Teams;
use App\Services\F1ApiService;
use Illuminate\View\View;

class StandingsController extends Controller
{
    public function races(int $year): View
    {
        return view('races', ['year' => $year]);
    }

    public function standings(int $year)
    {
        return to_route('standings.drivers', ['year' => $year], 302);
    }

    public function driverStandings(F1ApiService $f1, int $year): View
    {
        $season = $year;
        $driverStandings = Standings::getDriverStandings($season, null);
        $standingsByEntityId = $driverStandings->keyBy('entity_id');

        // Canonical season grid: drivers competing in this season, with team relationship attached.
        // This matches the prediction forms and other pages that need a consistent driver->team link.
        $allDrivers = Drivers::forSeason($season, $f1);

        $apiDriverStats = [];

        try {
            $data = $f1->fetchDriversChampionship($season);
            $entries = $data['drivers_championship'] ?? [];

            foreach ($entries as $entry) {
                $driverId = $entry['driverId'] ?? null;

                if ($driverId !== null) {
                    $apiDriverStats[$driverId] = [
                        'points' => (float) ($entry['points'] ?? 0),
                        'wins' => (int) ($entry['wins'] ?? 0),
                        'podiums' => (int) ($entry['podiums'] ?? 0),
                    ];
                }
            }
        } catch (\Throwable) {
            // API may not have data for future/past years; use empty fallback
        }

        $countriesByName = Countries::all()->keyBy('name');

        $rows = $allDrivers->map(function ($driver) use ($standingsByEntityId, $countriesByName, $apiDriverStats) {
            $standing = $standingsByEntityId->get((string) $driver->id) ?? $standingsByEntityId->get($driver->driver_id ?? '');
            $apiStats = $driver->driver_id ? ($apiDriverStats[$driver->driver_id] ?? null) : null;

            $points = $standing ? (float) $standing->points : ($apiStats['points'] ?? 0.0);
            $wins = $standing ? (int) ($standing->wins ?? 0) : ($apiStats['wins'] ?? 0);
            $podiums = $standing ? (int) ($standing->podiums ?? 0) : ($apiStats['podiums'] ?? 0);

            $driverName = trim($driver->name.' '.$driver->surname);

            $teamName = $driver->team?->team_name;

            $country = $driver->nationality ? $countriesByName->get($driver->nationality) : null;

            return [
                'sort_name' => trim($driver->surname.' '.$driver->name),
                'driver_name' => $driverName,
                'driver_slug' => $driver->slug,
                'nationality' => $driver->nationality,
                'country_flag_url' => $country ? $country->flag_url : '',
                'team_name' => $teamName,
                'team_display_name' => $driver->team?->display_name ?? Teams::displayNameFor($teamName),
                'team_slug' => $driver->team?->slug,
                'points' => $points,
                'wins' => $wins,
                'podiums' => $podiums,
            ];
        });

        $driverRows = $rows->sort(function ($a, $b) {
            $byPoints = (int) round($b['points'] * 100) - (int) round($a['points'] * 100);

            return $byPoints !== 0 ? $byPoints : strcasecmp($a['sort_name'], $b['sort_name']);
        })->values()->map(function ($row, $index) {
            unset($row['sort_name']);

            return array_merge(['position' => $index + 1], $row);
        })->all();

        $seasonStarted = Races::seasonHasStarted($season);
        $seasonEnded = Races::seasonHasEnded($season);

        return view('standings.drivers', [
            'year' => $season,
            'driverRows' => $driverRows,
            'seasonStarted' => $seasonStarted,
            'seasonEnded' => $seasonEnded,
        ]);
    }

    public function constructorStandings(F1ApiService $f1, int $year): View
    {
        $season = $year;
        $teamStandings = Standings::getConstructorStandings($season, null);
        $standingsByEntityId = $teamStandings->keyBy('entity_id');
        $entityIds = $teamStandings->pluck('entity_id')->unique()->filter()->values();

        $allTeams = collect();
        if ($entityIds->isNotEmpty()) {
            $byTeamId = Teams::whereIn('team_id', $entityIds)->with('drivers')->get();
            $numericIds = $entityIds->filter(fn ($id) => ctype_digit((string) $id))->values();
            $byId = $numericIds->isNotEmpty()
                ? Teams::whereIn('id', $numericIds->map(fn ($id) => (int) $id))->with('drivers')->get()
                : collect();
            $allTeams = $byTeamId->merge($byId)->unique('id')->values();
        }

        if ($allTeams->isEmpty()) {
            try {
                $data = $f1->fetchConstructorsChampionship($season);
                $entries = $data['constructors_championship'] ?? [];
                $apiTeamIds = collect($entries)->pluck('teamId')->filter()->unique()->values()->all();

                if ($apiTeamIds !== []) {
                    $allTeams = Teams::whereIn('team_id', $apiTeamIds)->with('drivers')->get();
                }
            } catch (\Throwable) {
                // leave empty
            }
        }

        $countriesByName = Countries::all()->keyBy('name');

        $rows = $allTeams->map(function ($team) use ($standingsByEntityId, $countriesByName) {
            $standing = $standingsByEntityId->get((string) $team->id) ?? $standingsByEntityId->get($team->team_id ?? '');

            $points = $standing ? (float) $standing->points : 0.0;
            $wins = $standing ? (int) ($standing->wins ?? 0) : 0;
            $podiums = $standing ? (int) ($standing->podiums ?? 0) : 0;

            $driverNames = $team->drivers->map(
                fn ($driver) => trim($driver->name.' '.$driver->surname)
            )->values()->all();

            $country = $team->nationality ? $countriesByName->get($team->nationality) : null;

            return [
                'sort_name' => $team->team_name,
                'team_name' => $team->team_name,
                'team_display_name' => $team->display_name,
                'team_slug' => $team->slug,
                'nationality' => $team->nationality,
                'country_flag_url' => $country ? $country->flag_url : '',
                'driver_names' => $driverNames,
                'points' => $points,
                'wins' => $wins,
                'podiums' => $podiums,
            ];
        });

        $teamRows = $rows->sort(function ($a, $b) {
            $byPoints = (int) round($b['points'] * 100) - (int) round($a['points'] * 100);

            return $byPoints !== 0 ? $byPoints : strcasecmp($a['sort_name'], $b['sort_name']);
        })->values()->map(function ($row, $index) {
            unset($row['sort_name']);

            return array_merge(['position' => $index + 1], $row);
        })->all();

        $seasonStarted = Races::seasonHasStarted($season);
        $seasonEnded = Races::seasonHasEnded($season);

        return view('standings.constructors', [
            'year' => $season,
            'teamRows' => $teamRows,
            'seasonStarted' => $seasonStarted,
            'seasonEnded' => $seasonEnded,
        ]);
    }

    public function predictionStandings(int $year, ?string $username = null): View
    {
        return view('standings.predictions', [
            'year' => $year,
            'username' => $username,
        ]);
    }

    public function yearRaceDetail(int $year, int $id): View
    {
        $race = Races::where('season', $year)->where('round', $id)->first();
        abort_unless($race, 404);

        return view('race', ['race' => $race]);
    }

    public function constructorDetail(string $slug): View
    {
        $constructor = Teams::with('drivers')->get()->first(
            fn (Teams $team) => $team->slug === $slug
        );
        abort_unless($constructor, 404);

        return view('constructor', ['constructor' => $constructor]);
    }

    public function driverDetail(string $slug): View
    {
        $driver = Drivers::with('team')->get()->first(
            fn (Drivers $driver) => $driver->slug === $slug
        );
        abort_unless($driver, 404);

        return view('driver', ['driver' => $driver]);
    }

    public function circuitDetail(string $slug): View
    {
        $circuit = Circuits::all()->first(
            fn (Circuits $circuit) => $circuit->slug === $slug
        );
        abort_unless($circuit, 404);

        return view('circuit', ['circuit' => $circuit]);
    }

    public function raceDetail(string $slug): View
    {
        $race = Races::all()->first(
            fn (Races $race) => $race->slug === $slug
        );
        abort_unless($race, 404);

        return view('race', ['race' => $race]);
    }
}
