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
        Schema::create('crm_alert_rules', function (Blueprint $table) {
            $table->id();
            $table->string('rule_name');
            $table->string('condition_type'); // e.g., sla_overdue_days, nps_threshold
            $table->string('threshold_value'); // e.g., 14, 70
            $table->string('action')->default('system_alert_feed');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_alert_rules');
    }
};
