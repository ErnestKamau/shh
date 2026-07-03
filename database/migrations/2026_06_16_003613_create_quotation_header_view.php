<?php

use Illuminate\Database\Migrations\Migration;

/**
 * NOTE: Superseded by 2026_06_16_123613_create_quotation_header_view.php.
 * Kept as a tracked no-op to preserve migration history integrity.
 */
return new class extends Migration
{
    public function up(): void
    {
        // No-op: quotation_header_view is created in 2026_06_16_123613_create_quotation_header_view.
    }

    public function down(): void
    {
        // No-op: rollback is handled by 2026_06_16_123613_create_quotation_header_view.
    }
};
