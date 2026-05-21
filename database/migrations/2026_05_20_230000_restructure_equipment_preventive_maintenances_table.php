<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment_preventive_maintenances', function (Blueprint $table) {
            // Drop old quarter status columns
            $table->dropColumn(['q1_status', 'q2_status', 'q3_status', 'q4_status', 'year_start']);

            // New: scheduled month number (1-12) within the maintenance year
            $table->unsignedTinyInteger('scheduled_month')->nullable()->comment('Month number (1-12) within the maintenance period year');
            // New: has the maintenance been carried out?
            $table->boolean('is_serviced')->default(false);
            // New: when it was actually serviced
            $table->date('serviced_date')->nullable();
            // New: optional note
            $table->string('notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('equipment_preventive_maintenances', function (Blueprint $table) {
            $table->dropColumn(['scheduled_month', 'is_serviced', 'serviced_date', 'notes']);

            $table->date('year_start')->nullable();
            $table->string('q1_status')->nullable();
            $table->string('q2_status')->nullable();
            $table->string('q3_status')->nullable();
            $table->string('q4_status')->nullable();
        });
    }
};
