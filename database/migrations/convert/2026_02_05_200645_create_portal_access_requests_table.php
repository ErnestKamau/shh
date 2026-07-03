<?php

use Illuminate\Database\Migrations\Migration;

/**
 * NOTE: Superseded by 2026_04_28_120000_create_portal_access_requests_table.php.
 * Kept as a tracked no-op to preserve migration history integrity.
 */
return new class extends Migration
{
    public function up(): void
    {
        // No-op: canonical table definition is in 2026_04_28_120000_create_portal_access_requests_table.
    }

    public function down(): void
    {
        // No-op: rollback is handled by 2026_04_28_120000_create_portal_access_requests_table.
    }
};
