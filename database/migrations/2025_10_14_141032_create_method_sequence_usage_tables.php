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
        // Add batch_number field to existing media usage table
        Schema::table('method_sequence_stage_media_usage', function (Blueprint $table) {
            $table->string('batch_number')->nullable()->after('unit');
        });

        // Add batch_number field to existing control usage table  
        Schema::table('method_sequence_stage_control_usage', function (Blueprint $table) {
            $table->string('batch_number')->nullable()->after('unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('method_sequence_stage_media_usage', function (Blueprint $table) {
            $table->dropColumn('batch_number');
        });

        Schema::table('method_sequence_stage_control_usage', function (Blueprint $table) {
            $table->dropColumn('batch_number');
        });
    }
};
