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
        Schema::table('users', function (Blueprint $table) {
            // Add role column after email
            $table->enum('role', ['superadmin', 'admin', 'teacher', 'parent'])
                  ->after('email')
                  ->comment('User role in the system');
            
            // Add phone number for SMS notifications
            $table->string('phone', 20)
                  ->nullable()
                  ->after('role')
                  ->comment('UK format: +44...');
            
            // Add profile photo
            $table->string('profile_photo', 255)
                  ->nullable()
                  ->after('phone')
                  ->comment('Path to user profile photo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'profile_photo']);
        });
    }
};