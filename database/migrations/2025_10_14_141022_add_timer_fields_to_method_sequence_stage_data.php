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
        Schema::table('method_sequence_run_stage_data', function (Blueprint $table) {
            $table->decimal('safe_duration_hours', 8, 2)->nullable()->after('status');
            $table->decimal('duration_hours', 8, 2)->nullable()->after('safe_duration_hours');
            $table->boolean('safe_duration_alert_sent')->default(false)->after('duration_hours');
            $table->boolean('duration_alert_sent')->default(false)->after('safe_duration_alert_sent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('method_sequence_run_stage_data', function (Blueprint $table) {
            $table->dropColumn([
                'safe_duration_hours',
                'duration_hours', 
                'safe_duration_alert_sent',
                'duration_alert_sent'
            ]);
        });
    }
};
