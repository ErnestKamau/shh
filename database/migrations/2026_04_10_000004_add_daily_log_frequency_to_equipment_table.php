<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->unsignedTinyInteger('daily_log_frequency')->default(1)->after('daily_log_reporting_unit'); // 1=once, 2=twice, etc.
            $table->string('daily_log_time_interval')->nullable()->after('daily_log_frequency'); // hours between readings
        });
    }

    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn(['daily_log_frequency', 'daily_log_time_interval']);
        });
    }
};
