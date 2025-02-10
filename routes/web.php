<?php

/*
Register web routes for your application. These routes are loaded by the RouteServiceProvider and all of them will | be assigned to the "web" middleware group.
*/

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    ProfileController,
    QuestionnaireController,
    MatchingController,
    ChatController,
    Auth\LinkedInAuthController
};

Route::middleware(['auth'])->group(function () {
    Route::get('/', function () { return view('welcome'); });

    // Questionnaire routes
    Route::get('/questionnaire', [QuestionnaireController::class, 'show'])->name('questionnaire.show');
    Route::post('/questionnaire/store', [QuestionnaireController::class, 'store'])->name('questionnaire.store');
    Route::get('/questionnaire/step/{step}', [QuestionnaireController::class, 'getStep'])->name('questionnaire.step');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/sync-linkedin', [ProfileController::class, 'syncLinkedIn'])->name('profile.sync-linkedin');

    // Matching routes
    Route::get('/matches', [MatchingController::class, 'index'])->name('matches.index');
    Route::get('/matches/{match}', [MatchingController::class, 'show'])->name('matches.show');
    Route::post('/matches/filter', [MatchingController::class, 'filter'])->name('matches.filter');

    // Chat routes
    Route::get('/chat/{match}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{match}/message', [ChatController::class, 'sendMessage'])->name('chat.message');
});

// LinkedIn OAuth routes
Route::get('auth/linkedin', [LinkedInAuthController::class, 'redirect'])->name('linkedin.redirect');
Route::get('auth/linkedin/callback', [LinkedInAuthController::class, 'callback'])->name('linkedin.callback');



