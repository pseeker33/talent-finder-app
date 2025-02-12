<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\LinkedInService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessLinkedInProfile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle(LinkedInService $linkedInService)
    {
        // Fetch and process LinkedIn profile data
        $profileData = $linkedInService->getFullProfile($this->user);

        // Update or create user profile
        $profile = $this->user->profile()->updateOrCreate(
            ['user_id' => $this->user->id],
            [
                'headline' => $profileData['headline'],
                'summary' => $profileData['summary'],
                'industry' => $profileData['industry'],
                'location' => $profileData['location'],
                'skills' => $profileData['skills'],
                'experience' => $profileData['experience'],
                'education' => $profileData['education']
            ]
        );

        // Process skills and experience for matching
        $linkedInService->processProfileMetadata($profile);
    }
}