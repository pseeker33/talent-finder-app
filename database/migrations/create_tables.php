<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Profiles table
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('linkedin_id')->nullable()->unique();
            $table->text('professional_summary')->nullable();
            $table->json('skills')->nullable();
            $table->integer('experience_years')->default(0);
            $table->string('location')->nullable();
            $table->string('profile_type')->nullable();
            $table->string('availability_status')->default('available');
            $table->string('collaboration_type')->nullable();
            $table->json('preferences')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        // Questions table
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->text('question');
            $table->json('options')->nullable();
            $table->integer('step');
            $table->timestamps();
        });

        // Answers table
        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->text('answer');
            $table->timestamps();
        });

        // Profle matches table
        Schema::create('profile_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_profile_id')->constrained('profiles')->onDelete('cascade');
            $table->foreignId('target_profile_id')->constrained('profiles')->onDelete('cascade');
            $table->float('matching_score');
            $table->json('matching_details')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();

            // Add index for performance
            $table->index(['source_profile_id', 'target_profile_id']);
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('matches');
        Schema::dropIfExists('answers');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('profiles');
    }
};