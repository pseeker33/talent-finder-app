<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProfileMatch extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'source_profile_id',
        'target_profile_id',
        'matching_score',
        'matching_details',
        'status'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'matching_score' => 'float',
        'matching_details' => 'array',
    ];

    /**
     * Get the source profile associated with the match.
     */
    public function sourceProfile()
    {
        return $this->belongsTo(Profile::class, 'source_profile_id');
    }

    /**
     * Get the target profile associated with the match.
     */
    public function targetProfile()
    {
        return $this->belongsTo(Profile::class, 'target_profile_id');
    }

    /**
     * Scope a query to only include pending matches.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include accepted matches.
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope a query to only include rejected matches.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Check if the match is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the match is accepted.
     */
    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    /**
     * Check if the match is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Accept the match.
     */
    public function accept(): bool
    {
        return $this->update(['status' => 'accepted']);
    }

    /**
     * Reject the match.
     */
    public function reject(): bool
    {
        return $this->update(['status' => 'rejected']);
    }
}