<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('labs')) {
            return;
        }

        try {
            DB::statement('ALTER TABLE labs DROP CONSTRAINT IF EXISTS labs_directorate_code_unique');
            DB::statement('ALTER TABLE labs DROP CONSTRAINT IF EXISTS labs_directorate_name_unique');
        } catch (\Throwable $e) {
            //
        }

        Schema::table('labs', function (Blueprint $table): void {
            if (Schema::hasColumn('labs', 'zone_id')) {
                $table->dropColumn('zone_id');
            }
            if (Schema::hasColumn('labs', 'directorate_id')) {
                $table->dropColumn('directorate_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('labs')) {
            return;
        }

        Schema::table('labs', function (Blueprint $table): void {
            if (! Schema::hasColumn('labs', 'zone_id')) {
                $table->uuid('zone_id')->nullable()->index();
            }
            if (! Schema::hasColumn('labs', 'directorate_id')) {
                $table->uuid('directorate_id')->nullable()->index();
            }
        });
    }
};
