<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * User roles constants
     */
    const ROLE_ADMIN = 'admin';
    const ROLE_REVIEWER = 'reviewer';
    const ROLE_CANDIDATE = 'candidate';

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is reviewer
     */
    public function isReviewer()
    {
        return $this->role === self::ROLE_REVIEWER;
    }

    /**
     * Check if user is candidate
     */
    public function isCandidate()
    {
        return $this->role === self::ROLE_CANDIDATE;
    }

    /**
     * Check if user can review (admin or reviewer)
     */
    public function canReview()
    {
        return $this->isAdmin() || $this->isReviewer();
    }

    /**
     * Get submissions for this user (if candidate)
     */
    public function submissions()
    {
        return $this->hasMany(Submission::class, 'candidate_id');
    }

    /**
     * Get scores given by this user (if reviewer)
     */
    public function scores()
    {
        return $this->hasMany(Score::class, 'reviewer_id');
    }
}
