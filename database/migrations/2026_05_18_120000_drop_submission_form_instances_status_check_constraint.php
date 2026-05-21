<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PostgreSQL keeps submission_form_instances_status_check after enum→varchar
     * migrations, which blocks receiving workflow statuses (received, etc.).
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        if (! Schema::hasTable('submission_form_instances')) {
            return;
        }

        DB::statement('ALTER TABLE submission_form_instances DROP CONSTRAINT IF EXISTS submission_form_instances_status_check');
    }

    public function down(): void
    {
        // Intentionally empty: do not restore the old enum check (would reject new statuses).
    }
};
