<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class NewMatchFound implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $recommendedProfiles;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, Collection $recommendedProfiles)
    {
        $this->user = $user;
        $this->recommendedProfiles = $recommendedProfiles;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->user->id),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'profiles' => $this->recommendedProfiles->map(function ($profile) {
                return [
                    'id' => $profile->id,
                    'name' => $profile->user->name,
                    'headline' => $profile->headline,
                    'compatibility_score' => $profile->compatibility_score,
                    'matched_at' => now()->toISOString()
                ];
            })
        ];
    }
}