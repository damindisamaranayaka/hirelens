<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_post_id')->constrained('job_posts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('cv_path');
            $table->text('cover_message')->nullable();
            $table->enum('status', ['pending', 'reviewed', 'shortlisted', 'rejected'])
                ->default('pending');

            // Filled later by the AI service
            $table->unsignedTinyInteger('match_score')->nullable();
            $table->json('matched_skills')->nullable();
            $table->json('missing_skills')->nullable();
            $table->text('ai_feedback')->nullable();

            $table->timestamps();

            // One application per user per job
            $table->unique(['job_post_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};