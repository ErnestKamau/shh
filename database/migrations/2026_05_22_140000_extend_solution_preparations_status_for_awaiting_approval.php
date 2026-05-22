<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Legacy solution_preparations.status omitted awaiting_approval (PostgreSQL CHECK / MySQL ENUM).
     */
    public function up(): void
    {
        if (! Schema::hasTable('solution_preparations') || ! Schema::hasColumn('solution_preparations', 'status')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
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
            DB::statement("ALTER TABLE solution_preparations ADD CONSTRAINT solution_preparations_status_check CHECK (status IN ('preparing', 'awaiting_approval', 'completed', 'cancelled', 'failed'))");
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE solution_preparations MODIFY status ENUM('preparing', 'awaiting_approval', 'completed', 'cancelled', 'failed') NOT NULL DEFAULT 'preparing'");
        } else {
            Schema::table('solution_preparations', function (Blueprint $table): void {
                $table->string('status', 50)->default('preparing')->change();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('solution_preparations') || ! Schema::hasColumn('solution_preparations', 'status')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE solution_preparations DROP CONSTRAINT IF EXISTS solution_preparations_status_check');
            DB::statement("UPDATE solution_preparations SET status = 'preparing' WHERE status = 'awaiting_approval'");
            DB::statement('ALTER TABLE solution_preparations ALTER COLUMN status TYPE VARCHAR(50) USING status::text');
            DB::statement("ALTER TABLE solution_preparations ADD CONSTRAINT solution_preparations_status_check CHECK (status IN ('preparing', 'completed', 'cancelled', 'failed'))");
        } elseif ($driver === 'mysql') {
            DB::statement("UPDATE solution_preparations SET status = 'preparing' WHERE status = 'awaiting_approval'");
            DB::statement("ALTER TABLE solution_preparations MODIFY status ENUM('preparing', 'completed', 'cancelled', 'failed') NOT NULL DEFAULT 'preparing'");
        }
    }
};
