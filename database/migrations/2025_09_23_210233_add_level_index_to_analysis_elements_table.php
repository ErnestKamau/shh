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
        Schema::table('analysis_elements', function (Blueprint $table) {
            // Add index on level column for better performance when ordering
            $table->index('level', 'analysis_elements_level_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analysis_elements', function (Blueprint $table) {
            // Drop the index
            $table->dropIndex('analysis_elements_level_index');
        });
    }
};
