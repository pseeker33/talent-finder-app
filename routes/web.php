<?php

use App\Http\Controllers\{
    ProfileController,
    QuestionnaireController,
    MatchingController,
    ChatController,
    Auth\LinkedInAuthController
};

// del archivo original al crearse el proyecto
Route::get('/', function () {
    return view('welcome');
});

// del archivo original al crearse el proyecto
Route::get('/dashboard', function () { 
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware(['auth'])->group(function () {
    // Questionnaire routes
    Route::get('/questionnaire', [QuestionnaireController::class, 'show'])->name('questionnaire.show');
    Route::post('/questionnaire/store', [QuestionnaireController::class, 'store'])->name('questionnaire.store');
    Route::get('/questionnaire/step/{step}', [QuestionnaireController::class, 'getStep'])->name('questionnaire.step');

    // Profile routes
    
    // Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit'); // del original al crearse el proyecto
    // Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update'); // del original al crearse el proyecto
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
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

require __DIR__.'/auth.php'; // del archivo original al crearse el proyecto