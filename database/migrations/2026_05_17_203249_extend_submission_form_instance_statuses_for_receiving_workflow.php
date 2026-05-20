<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Extend submission_form_instances.status for Samples Receiving workflow tabs.
     *
     * Uses string(50) instead of enum so PostgreSQL/MySQL both accept new slugs:
     * received, in_additional_info, complete (plus existing submitted, in_review, etc.).
     */
    public function up(): void
    {
        if (! Schema::hasTable('submission_form_instances') || ! Schema::hasColumn('submission_form_instances', 'status')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE submission_form_instances MODIFY status VARCHAR(50) NOT NULL DEFAULT 'draft'");
        } elseif ($driver === 'pgsql') {
            // Laravel enum() leaves a CHECK constraint; drop it before widening the column.
            DB::statement('ALTER TABLE submission_form_instances DROP CONSTRAINT IF EXISTS submission_form_instances_status_check');
            DB::statement('ALTER TABLE submission_form_instances ALTER COLUMN status TYPE VARCHAR(50) USING status::text');
            DB::statement("ALTER TABLE submission_form_instances ALTER COLUMN status SET DEFAULT 'draft'");
        } else {
            Schema::table('submission_form_instances', function (Blueprint $table) {
                $table->string('status', 50)->default('draft')->change();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('submission_form_instances') || ! Schema::hasColumn('submission_form_instances', 'status')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE submission_form_instances MODIFY status ENUM(
                'draft', 'submitted', 'in_review', 'approved', 'rejected', 'cancelled'
            ) NOT NULL DEFAULT 'draft'");
        }

        // PostgreSQL: leave as VARCHAR on rollback to avoid data loss for new status values.
    }
};
