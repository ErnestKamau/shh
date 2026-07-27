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
        if (! Schema::hasTable('sampling_schedules')) {
            return;
        }

        Schema::table('sampling_schedules', function (Blueprint $table) {
            if (! Schema::hasColumn('sampling_schedules', 'sample_point_id')) {
                $table->uuid('sample_point_id')->nullable()->after('location')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('sampling_schedules')) {
            return;
        }

        Schema::table('sampling_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('sampling_schedules', 'sample_point_id')) {
                $table->dropColumn('sample_point_id');
            }
        });
    }
};
