<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'interview_id',
        'question_id',
        'candidate_id',
        'answer_text',
        'video_url',
        'audio_url',
        'submission_time',
        'status',
    ];

    protected $casts = [
        'submission_time' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Submission status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_REVIEWED = 'reviewed';

    /**
     * Get the interview this submission belongs to
     */
    public function interview()
    {
        return $this->belongsTo(Interview::class);
    }

    /**
     * Get the question this submission answers
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the candidate who made this submission
     */
    public function candidate()
    {
        return $this->belongsTo(User::class, 'candidate_id');
    }

    /**
     * Get all scores for this submission
     */
    public function scores()
    {
        return $this->hasMany(Score::class);
    }

    /**
     * Check if submission is pending
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if submission is submitted
     */
    public function isSubmitted()
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    /**
     * Check if submission is under review
     */
    public function isUnderReview()
    {
        return $this->status === self::STATUS_UNDER_REVIEW;
    }

    /**
     * Check if submission is reviewed
     */
    public function isReviewed()
    {
        return $this->status === self::STATUS_REVIEWED;
    }

    /**
     * Get the answer content based on question type
     */
    public function getAnswerContent()
    {
        if ($this->question->isVideoType()) {
            return $this->video_url;
        } elseif ($this->question->isAudioType()) {
            return $this->audio_url;
        } else {
            return $this->answer_text;
        }
    }
}
