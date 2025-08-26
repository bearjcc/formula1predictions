<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prediction extends Model
{
    /** @use HasFactory<\Database\Factories\PredictionFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * 
     * TODO: Define fillable fields for predictions
     * TODO: Add validation rules for prediction data
     * TODO: Consider prediction status (draft, submitted, locked)
     * TODO: Add prediction scoring fields
     */
    protected $fillable = [
        // TODO: Add fillable fields
    ];

    /**
     * Get the user that owns the prediction.
     * 
     * TODO: Add user relationship
     * TODO: Add race relationship
     * TODO: Add driver relationships for predictions
     * TODO: Add team relationships for predictions
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the race for this prediction.
     * 
     * TODO: Implement race relationship
     * TODO: Add race validation (can't predict past races)
     * TODO: Add prediction deadline logic
     */
    public function race(): BelongsTo
    {
        // TODO: Return race relationship when Race model is created
        // return $this->belongsTo(Race::class);
        throw new \Exception('Race relationship not yet implemented');
    }

    /**
     * Calculate the prediction score.
     * 
     * TODO: Implement scoring algorithm
     * TODO: Add points for correct positions
     * TODO: Add bonus points for perfect predictions
     * TODO: Consider qualifying vs race predictions
     */
    public function calculateScore(): int
    {
        // TODO: Calculate and return prediction score
        return 0;
    }

    /**
     * Check if prediction is still editable.
     * 
     * TODO: Implement deadline checking logic
     * TODO: Add race start time validation
     * TODO: Consider timezone handling
     */
    public function isEditable(): bool
    {
        // TODO: Check if prediction can still be edited
        return true;
    }
}
