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
     * 
     * TODO: Implement scoring system
     * TODO: Add point calculation logic
     * TODO: Consider bonus points for perfect predictions
     */
    public function getTotalScore(): int
    {
        // TODO: Calculate and return total prediction score
        return 0;
    }
}
