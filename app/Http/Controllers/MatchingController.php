<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\ProfileMatch;
use App\Services\MatchingService;
use App\Services\GeolocationService;
use App\Jobs\GenerateMatchReport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MatchingController extends Controller
{
    protected $matchingService;
    protected $geolocationService;

    public function __construct(
        MatchingService $matchingService,
        GeolocationService $geolocationService
    ) {
        $this->matchingService = $matchingService;
        $this->geolocationService = $geolocationService;
    }

    /**
     * Get matches based on user preferences and profile data
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getMatches(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'location' => 'nullable|string',
            'max_distance' => 'nullable|integer',
            'profile_type' => 'required|in:technical,business,both',
            'collaboration_type' => 'required|in:partner,collaborator',
            'skills' => 'nullable|array',
            'industry' => 'nullable|string',
        ]);

        $matches = $this->matchingService->findMatches(
            $request->user()->profile,
            $filters
        );

        // Dispatch job to generate detailed match report
        GenerateMatchReport::dispatch($request->user(), $matches);

        return response()->json([
            'matches' => $matches,
            'total' => $matches->count(),
            'filters_applied' => $filters
        ]);
    }

    /**
     * Get match details including compatibility score
     * 
     * @param Profile $profile
     * @return JsonResponse
     */
    public function getMatchDetails(Profile $profile): JsonResponse
    {
        $compatibility = $this->matchingService->calculateCompatibility(
            auth()->user()->profile,
            $profile
        );

        return response()->json([
            'profile' => $profile->load('user', 'skills'),
            'compatibility_score' => $compatibility['score'],
            'matching_factors' => $compatibility['factors'],
            'distance' => $this->geolocationService->calculateDistance(
                auth()->user()->profile,
                $profile
            )
        ]);
    }

    /**
     * Save a profile match for later reference
     * 
     * @param Profile $profile
     * @return JsonResponse
     */
    public function saveMatch(Profile $profile): JsonResponse
    {
        $profileMatch = ProfileMatch::create([
            'user_id' => auth()->id(),
            'matched_profile_id' => $profile->id,
            'compatibility_score' => $this->matchingService->calculateCompatibility(
                auth()->user()->profile,
                $profile
            )['score']
        ]);

        return response()->json([
            'message' => 'Match saved successfully',
            'match' => $profileMatch
        ]);
    }
}