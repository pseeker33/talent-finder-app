<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;

class Profile extends Model
{
    use HasFactory, HasTranslations;

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    public $translatable = ['professional_summary'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'linkedin_id',
        'professional_summary',
        'skills',
        'experience_years',
        'location',
        'profile_type', // technical, business, hybrid
        'availability_status',
        'collaboration_type', // partner, collaborator
        'last_synced_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'skills' => 'array',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Get the user that owns the profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the matches where this profile is the source.
     */
    public function matchesAsSource()
    {
        return $this->hasMany(profileMatch::class, 'source_profile_id');
    }

    /**
     * Get the matches where this profile is the target.
     */
    public function matchesAsTarget()
    {
        return $this->hasMany(profileMatch::class, 'target_profile_id');
    }

    /**
     * Scope a query to only include profiles of a given type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('profile_type', $type);
    }

    /**
     * Calculate the matching score with another profile.
     */
    public function calculateMatchingScore(Profile $targetProfile): float
    {
        // Implementation for matching algorithm
        return app(MatchingService::class)->calculateScore($this, $targetProfile);
    }
}