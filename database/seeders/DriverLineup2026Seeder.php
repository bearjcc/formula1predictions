<?php

namespace Database\Seeders;

use App\Models\Drivers;
use App\Models\Teams;
use Illuminate\Database\Seeder;

/**
 * 2026 Formula 1 driver lineup (as of February 2026).
 * Assigns each driver to their constructor so standings and prediction forms show the correct team.
 */
class DriverLineup2026Seeder extends Seeder
{
    /** @var array<string, list<string>> team_name => [driver full name, ...] (names as stored in DB: e.g. Alex not Alexander, Andrea Kimi Antonelli, Arvin Lindblad, Sergio Pérez) */
    private const LINEUP_2026 = [
        'Red Bull Racing' => ['Max Verstappen', 'Isack Hadjar'],
        'Ferrari' => ['Charles Leclerc', 'Lewis Hamilton'],
        'McLaren' => ['Lando Norris', 'Oscar Piastri'],
        'Mercedes' => ['George Russell', 'Andrea Kimi Antonelli'],
        'Aston Martin' => ['Fernando Alonso', 'Lance Stroll'],
        'Audi' => ['Nico Hulkenberg', 'Gabriel Bortoleto'],
        'Cadillac' => ['Sergio Pérez', 'Valtteri Bottas'],
        'Williams' => ['Alex Albon', 'Carlos Sainz'],
        'Alpine' => ['Pierre Gasly', 'Franco Colapinto'],
        'Haas F1 Team' => ['Esteban Ocon', 'Oliver Bearman'],
        'RB' => ['Liam Lawson', 'Arvin Lindblad'],
    ];

    public function run(): void
    {
        $teamNameVariants = $this->teamNameVariants();

        foreach (self::LINEUP_2026 as $canonicalTeamName => $driverFullNames) {
            $team = $this->resolveTeam($canonicalTeamName, $teamNameVariants);
            if (! $team) {
                $team = $this->ensureTeamExists($canonicalTeamName);
            }
            if (! $team) {
                $this->command?->warn("Team not found and could not create: {$canonicalTeamName}");

                continue;
            }

            foreach ($driverFullNames as $fullName) {
                $driver = $this->resolveDriver(trim($fullName));
                if (! $driver) {
                    $this->command?->warn("Driver not found: {$fullName} ({$canonicalTeamName})");

                    continue;
                }
                if ((int) $driver->team_id !== (int) $team->id) {
                    $driver->update(['team_id' => $team->id]);
                    $this->command?->info("  {$driver->name} {$driver->surname} -> {$team->team_name}");
                }
            }
        }
    }

    /**
     * Resolve team by canonical name or known API/variant names.
     *
     * @param  array<string, list<string>>  $variants
     */
    private function resolveTeam(string $canonicalTeamName, array $variants): ?Teams
    {
        $namesToTry = array_merge([$canonicalTeamName], $variants[$canonicalTeamName] ?? []);
        foreach ($namesToTry as $name) {
            $team = Teams::where('team_name', $name)->first();
            if ($team) {
                return $team;
            }
        }
        $team = Teams::where('team_name', 'like', '%'.trim($canonicalTeamName).'%')->first();

        return $team ?: null;
    }

    /**
     * Create team if missing (e.g. Audi, Cadillac, RB before API sync).
     */
    private function ensureTeamExists(string $canonicalTeamName): ?Teams
    {
        $slug = str($canonicalTeamName)->lower()->replace([' ', '-'], '_')->slug();
        $teamId = 'lineup_2026_'.$slug;

        return Teams::firstOrCreate(
            ['team_id' => $teamId],
            [
                'team_name' => $canonicalTeamName,
                'is_active' => true,
            ]
        );
    }

    private function resolveDriver(string $fullName): ?Drivers
    {
        $fullName = trim($fullName);
        $parts = preg_split('/\s+/', $fullName, 2);
        if (count($parts) < 2) {
            return Drivers::where('surname', $fullName)->orWhere('name', $fullName)->first();
        }
        [$first, $last] = $parts;
        $normalized = strtolower($fullName);
        $normalizedNoAccents = $this->normalizeAccents($normalized);

        $driver = Drivers::where('name', $first)->where('surname', $last)->first();
        if ($driver) {
            return $driver;
        }
        $driver = Drivers::all()->first(fn (Drivers $d) => strtolower(trim($d->name.' '.$d->surname)) === $normalized);
        if ($driver) {
            return $driver;
        }
        return Drivers::all()->first(fn (Drivers $d) => $this->normalizeAccents(strtolower(trim($d->name.' '.$d->surname))) === $normalizedNoAccents);
    }

    private function normalizeAccents(string $s): string
    {
        $map = ['é' => 'e', 'è' => 'e', 'ê' => 'e', 'á' => 'a', 'à' => 'a', 'ä' => 'a', 'ö' => 'o', 'ü' => 'u', 'ñ' => 'n', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ç' => 'c'];
        return strtr($s, $map);
    }

    /**
     * Known API or display variants for team names (canonical => [variant1, ...]).
     *
     * @return array<string, list<string>>
     */
    private function teamNameVariants(): array
    {
        return [
            'RB' => ['Racing Bulls', 'Visa Cash App RB', 'VCARB'],
            'Audi' => ['Sauber', 'Kick Sauber', 'Stake F1 Team Kick Sauber'],
            'Haas F1 Team' => ['Haas', 'MoneyGram Haas F1 Team'],
        ];
    }
}
