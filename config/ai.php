<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Service Configuration
    |--------------------------------------------------------------------------
    */
    'service_provider' => env('AI_SERVICE_PROVIDER', 'openai'),
    
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4'),
        'temperature' => 0.7,
        'max_tokens' => 300
    ],

    'prompt_templates' => [
        'profile_analysis' => 'Analyze the following professional profile and identify key strengths: {profile}',
        'match_explanation' => 'Explain why these profiles match: {profile1} and {profile2}',
        'skill_requirements' => 'Based on the business description: {description}, what skills would be most valuable?'
    ],

    'chat_context_window' => 10, // Number of previous messages to include for context
    
    'confidence_threshold' => 0.85 // Minimum confidence score for AI recommendations
];