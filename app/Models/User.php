<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_season_supporter',
        'supporter_since',
        'badges',
        'stats_cache',
        'stats_cache_updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_season_supporter' => 'boolean',
            'supporter_since' => 'datetime',
            'badges' => 'array',
            'stats_cache' => 'array',
            'stats_cache_updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Get the predictions for the user.
     * 
     * TODO: Create Prediction model and migration
     * TODO: Add prediction statistics methods (accuracy, total predictions, etc.)
     * TODO: Add prediction history with pagination
     * TODO: Implement prediction scoring methods
     * TODO: Add prediction comparison methods
     */
    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class);
    }

    /**
     * Get the user's prediction accuracy percentage.
     */
    public function getPredictionAccuracy(): float
    {
        $scoredPredictions = $this->predictions()
            ->where('status', 'scored')
            ->where('score', '>', 0);
        
        $totalPredictions = $scoredPredictions->count();
        
        if ($totalPredictions === 0) {
            return 0.0;
        }
        
        $totalScore = $scoredPredictions->sum('score');
        $maxPossibleScore = $totalPredictions * 25; // Assuming 25 points per perfect prediction
        
        return round(($totalScore / $maxPossibleScore) * 100, 2);
    }

    /**
     * Check if the user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        // For now, implement a simple role system
        // In a production app, you might want to use Spatie Laravel Permission package
        $roles = [
            'admin' => ['admin@example.com', 'system@example.com'],
            'system' => ['system@example.com', 'bot@example.com'],
            'moderator' => ['moderator@example.com'],
        ];
        
        return in_array($this->email, $roles[$role] ?? []);
    }

    /**
     * Check if the user has any of the given roles.
     */
    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if the user has all of the given roles.
     */
    public function hasAllRoles(array $roles): bool
    {
        foreach ($roles as $role) {
            if (!$this->hasRole($role)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get the user's total prediction score.
     */
    public function getTotalScore(): int
    {
        return $this->predictions()->where('status', 'scored')->sum('score') ?? 0;
    }

    /**
     * Get all user badges.
     */
    public function getBadges(): array
    {
        return $this->badges ?? [];
    }

    /**
     * Check if user has a specific badge.
     */
    public function hasBadge(string $badge): bool
    {
        return in_array($badge, $this->getBadges());
    }

    /**
     * Add a badge to the user.
     */
    public function addBadge(string $badge): bool
    {
        $badges = $this->getBadges();
        
        if (!in_array($badge, $badges)) {
            $badges[] = $badge;
            $this->badges = $badges;
            return $this->save();
        }
        
        return false;
    }

    /**
     * Remove a badge from the user.
     */
    public function removeBadge(string $badge): bool
    {
        $badges = $this->getBadges();
        $key = array_search($badge, $badges);
        
        if ($key !== false) {
            unset($badges[$key]);
            $this->badges = array_values($badges); // Reindex array
            return $this->save();
        }
        
        return false;
    }

    /**
     * Make user a season supporter.
     */
    public function makeSeasonSupporter(): bool
    {
        if (!$this->is_season_supporter) {
            $this->is_season_supporter = true;
            $this->supporter_since = now();
            $this->addBadge('season-supporter');
            return $this->save();
        }
        
        return false;
    }

    /**
     * Get detailed statistics for the user.
     */
    public function getDetailedStats(?int $season = null): array
    {
        $query = $this->predictions()->where('status', 'scored');
        
        if ($season) {
            $query->where('season', $season);
        }

        $predictions = $query->get();
        
        if ($predictions->isEmpty()) {
            return [
                'total_predictions' => 0,
                'total_score' => 0,
                'avg_score' => 0,
                'accuracy' => 0,
                'best_score' => 0,
                'perfect_predictions' => 0,
                'top_3_count' => 0,
                'bottom_3_count' => 0,
                'points_over_time' => [],
                'accuracy_over_time' => [],
                'race_performance' => [],
            ];
        }

        // Basic stats
        $totalScore = $predictions->sum('score');
        $avgScore = $predictions->avg('score');
        $bestScore = $predictions->max('score');
        $accuracy = $predictions->avg('accuracy') ?? 0;

        // Perfect predictions (score >= 25 per driver)
        $perfectPredictions = $predictions->filter(function ($p) {
            return $p->score >= 25; // Assuming 20 drivers, 25 points each = 500 for perfect
        })->count();

        // Top 3 leaderboard appearances (for season stats)
        // This is a simplified version - in production, you'd query the actual leaderboard
        
        // Points progression over time
        $pointsOverTime = $predictions
            ->sortBy('scored_at')
            ->map(function ($p) use (&$runningTotal) {
                if (!isset($runningTotal)) {
                    $runningTotal = 0;
                }
                $runningTotal += $p->score;
                return [
                    'date' => $p->scored_at ? $p->scored_at->format('Y-m-d') : null,
                    'race' => $p->race_round ?? 'N/A',
                    'score' => $p->score,
                    'total' => $runningTotal,
                ];
            })
            ->values()
            ->toArray();

        // Accuracy over time
        $accuracyOverTime = $predictions
            ->sortBy('scored_at')
            ->map(function ($p) {
                return [
                    'date' => $p->scored_at ? $p->scored_at->format('Y-m-d') : null,
                    'race' => $p->race_round ?? 'N/A',
                    'accuracy' => (float) ($p->accuracy ?? 0),
                ];
            })
            ->values()
            ->toArray();

        // Race performance breakdown
        $racePerformance = $predictions
            ->groupBy('race_round')
            ->map(function ($racePredictions) {
                return [
                    'race_round' => $racePredictions->first()->race_round,
                    'score' => $racePredictions->sum('score'),
                    'accuracy' => $racePredictions->avg('accuracy') ?? 0,
                    'count' => $racePredictions->count(),
                ];
            })
            ->sortBy('race_round')
            ->values()
            ->toArray();

        return [
            'total_predictions' => $predictions->count(),
            'total_score' => $totalScore,
            'avg_score' => round($avgScore, 2),
            'accuracy' => round($accuracy, 2),
            'best_score' => $bestScore,
            'perfect_predictions' => $perfectPredictions,
            'top_3_count' => 0, // TODO: Implement actual leaderboard position tracking
            'bottom_3_count' => 0, // TODO: Implement actual leaderboard position tracking
            'points_over_time' => $pointsOverTime,
            'accuracy_over_time' => $accuracyOverTime,
            'race_performance' => $racePerformance,
        ];
    }

    /**
     * Get position heatmap data (for Pro Stats visualization).
     */
    public function getPositionHeatmapData(?int $season = null): array
    {
        $query = $this->predictions()
            ->where('type', 'race')
            ->where('status', 'scored');
        
        if ($season) {
            $query->where('season', $season);
        }

        $predictions = $query->get();

        $heatmap = [];
        
        foreach ($predictions as $prediction) {
            $predictedOrder = $prediction->getPredictedDriverOrder();
            $raceResults = $prediction->race ? $prediction->race->getResultsArray() : [];
            
            if (empty($raceResults)) {
                continue;
            }

            // Build position accuracy matrix
            foreach ($predictedOrder as $predictedPosition => $driverId) {
                $actualPosition = $this->findDriverPosition($driverId, $raceResults);
                
                if ($actualPosition !== null) {
                    $diff = abs($predictedPosition - $actualPosition);
                    
                    if (!isset($heatmap[$predictedPosition][$actualPosition])) {
                        $heatmap[$predictedPosition][$actualPosition] = 0;
                    }
                    
                    $heatmap[$predictedPosition][$actualPosition]++;
                }
            }
        }

        // Normalize heatmap to percentages
        $normalizedHeatmap = [];
        foreach ($heatmap as $predictedPos => $actualPositions) {
            $total = array_sum($actualPositions);
            foreach ($actualPositions as $actualPos => $count) {
                $normalizedHeatmap[] = [
                    'predicted_position' => $predictedPos,
                    'actual_position' => $actualPos,
                    'count' => $count,
                    'percentage' => round(($count / $total) * 100, 1),
                ];
            }
        }

        return $normalizedHeatmap;
    }

    /**
     * Find the actual position of a driver in race results.
     */
    private function findDriverPosition(string $driverId, array $results): ?int
    {
        foreach ($results as $position => $result) {
            if (($result['driver']['driverId'] ?? '') === $driverId) {
                return $position;
            }
        }
        return null;
    }

    /**
     * Get accuracy trends over time.
     */
    public function getAccuracyTrends(?int $season = null): array
    {
        $query = $this->predictions()
            ->where('type', 'race')
            ->where('status', 'scored');
        
        if ($season) {
            $query->where('season', $season);
        }

        $predictions = $query->orderBy('scored_at')->get();

        $trends = [];
        $windowSize = 5; // Moving average window
        $accuracies = $predictions->pluck('accuracy')->map(fn($a) => (float) $a)->toArray();

        for ($i = 0; $i < count($accuracies); $i++) {
            $window = array_slice($accuracies, max(0, $i - $windowSize + 1), $windowSize);
            $avg = count($window) > 0 ? array_sum($window) / count($window) : 0;
            
            $trends[] = [
                'index' => $i,
                'race' => $predictions[$i]->race_round ?? 'N/A',
                'accuracy' => $accuracies[$i],
                'moving_avg' => round($avg, 2),
            ];
        }

        return $trends;
    }
}
