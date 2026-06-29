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
        Schema::create('crm_dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('description')->nullable();
            $table->string('type')->default('bar'); // e.g. bar, line, doughnut, horizontalBar, kpi_card
            $table->string('data_source'); // e.g. samples_trend, issue_types
            $table->json('ui_config')->nullable(); // Stores colors, custom scales, format targets
            $table->integer('position_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_dashboard_widgets');
    }
};
