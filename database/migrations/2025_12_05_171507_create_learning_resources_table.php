<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_resources', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->enum('resource_type', ['pdf', 'video', 'link', 'image', 'document']);
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('restrict')->comment('Teacher ID');
            $table->foreignId('class_id')->nullable()->constrained('classes')->onDelete('cascade')->comment('NULL = general resource');
            $table->string('subject', 100)->nullable();
            $table->timestamps();

            // Indexes
            $table->index('class_id');
            $table->index('subject');
            $table->index('resource_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_resources');
    }
};