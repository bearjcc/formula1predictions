<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Standings extends Model
{
    /** @use HasFactory<\Database\Factories\StandingsFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'season',
        'type',
        'round',
        'entity_id',
        'entity_name',
        'position',
        'points',
        'wins',
        'podiums',
        'poles',
        'fastest_laps',
        'dnfs',
        'additional_data',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'season' => 'integer',
            'round' => 'integer',
            'position' => 'integer',
            'points' => 'decimal:2',
            'wins' => 'integer',
            'podiums' => 'integer',
            'poles' => 'integer',
            'fastest_laps' => 'integer',
            'dnfs' => 'integer',
            'additional_data' => 'array',
        ];
    }

    /**
     * Scope to get standings for a specific season.
     */
    public function scopeForSeason($query, int $season)
    {
        return $query->where('season', $season);
    }

    /**
     * Scope to get standings by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get current standings (no round specified).
     */
    public function scopeCurrent($query)
    {
        return $query->whereNull('round');
    }

    /**
     * Scope to get race-by-race standings.
     */
    public function scopeByRound($query, int $round)
    {
        return $query->where('round', $round);
    }

    /**
     * Scope to get standings for a specific entity.
     */
    public function scopeForEntity($query, string $entityId)
    {
        return $query->where('entity_id', $entityId);
    }

    /**
     * Get the driver standings for a season.
     */
    public static function getDriverStandings(int $season, ?int $round = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = static::forSeason($season)->byType('drivers');

        if ($round) {
            $query->byRound($round);
        } else {
            $query->current();
        }

        return $query->orderBy('position')->get();
    }

    /**
     * Get the constructor standings for a season.
     */
    public static function getConstructorStandings(int $season, ?int $round = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = static::forSeason($season)->byType('constructors');

        if ($round) {
            $query->byRound($round);
        } else {
            $query->current();
        }

        return $query->orderBy('position')->get();
    }

    /**
     * Get the standings progression for an entity over a season.
     */
    public static function getEntityProgression(string $entityId, int $season): \Illuminate\Database\Eloquent\Collection
    {
        return static::forEntity($entityId)
            ->forSeason($season)
            ->whereNotNull('round')
            ->orderBy('round')
            ->get();
    }

    /**
     * Get the championship leader for a season and type.
     */
    public static function getLeader(int $season, string $type): ?Standings
    {
        return static::forSeason($season)
            ->byType($type)
            ->current()
            ->where('position', 1)
            ->first();
    }

    /**
     * Get the top N positions for a season and type.
     */
    public static function getTopN(int $season, string $type, int $n = 10): \Illuminate\Database\Eloquent\Collection
    {
        return static::forSeason($season)
            ->byType($type)
            ->current()
            ->where('position', '<=', $n)
            ->orderBy('position')
            ->get();
    }

    /**
     * Get the standings change between two rounds.
     */
    public static function getStandingsChange(int $season, string $type, int $currentRound, int $previousRound): array
    {
        $current = static::forSeason($season)
            ->byType($type)
            ->byRound($currentRound)
            ->orderBy('position')
            ->get()
            ->keyBy('entity_id');

        $previous = static::forSeason($season)
            ->byType($type)
            ->byRound($previousRound)
            ->orderBy('position')
            ->get()
            ->keyBy('entity_id');

        $changes = [];

        foreach ($current as $entityId => $currentStanding) {
            $previousStanding = $previous->get($entityId);

            if ($previousStanding) {
                $positionChange = $previousStanding->position - $currentStanding->position;
                $pointsChange = $currentStanding->points - $previousStanding->points;

                $changes[$entityId] = [
                    'entity_name' => $currentStanding->entity_name,
                    'current_position' => $currentStanding->position,
                    'previous_position' => $previousStanding->position,
                    'position_change' => $positionChange,
                    'points_change' => $pointsChange,
                ];
            }
        }

        return $changes;
    }

    /**
     * Get the additional data as an array.
     */
    public function getAdditionalDataArray(): array
    {
        return $this->additional_data ?? [];
    }

    /**
     * Check if this is a driver standing.
     */
    public function isDriverStanding(): bool
    {
        return $this->type === 'drivers';
    }

    /**
     * Check if this is a constructor standing.
     */
    public function isConstructorStanding(): bool
    {
        return $this->type === 'constructors';
    }

    /**
     * Get the position change indicator.
     */
    public function getPositionChangeIndicator(): string
    {
        // This would need to be calculated based on previous round data
        return '='; // Default to no change
    }
}
