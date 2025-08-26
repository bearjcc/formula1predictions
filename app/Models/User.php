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
            ->whereNotNull('accuracy')
            ->get();

        if ($scoredPredictions->isEmpty()) {
            return 0.0;
        }

        $totalAccuracy = $scoredPredictions->sum('accuracy');
        return $totalAccuracy / $scoredPredictions->count();
    }

    /**
     * Get the user's total prediction score.
     */
    public function getTotalScore(): int
    {
        return $this->predictions()
            ->where('status', 'scored')
            ->sum('score');
    }

    /**
     * Get the user's score for a specific season.
     */
    public function getSeasonScore(int $season): int
    {
        return $this->predictions()
            ->where('status', 'scored')
            ->where('season', $season)
            ->sum('score');
    }

    /**
     * Get the user's accuracy for a specific season.
     */
    public function getSeasonAccuracy(int $season): float
    {
        $scoredPredictions = $this->predictions()
            ->where('status', 'scored')
            ->where('season', $season)
            ->whereNotNull('accuracy')
            ->get();

        if ($scoredPredictions->isEmpty()) {
            return 0.0;
        }

        $totalAccuracy = $scoredPredictions->sum('accuracy');
        return $totalAccuracy / $scoredPredictions->count();
    }

    /**
     * Get the user's total number of predictions.
     */
    public function getTotalPredictionsCount(): int
    {
        return $this->predictions()->count();
    }

    /**
     * Get the user's scored predictions count.
     */
    public function getScoredPredictionsCount(): int
    {
        return $this->predictions()
            ->where('status', 'scored')
            ->count();
    }

    /**
     * Get the user's predictions for a specific season.
     */
    public function getSeasonPredictions(int $season): \Illuminate\Database\Eloquent\Collection
    {
        return $this->predictions()
            ->where('season', $season)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get the user's race predictions.
     */
    public function getRacePredictions(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->predictions()
            ->where('type', 'race')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get the user's preseason predictions.
     */
    public function getPreseasonPredictions(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->predictions()
            ->where('type', 'preseason')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get the user's midseason predictions.
     */
    public function getMidseasonPredictions(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->predictions()
            ->where('type', 'midseason')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get the user's best prediction score.
     */
    public function getBestPredictionScore(): int
    {
        return $this->predictions()
            ->where('status', 'scored')
            ->max('score') ?? 0;
    }

    /**
     * Get the user's average prediction score.
     */
    public function getAveragePredictionScore(): float
    {
        $scoredPredictions = $this->predictions()
            ->where('status', 'scored')
            ->get();

        if ($scoredPredictions->isEmpty()) {
            return 0.0;
        }

        return $scoredPredictions->avg('score');
    }

    /**
     * Get the user's prediction statistics.
     */
    public function getPredictionStats(): array
    {
        return [
            'total_predictions' => $this->getTotalPredictionsCount(),
            'scored_predictions' => $this->getScoredPredictionsCount(),
            'total_score' => $this->getTotalScore(),
            'average_accuracy' => $this->getPredictionAccuracy(),
            'best_score' => $this->getBestPredictionScore(),
            'average_score' => $this->getAveragePredictionScore(),
        ];
    }
}
