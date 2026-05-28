<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monitoring_reading_steps', function (Blueprint $table) {
            $table->boolean('show_in_monitoring_logs')->default(true)->after('variable_slug');
        });
    }

    public function down(): void
    {
        Schema::table('monitoring_reading_steps', function (Blueprint $table) {
            $table->dropColumn('show_in_monitoring_logs');
        });
    }
};

