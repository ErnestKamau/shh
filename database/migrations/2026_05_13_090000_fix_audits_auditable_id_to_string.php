<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('audits') || ! Schema::hasColumn('audits', 'auditable_id')) {
            return;
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE audits ALTER COLUMN auditable_id TYPE VARCHAR(255) USING auditable_id::text');

            return;
        }

        DB::statement('ALTER TABLE audits MODIFY auditable_id VARCHAR(255) NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('audits') || ! Schema::hasColumn('audits', 'auditable_id')) {
            return;
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE audits ALTER COLUMN auditable_id TYPE bigint USING CASE WHEN auditable_id ~ '^[0-9]+$' THEN auditable_id::bigint ELSE 0 END");

            return;
        }

        DB::statement('ALTER TABLE audits MODIFY auditable_id BIGINT NOT NULL');
    }
};
