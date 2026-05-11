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
        Schema::table('equipment', function (Blueprint $table) {
            $table->boolean('daily_log_monitored_by_another_equipment')
                ->default(false)
                ->after('daily_log_frequency');

            $table->uuid('daily_log_monitored_equipment_id')
                ->nullable()
                ->after('daily_log_monitored_by_another_equipment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn(['daily_log_monitored_by_another_equipment', 'daily_log_monitored_equipment_id']);
        });
    }
};
