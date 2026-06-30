<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * NOTE: This migration is superseded by the rename migration
 * (2026_04_20_034902_rename_health_score_to_interaction_score_in_crm_customers).
 * Kept as a tracked no-op to preserve migration history integrity.
 */
return new class extends Migration
{
    public function up(): void
    {
        // No-op: interaction_score is added via the rename of health_score
        // in migration 2026_04_20_034902.
    }

    public function down(): void
    {
        // No-op: rollback is handled by the rename migration's down() method.
    }
};
