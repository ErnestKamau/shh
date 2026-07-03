<?php

use Illuminate\Database\Migrations\Migration;

/**
 * NOTE: Superseded by 2026_05_02_120000_create_user_zone_lab_directorate_relation_tables.php,
 * which already creates the same UUID relation tables with hasTable guards.
 * Kept as a tracked no-op to preserve migration history integrity.
 */
return new class extends Migration
{
    public function up(): void
    {
        // No-op: user_zone_relation, user_directorate_relation, and user_lab_relation
        // are created in 2026_05_02_120000_create_user_zone_lab_directorate_relation_tables.
    }

    public function down(): void
    {
        // No-op: rollback is handled by 2026_05_02_120000_create_user_zone_lab_directorate_relation_tables.
    }
};
