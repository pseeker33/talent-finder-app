<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class AIService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.openai.com/v1';

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
    }

    /**
     * Compare skills using semantic analysis
     */
    public function compareSkills(array $sourceSkills, array $targetSkills): float
    {
        $cacheKey = 'skills_comparison_' . md5(json_encode([$sourceSkills, $targetSkills]));

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($sourceSkills, $targetSkills) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/embeddings', [
                'input' => [implode(', ', $sourceSkills), implode(', ', $targetSkills)],
                'model' => 'text-embedding-ada-002'
            ]);

            if ($response->successful()) {
                $embeddings = $response->json()['data'];
                return $this->calculateCosineSimilarity(
                    $embeddings[0]['embedding'],
                    $embeddings[1]['embedding']
                );
            }

            return 0.0;
        });
    }

    /**
     * Calculate cosine similarity between two vectors
     */
    private function calculateCosineSimilarity(array $a, array $b): float
    {
        $dotProduct = 0;
        $magnitudeA = 0;
        $magnitudeB = 0;

        foreach ($a as $i => $value) {
            $dotProduct += $value * $b[$i];
            $magnitudeA += $value * $value;
            $magnitudeB += $b[$i] * $b[$i];
        }

        $magnitudeA = sqrt($magnitudeA);
        $magnitudeB = sqrt($magnitudeB);

        return $dotProduct / ($magnitudeA * $magnitudeB);
    }
}