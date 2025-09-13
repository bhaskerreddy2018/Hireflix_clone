<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_id',
        'reviewer_id',
        'score',
        'comments',
        'reviewed_at',
    ];

    protected $casts = [
        'score' => 'integer',
        'reviewed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Score range constants
     */
    const MIN_SCORE = 1;
    const MAX_SCORE = 10;

    /**
     * Get the submission this score belongs to
     */
    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    /**
     * Get the reviewer who gave this score
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Scope to get scores by reviewer
     */
    public function scopeByReviewer($query, $reviewerId)
    {
        return $query->where('reviewer_id', $reviewerId);
    }

    /**
     * Scope to get scores for a specific submission
     */
    public function scopeForSubmission($query, $submissionId)
    {
        return $query->where('submission_id', $submissionId);
    }

    /**
     * Check if score is within valid range
     */
    public function isValidScore()
    {
        return $this->score >= self::MIN_SCORE && $this->score <= self::MAX_SCORE;
    }

    /**
     * Get score level description
     */
    public function getScoreLevel()
    {
        if ($this->score >= 9) {
            return 'Excellent';
        } elseif ($this->score >= 7) {
            return 'Good';
        } elseif ($this->score >= 5) {
            return 'Average';
        } elseif ($this->score >= 3) {
            return 'Below Average';
        } else {
            return 'Poor';
        }
    }
}
