<?php

use Illuminate\Database\Migrations\Migration;

/**
 * NOTE: Superseded by 2026_05_08_215000_create_lab_tat_reporting_views.php,
 * which already defines v_lab_tat_stage_summary with completed_batches.
 * Kept as a tracked no-op to preserve migration history integrity.
 */
return new class extends Migration
{
    public function up(): void
    {
        // No-op: v_lab_tat_stage_summary includes completed_batches in 2026_05_08_215000.
    }

    public function down(): void
    {
        // No-op: rollback is handled by 2026_05_08_215000_create_lab_tat_reporting_views.
    }
};
