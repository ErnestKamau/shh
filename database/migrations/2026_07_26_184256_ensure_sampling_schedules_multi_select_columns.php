<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ensure multi-select / sample-point columns exist even if an earlier
     * migration was recorded without applying the DDL.
     */
    public function up(): void
    {
        if (! Schema::hasTable('sampling_schedules')) {
            return;
        }

        DB::statement('ALTER TABLE sampling_schedules ADD COLUMN IF NOT EXISTS contact_ids JSON NULL');
        DB::statement('ALTER TABLE sampling_schedules ADD COLUMN IF NOT EXISTS personnel_ids JSON NULL');
        DB::statement('ALTER TABLE sampling_schedules ADD COLUMN IF NOT EXISTS sample_point_id UUID NULL');

        // Backfill JSON arrays from legacy single FKs where empty.
        DB::statement("
            UPDATE sampling_schedules
            SET contact_ids = CASE
                WHEN contact_id IS NOT NULL AND (contact_ids IS NULL OR contact_ids::text IN ('null', '[]'))
                    THEN json_build_array(contact_id::text)::json
                ELSE contact_ids
            END
        ");

        DB::statement("
            UPDATE sampling_schedules
            SET personnel_ids = CASE
                WHEN personnel_id IS NOT NULL AND (personnel_ids IS NULL OR personnel_ids::text IN ('null', '[]'))
                    THEN json_build_array(personnel_id::text)::json
                ELSE personnel_ids
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left blank: columns may be required by the application.
    }
};
