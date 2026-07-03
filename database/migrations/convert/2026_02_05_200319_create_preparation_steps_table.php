<?php

use Illuminate\Database\Migrations\Migration;

/**
 * NOTE: Superseded by 2026_05_22_100000_create_solution_preparation_module_tables.php.
 * Kept as a tracked no-op to preserve migration history integrity.
 */
return new class extends Migration
{
    public function up(): void
    {
        // No-op: canonical preparation_steps schema is in the solution preparation module migration.
    }

    public function down(): void
    {
        // No-op: rollback is handled by 2026_05_22_100000_create_solution_preparation_module_tables.
    }
};
