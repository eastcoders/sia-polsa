<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('form_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')->constrained('semesters');
            $table->string('title');
            $table->string('category'); // UTS_LAYANAN, UAS_DOSEN
            $table->string('evaluation_target'); // e.g., 'App\Models\Dosen'
            $table->json('schema_snapshot');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('survey_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->morphs('reference'); // reference_type, reference_id
            $table->foreignId('form_template_id')->constrained('form_templates')->cascadeOnDelete();
            $table->string('status')->default('PENDING'); // PENDING, COMPLETED, EXPIRED
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Index for fast lookup in dashboard
            $table->index(['user_id', 'status']);
        });

        Schema::create('response_ballots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_template_id')->constrained('form_templates')->cascadeOnDelete();
            $table->unsignedBigInteger('target_id')->nullable(); // ID of the Dosen/Prodi being rated
            $table->json('answers_full'); // Full JSON storage
            $table->float('calculated_score')->nullable(); // Average score if applicable
            $table->timestamps();

            // Intentionally NO user_id for privacy
            $table->index('target_id');
        });

        Schema::create('response_metric_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('response_ballot_id')->constrained('response_ballots')->cascadeOnDelete();
            $table->string('metric_key'); // e.g., 'punctuality'
            $table->integer('score'); // 1-4

            $table->index('metric_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('response_metric_values');
        Schema::dropIfExists('response_ballots');
        Schema::dropIfExists('survey_tickets');
        Schema::dropIfExists('form_templates');
    }
};
