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
        Schema::table('formula_steps', function (Blueprint $table) {
            $table->boolean('is_end_stage')->default(false)->after('description');
            $table->boolean('is_end_stage_if_pass')->default(false)->after('is_end_stage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('formula_steps', function (Blueprint $table) {
            $table->dropColumn(['is_end_stage', 'is_end_stage_if_pass']);
        });
    }
};
