<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Matching Algorithm Settings
    |--------------------------------------------------------------------------
    */
    'weights' => [
        'skills_match' => 0.35,
        'experience_match' => 0.25,
        'industry_match' => 0.20,
        'location_match' => 0.10,
        'education_match' => 0.10
    ],

    'minimum_compatibility_score' => 0.60,

    'location_preferences' => [
        'same_city' => 100,
        'same_region' => 200,
        'same_country' => 500,
        'anywhere' => null
    ],

    'cache_duration' => 60 * 24, // 24 hours in minutes
];