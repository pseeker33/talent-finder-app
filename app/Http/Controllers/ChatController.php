<?php

namespace App\Http\Controllers;

use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Events\NewMatchFound;

class ChatController extends Controller
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Process a chat message and get AI-powered profile recommendations
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function processMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'context' => 'nullable|array'
        ]);

        $response = $this->aiService->analyzeMessage(
            $validated['message'],
            $validated['context'] ?? []
        );

        if ($response['type'] === 'profile_recommendation') {
            event(new NewMatchFound(
                auth()->user(),
                $response['recommended_profiles']
            ));
        }

        return response()->json([
            'response' => $response['message'],
            'recommendations' => $response['recommended_profiles'] ?? [],
            'suggested_actions' => $response['suggested_actions'] ?? []
        ]);
    }

    /**
     * Get chat history for the current user
     * 
     * @return JsonResponse
     */
    public function getChatHistory(): JsonResponse
    {
        $history = auth()->user()
            ->chatHistory()
            ->with('recommendations')
            ->latest()
            ->paginate(15);

        return response()->json($history);
    }

    /**
     * Clear chat history for the current user
     * 
     * @return JsonResponse
     */
    public function clearHistory(): JsonResponse
    {
        auth()->user()->chatHistory()->delete();

        return response()->json([
            'message' => 'Chat history cleared successfully'
        ]);
    }
}