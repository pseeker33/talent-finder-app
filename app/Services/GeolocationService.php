<?php

namespace App\Services;

use App\Models\Profile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class GeolocationService
{
    protected $apiKey;
    
    public function __construct()
    {
        $this->apiKey = config('services.google.maps_api_key');
    }

    /**
     * Calculate distance between two profiles
     */
    public function calculateDistance(Profile $profile1, Profile $profile2): float
    {
        $coords1 = $this->getCoordinates($profile1->location);
        $coords2 = $this->getCoordinates($profile2->location);

        return $this->haversineDistance(
            $coords1['lat'],
            $coords1['lng'],
            $coords2['lat'],
            $coords2['lng']
        );
    }

    /**
     * Get coordinates for a location string
     */
    protected function getCoordinates(string $location): array
    {
        $cacheKey = "coords_" . md5($location);

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($location) {
            $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => $location,
                'key' => $this->apiKey
            ]);

            $data = $response->json();

            if (empty($data['results'])) {
                return ['lat' => 0, 'lng' => 0];
            }

            $location = $data['results'][0]['geometry']['location'];
            return [
                'lat' => $location['lat'],
                'lng' => $location['lng']
            ];
        });
    }

    /**
     * Calculate haversine distance between two points
     */
    protected function haversineDistance(
        float $lat1,
        float $lon1,
        float $lat2,
        float $lon2
    ): float {
        $earthRadius = 6371; // km

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDelta / 2) * sin($lonDelta / 2);
            
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Find profiles within radius
     */
    public function findProfilesWithinRadius(Profile $centerProfile, float $radiusKm)
    {
        return Profile::where('id', '!=', $centerProfile->id)
            ->get()
            ->filter(function ($profile) use ($centerProfile, $radiusKm) {
                return $this->calculateDistance($centerProfile, $profile) <= $radiusKm;
            });
    }
}