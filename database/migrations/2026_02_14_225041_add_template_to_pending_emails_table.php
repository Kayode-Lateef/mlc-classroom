<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pending_emails', function (Blueprint $table) {
            $table->string('template', 100)
                  ->default('emails.notification')
                  ->after('data');
        });
    }

    public function down(): void
    {
        Schema::table('pending_emails', function (Blueprint $table) {
            $table->dropColumn('template');
        });
    }
};