<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'interview_id',
        'question_text',
        'question_type',
        'time_limit',
        'order',
        'is_required',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'time_limit' => 'integer',
        'order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Question type constants
     */
    const TYPE_VIDEO = 'video';
    const TYPE_AUDIO = 'audio';
    const TYPE_TEXT = 'text';

    /**
     * Get the interview this question belongs to
     */
    public function interview()
    {
        return $this->belongsTo(Interview::class);
    }

    /**
     * Get all submissions for this question
     */
    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    /**
     * Check if question is video type
     */
    public function isVideoType()
    {
        return $this->question_type === self::TYPE_VIDEO;
    }

    /**
     * Check if question is audio type
     */
    public function isAudioType()
    {
        return $this->question_type === self::TYPE_AUDIO;
    }

    /**
     * Check if question is text type
     */
    public function isTextType()
    {
        return $this->question_type === self::TYPE_TEXT;
    }
}
