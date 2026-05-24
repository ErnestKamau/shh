<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    /** @var list<string> */
    private array $tables = [
        'skills_capability_matrix_role',
        'skill_capability_detail',
        'skill_training_header',
    ];

    /**
     * skills_capability_matrix.id is uuid; child capability_id columns were still integer.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'capability_id')) {
                continue;
            }

            if ($driver === 'pgsql') {
                DB::statement("ALTER TABLE {$table} ALTER COLUMN capability_id DROP DEFAULT");
                DB::statement(
                    "ALTER TABLE {$table}
                     ALTER COLUMN capability_id TYPE uuid
                     USING (
                        CASE
                            WHEN capability_id::text ~ '^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$'
                                THEN capability_id::text::uuid
                            ELSE NULL
                        END
                     )"
                );

                continue;
            }

            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE {$table} MODIFY capability_id CHAR(36) NULL");
            }
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'capability_id')) {
                continue;
            }

            if ($driver === 'pgsql') {
                DB::statement(
                    "ALTER TABLE {$table}
                     ALTER COLUMN capability_id TYPE integer
                     USING (
                        CASE
                            WHEN capability_id IS NULL THEN NULL
                            ELSE 0
                        END
                     )"
                );

                continue;
            }

            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE {$table} MODIFY capability_id INT NULL");
            }
        }
    }
};
