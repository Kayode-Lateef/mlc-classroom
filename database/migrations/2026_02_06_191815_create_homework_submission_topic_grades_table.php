<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homework_submission_topic_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homework_submission_id')
                ->constrained('homework_submissions')
                ->onDelete('cascade');
            $table->foreignId('homework_topic_id')
                ->constrained('homework_topics')
                ->onDelete('cascade');
            $table->unsignedInteger('score')->comment('Student score for this topic');
            $table->unsignedInteger('max_score')->comment('Maximum possible score');
            $table->text('comments')->nullable()->comment('Optional per-topic comments');
            $table->foreignId('graded_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->dateTime('graded_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('homework_submission_id', 'idx_sub_topic_submission');
            $table->index('homework_topic_id', 'idx_sub_topic_topic');
            $table->index('graded_by', 'idx_sub_topic_graded_by');

            // Prevent duplicate grades for same submission + topic
            $table->unique(
                ['homework_submission_id', 'homework_topic_id'],
                'unique_submission_topic_grade'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homework_submission_topic_grades');
    }
};