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
        Schema::table('sample_analysis_stages', function (Blueprint $table) {
            if (! Schema::hasColumn('sample_analysis_stages', 'requires_sample_preparation')) {
                $table->boolean('requires_sample_preparation')->default(false)->after('active');
            }

            if (! Schema::hasColumn('sample_analysis_stages', 'does_environmental_analysis')) {
                $table->boolean('does_environmental_analysis')->default(false)->after('requires_sample_preparation');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sample_analysis_stages', function (Blueprint $table) {
            if (Schema::hasColumn('sample_analysis_stages', 'does_environmental_analysis')) {
                $table->dropColumn('does_environmental_analysis');
            }

            if (Schema::hasColumn('sample_analysis_stages', 'requires_sample_preparation')) {
                $table->dropColumn('requires_sample_preparation');
            }
        });
    }
};
