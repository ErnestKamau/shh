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
        if (!Schema::hasColumn('sampling_schedules', 'is_collected')) {
            Schema::table('sampling_schedules', function (Blueprint $table) {
                $table->boolean('is_collected')->default(false);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('sampling_schedules', 'is_collected')) {
            Schema::table('sampling_schedules', function (Blueprint $table) {
                $table->dropColumn('is_collected');
            });
        }
    }
};
