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
        Schema::table('analysis_types', function (Blueprint $table) {
            $table->boolean('include_hygiene_score')->default(false)->after('active');
            $table->boolean('include_sanitizer_efficiency')->default(false)->after('include_hygiene_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analysis_types', function (Blueprint $table) {
            $table->dropColumn(['include_hygiene_score', 'include_sanitizer_efficiency']);
        });
    }
};
