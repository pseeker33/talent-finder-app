<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\Match;
use Illuminate\Support\Facades\Cache;
use App\Jobs\GenerateMatchReport;
use App\Events\NewMatchFound;

class MatchingService
{
    /**
     * @var AIService
     */
    protected $aiService;

    /**
     * @var LinkedInService
     */
    protected $linkedInService;

    public function __construct(AIService $aiService, LinkedInService $linkedInService)
    {
        $this->aiService = $aiService;
        $this->linkedInService = $linkedInService;
    }

    /**
     * Find matches for a given profile based on criteria
     *
     * @param Profile $profile
     * @param array $criteria
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findMatches(Profile $profile, array $criteria)
    {
        $cacheKey = "matches_{$profile->id}_" . md5(json_encode($criteria));

        return Cache::remember($cacheKey, now()->addHours(1), function () use ($profile, $criteria) {
            $query = Profile::query()
                ->where('id', '!=', $profile->id)
                ->when($criteria['location'] ?? null, function ($query, $location) {
                    return $query->where('location', $location);
                })
                ->when($criteria['profile_type'] ?? null, function ($query, $type) {
                    return $query->where('profile_type', $type);
                });

            $matches = $query->get()->map(function ($targetProfile) use ($profile) {
                $score = $this->calculateScore($profile, $targetProfile);
                return [
                    'profile' => $targetProfile,
                    'score' => $score
                ];
            })->sortByDesc('score')
            ->take(10);

            // Dispatch job to generate detailed report
            GenerateMatchReport::dispatch($profile, $matches);

            // Trigger event for real-time notifications
            event(new NewMatchFound($profile, $matches->first()));

            return $matches;
        });
    }

    /**
     * Calculate matching score between two profiles
     *
     * @param Profile $sourceProfile
     * @param Profile $targetProfile
     * @return float
     */
    public function calculateScore(Profile $sourceProfile, Profile $targetProfile): float
    {
        $weights = config('matching.weights');
        $score = 0;

        // Skills matching (using AI service for semantic comparison)
        $skillsScore = $this->aiService->compareSkills(
            $sourceProfile->skills,
            $targetProfile->skills
        );
        $score += $skillsScore * $weights['skills'];

        // Experience level matching
        $experienceScore = $this->calculateExperienceScore(
            $sourceProfile->experience_years,
            $targetProfile->experience_years
        );
        $score += $experienceScore * $weights['experience'];

        // Location matching if required
        if ($sourceProfile->location) {
            $locationScore = $this->calculateLocationScore(
                $sourceProfile->location,
                $targetProfile->location
            );
            $score += $locationScore * $weights['location'];
        }

        // Normalize final score to 0-100 range
        return min(100, max(0, $score));
    }

    /**
     * Calculate experience compatibility score
     */
    private function calculateExperienceScore(int $sourceYears, int $targetYears): float
    {
        $difference = abs($sourceYears - $targetYears);
        return max(0, 100 - ($difference * 10));
    }

    /**
     * Calculate location compatibility score
     */
    private function calculateLocationScore(string $sourceLocation, string $targetLocation): float
    {
        // Basic implementation - can be enhanced with actual distance calculation
        return $sourceLocation === $targetLocation ? 100 : 0;
    }
}