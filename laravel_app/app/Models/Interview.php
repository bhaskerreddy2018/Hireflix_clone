<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interview extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'created_by',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Interview status constants
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ARCHIVED = 'archived';

    /**
     * Get the user who created this interview
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all questions for this interview
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Get all submissions for this interview
     */
    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    /**
     * Check if interview is active
     */
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if interview is draft
     */
    public function isDraft()
    {
        return $this->status === self::STATUS_DRAFT;
    }
}
