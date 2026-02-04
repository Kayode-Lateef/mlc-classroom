<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('homework_topics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('subject')->nullable()->comment('Related subject area');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('subject');
            $table->index('is_active');
        });

        // Pivot table for many-to-many relationship
        Schema::create('homework_assignment_topic', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homework_assignment_id')->constrained('homework_assignments')->onDelete('cascade');
            $table->foreignId('homework_topic_id')->constrained('homework_topics')->onDelete('cascade');
            $table->timestamps();

            // Indexes and unique constraint
            $table->index('homework_assignment_id');
            $table->index('homework_topic_id');
            $table->unique(['homework_assignment_id', 'homework_topic_id'], 'unique_assignment_topic');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homework_assignment_topic');
        Schema::dropIfExists('homework_topics');
    }
};