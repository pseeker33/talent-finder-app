<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Profile;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class LinkedInAuthController extends Controller
{
    /**
     * Redirect to LinkedIn OAuth page
     */
    public function redirect()
    {
        return Socialite::driver('linkedin')->redirect();
    }

    /**
     * Handle LinkedIn callback
     */
    public function callback()
    {
        try {
            DB::beginTransaction();
            
            $linkedinUser = Socialite::driver('linkedin')->user();
            
            $user = User::updateOrCreate(
                ['email' => $linkedinUser->email],
                [
                    'name' => $linkedinUser->name,
                    'password' => encrypt(str_random(24))
                ]
            );

            $profile = Profile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'linkedin_id' => $linkedinUser->id,
                    'professional_summary' => $linkedinUser->user['headline'] ?? null,
                    'last_synced_at' => now()
                ]
            );

            DB::commit();
            
            Auth::login($user);
            
            return redirect()->route('questionnaire.show');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->route('login')
                ->with('error', 'Something went wrong with LinkedIn login');
        }
    }
}