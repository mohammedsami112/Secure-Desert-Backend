<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'photo',
        'email',
        'email_verified_at',
        'username',
        'password',
        'country_code',
        'phone_number',
        'firebase_token',
        'is_active',
        'points',
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
        'is_active' => 'boolean',
    ];

    /**
     * Submitted Tasks
     *
     * @return \App\Models\SolvedTask
     */
    public function submittedTasks()
    {
        return $this->hasMany(SolvedTask::class);
    }

    /**
     * Check if user is subscribed
     *
     * @return bool
     */
    public function subscribed()
    {
        return $this->subscription()->exists() && $this->subscription->isActive();
    }

    /**
     * Get the subscription for the user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    public function team()
    {
        return $this->hasOneThrough(Team::class, TeamMember::class, 'user_id', 'id', 'id', 'team_id');
    }

    public function courses()
    {
        return $this->hasManyThrough(Course::class, CourseEnrollment::class, 'user_id', 'id');
    }
}
