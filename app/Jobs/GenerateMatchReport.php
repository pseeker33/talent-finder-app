<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Profile;
use App\Services\MatchingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class GenerateMatchReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $matches;
    
    public function __construct(User $user, Collection $matches)
    {
        $this->user = $user;
        $this->matches = $matches;
    }

    public function handle(MatchingService $matchingService)
    {
        $report = [
            'generated_at' => now(),
            'total_matches' => $this->matches->count(),
            'match_details' => $this->matches->map(function ($match) use ($matchingService) {
                $compatibility = $matchingService->calculateCompatibility(
                    $this->user->profile,
                    $match
                );

                return [
                    'profile_id' => $match->id,
                    'compatibility_score' => $compatibility['score'],
                    'matching_factors' => $compatibility['factors'],
                    'skills_overlap' => $this->calculateSkillsOverlap($match),
                    'experience_relevance' => $this->calculateExperienceRelevance($match)
                ];
            }),
            'recommendations' => $matchingService->generateRecommendations(
                $this->user->profile,
                $this->matches
            )
        ];

        // Store the report
        $this->user->matchReports()->create([
            'data' => $report
        ]);
    }

    protected function calculateSkillsOverlap(Profile $match)
    {
        $userSkills = collect($this->user->profile->skills);
        $matchSkills = collect($match->skills);

        return [
            'common_skills' => $userSkills->intersect($matchSkills)->values(),
            'overlap_percentage' => ($userSkills->intersect($matchSkills)->count() / $userSkills->count()) * 100
        ];
    }

    protected function calculateExperienceRelevance(Profile $match)
    {
        // Implement experience relevance calculation logic
        // This could include industry overlap, years of experience comparison, etc.
        return [
            'industry_match' => $this->user->profile->industry === $match->industry,
            'experience_level_match' => abs(
                count($this->user->profile->experience) - count($match->experience)
            ) <= 2
        ];
    }
}