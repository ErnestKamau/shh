<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lab_sections', function (Blueprint $table) {
            $table->json('reading_frequency_schedule')->nullable()->after('reading_frequency_interval');
        });
    }

    public function down(): void
    {
        Schema::table('lab_sections', function (Blueprint $table) {
            $table->dropColumn('reading_frequency_schedule');
        });
    }
};
