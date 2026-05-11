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
            $table->unsignedInteger('preventive_maintainance_period')->nullable()->after('calibration_notification_in_days')->comment('Number of days between preventive maintenance activities');
            $table->unsignedInteger('preventive_maintainance_notification_days')->nullable()->after('preventive_maintainance_period')->comment('Send notification this many days before preventive maintenance is due');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn(['preventive_maintainance_period', 'preventive_maintainance_notification_days']);
        });
    }
};
