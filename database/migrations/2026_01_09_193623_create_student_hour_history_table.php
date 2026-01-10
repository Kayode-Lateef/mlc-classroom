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
        Schema::create('student_hour_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->decimal('old_hours', 4, 1)->comment('Previous weekly hours');
            $table->decimal('new_hours', 4, 1)->comment('New weekly hours');
            $table->foreignId('changed_by')->constrained('users')->onDelete('restrict')->comment('User who made the change');
            $table->text('reason')->nullable()->comment('Reason for change');
            $table->timestamp('changed_at')->useCurrent()->comment('When the change was made');
            $table->timestamps();

            // Indexes
            $table->index('student_id', 'idx_hour_history_student');
            $table->index('changed_at', 'idx_hour_history_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_hour_history');
    }
};