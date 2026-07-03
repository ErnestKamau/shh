<?php

use Illuminate\Database\Migrations\Migration;

/**
 * NOTE: Superseded by 2026_05_14_183500_change_sample_interlab_log_columns_to_uuid.php,
 * which recreates samples_inter_lab_log_view after UUID column conversion.
 * Kept as a tracked no-op to preserve migration history integrity.
 */
return new class extends Migration
{
    public function up(): void
    {
        // No-op: view is created when sample_interlab_log UUID migration runs.
    }

    public function down(): void
    {
        // No-op: rollback is handled by 2026_05_14_183500_change_sample_interlab_log_columns_to_uuid.
    }
};
