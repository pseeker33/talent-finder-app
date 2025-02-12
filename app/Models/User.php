<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'linkedin_id',
        'linkedin_token',
        'preferences',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'linkedin_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'preferences' => 'array',
    ];

    /**
     * Get the profile associated with the user.
     */
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Get the user's questionnaire answers.
     */
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * Get the user's saved profile matches.
     */
    public function savedMatches()
    {
        return $this->hasMany(ProfileMatch::class);
    }

    /**
     * Get the user's chat history.
     */
    public function chatHistory()
    {
        return $this->hasMany(ChatHistory::class);
    }

    /**
     * Check if user has completed the onboarding questionnaire.
     */
    public function hasCompletedQuestionnaire(): bool
    {
        return $this->answers()
            ->whereIn('question_id', Question::required()->pluck('id'))
            ->count() === Question::required()->count();
    }
}