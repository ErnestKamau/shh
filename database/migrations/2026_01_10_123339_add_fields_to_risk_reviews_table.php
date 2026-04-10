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
        Schema::table('risk_reviews', function (Blueprint $table) {
            // KPI Metrics
            $table->text('kpi_metrics')->nullable()->after('new_risks_identified'); // Performance indicators
            
            // Action Required
            $table->boolean('action_required')->default(false)->after('kpi_metrics'); // Triggers reassessment
            $table->text('action_required_reason')->nullable()->after('action_required');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('risk_reviews', function (Blueprint $table) {
            $table->dropColumn([
                'kpi_metrics',
                'action_required',
                'action_required_reason'
            ]);
        });
    }
};
