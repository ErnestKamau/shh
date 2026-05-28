<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Idempotent fix: legacy solution_preparations.status CHECK omits awaiting_approval.
     */
    public function up(): void
    {
        if (! Schema::hasTable('solution_preparations') || ! Schema::hasColumn('solution_preparations', 'status')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            $this->fixPostgresStatusColumn();
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE solution_preparations MODIFY status ENUM('preparing', 'awaiting_approval', 'completed', 'cancelled', 'failed') NOT NULL DEFAULT 'preparing'");
        }
    }

    protected function fixPostgresStatusColumn(): void
    {
        $constraints = DB::select("
            SELECT c.conname AS name
            FROM pg_constraint c
            JOIN pg_class t ON c.conrelid = t.oid
            JOIN pg_namespace n ON t.relnamespace = n.oid
            WHERE n.nspname = current_schema()
              AND t.relname = 'solution_preparations'
              AND c.contype = 'c'
              AND pg_get_constraintdef(c.oid) ILIKE '%status%'
        ");

        foreach ($constraints as $constraint) {
            $name = str_replace('"', '""', $constraint->name);
            DB::statement("ALTER TABLE solution_preparations DROP CONSTRAINT IF EXISTS \"{$name}\"");
        }

        DB::statement('ALTER TABLE solution_preparations DROP CONSTRAINT IF EXISTS solution_preparations_status_check');

        DB::statement('ALTER TABLE solution_preparations ALTER COLUMN status DROP DEFAULT');
        DB::statement('ALTER TABLE solution_preparations ALTER COLUMN status TYPE VARCHAR(50) USING status::text');
        DB::statement("ALTER TABLE solution_preparations ALTER COLUMN status SET DEFAULT 'preparing'");
        DB::statement('ALTER TABLE solution_preparations ALTER COLUMN status SET NOT NULL');

        $hasConstraint = DB::selectOne("
            SELECT 1
            FROM pg_constraint c
            JOIN pg_class t ON c.conrelid = t.oid
            JOIN pg_namespace n ON t.relnamespace = n.oid
            WHERE n.nspname = current_schema()
              AND t.relname = 'solution_preparations'
              AND c.conname = 'solution_preparations_status_check'
        ");

        if (! $hasConstraint) {
            DB::statement("ALTER TABLE solution_preparations ADD CONSTRAINT solution_preparations_status_check CHECK (status IN ('preparing', 'awaiting_approval', 'completed', 'cancelled', 'failed'))");
        }
    }

    public function down(): void
    {
        // Intentionally empty: do not restore the restrictive check.
    }
};
