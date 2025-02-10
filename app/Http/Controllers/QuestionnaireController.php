<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Answer;
use App\Services\MatchingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class QuestionnaireController extends Controller
{
    protected $matchingService;

    public function __construct(MatchingService $matchingService)
    {
        $this->matchingService = $matchingService;
    }

    /**
     * Show the questionnaire page
     */
    public function show()
    {
        return view('questionnaire.index', [
            'totalSteps' => config('questionnaire.total_steps'),
            'currentStep' => session('current_step', 1)
        ]);
    }

    /**
     * Get questions for a specific step
     */
    public function getStep(Request $request, int $step)
    {
        $questions = $this->getQuestionsForStep($step);
        
        return response()->json([
            'questions' => $questions,
            'progress' => ($step / config('questionnaire.total_steps')) * 100
        ]);
    }

    /**
     * Store answers for the current step
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'step' => 'required|integer',
            'answers' => 'required|array',
            'answers.*' => 'required|string'
        ]);

        $profile = $request->user()->profile;
        
        // Store answers
        foreach ($validated['answers'] as $questionId => $answer) {
            Answer::updateOrCreate(
                ['profile_id' => $profile->id, 'question_id' => $questionId],
                ['answer' => $answer]
            );
        }

        // Update profile preferences based on answers
        $this->updateProfilePreferences($profile, $validated['answers']);

        // Check if this was the last step
        if ($validated['step'] >= config('questionnaire.total_steps')) {
            // Trigger matching process
            $this->matchingService->findMatches($profile, $this->buildMatchingCriteria($profile));
            return response()->json(['redirect' => route('matches.index')]);
        }

        return response()->json([
            'nextStep' => $validated['step'] + 1
        ]);
    }

    /**
     * Get questions for a specific step
     */
    private function getQuestionsForStep(int $step): array
    {
        $cacheKey = "questions_step_{$step}";

        return Cache::remember($cacheKey, now()->addDay(), function () use ($step) {
            switch ($step) {
                case 1:
                    return [
                        [
                            'id' => 'business_type',
                            'type' => 'select',
                            'question' => 'What type of business are you running?',
                            'options' => [
                                'tech_startup' => 'Technology Startup',
                                'service_business' => 'Service Business',
                                'product_company' => 'Product Company',
                                'consulting' => 'Consulting Firm'
                            ]
                        ],
                        [
                            'id' => 'business_stage',
                            'type' => 'select',
                            'question' => 'What stage is your business in?',
                            'options' => [
                                'idea' => 'Idea Stage',
                                'mvp' => 'MVP Stage',
                                'growth' => 'Growth Stage',
                                'scaling' => 'Scaling Stage'
                            ]
                        ]
                    ];
                case 2:
                    return [
                        [
                            'id' => 'collaboration_type',
                            'type' => 'radio',
                            'question' => 'What type of collaboration are you looking for?',
                            'options' => [
                                'partner' => 'Business Partner',
                                'collaborator' => 'Team Member/Collaborator'
                            ]
                        ]
                    ];
                case 3:
                    return [
                        [
                            'id' => 'location_preference',
                            'type' => 'select',
                            'question' => 'What are your location preferences?',
                            'options' => [
                                'same_city' => 'Same City',
                                'same_country' => 'Same Country',
                                'remote' => 'Remote - Location Doesn\'t Matter'
                            ]
                        ]
                    ];
                // Add more steps as needed
                default:
                    return [];
            }
        });
    }

    /**
     * Update profile preferences based on questionnaire answers
     */
    private function updateProfilePreferences($profile, array $answers)
    {
        $preferences = $profile->preferences ?? [];
        $preferences = array_merge($preferences, $answers);
        $profile->update(['preferences' => $preferences]);
    }

    /**
     * Build matching criteria based on profile and answers
     */
    private function buildMatchingCriteria($profile): array
    {
        $preferences = $profile->preferences;
        
        return [
            'location' => $preferences['location_preference'] ?? null,
            'collaboration_type' => $preferences['collaboration_type'] ?? null,
            'business_type' => $preferences['business_type'] ?? null,
            'profile_type' => $preferences['profile_type'] ?? null,
        ];
    }
}