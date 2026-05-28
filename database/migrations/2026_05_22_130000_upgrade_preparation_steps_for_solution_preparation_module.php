<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('preparation_steps')) {
            return;
        }

        Schema::table('preparation_steps', function (Blueprint $table): void {
            if (! Schema::hasColumn('preparation_steps', 'step_type')) {
                $table->string('step_type')->nullable();
            }
            if (! Schema::hasColumn('preparation_steps', 'sample_type_id')) {
                $table->uuid('sample_type_id')->nullable();
            }
            if (! Schema::hasColumn('preparation_steps', 'analysis_type_id')) {
                $table->uuid('analysis_type_id')->nullable();
            }
            if (! Schema::hasColumn('preparation_steps', 'selected_analytes')) {
                $table->json('selected_analytes')->nullable();
            }
            if (! Schema::hasColumn('preparation_steps', 'result_type')) {
                $table->string('result_type')->nullable();
            }
            if (! Schema::hasColumn('preparation_steps', 'analyte_result_types')) {
                $table->json('analyte_result_types')->nullable();
            }
            if (! Schema::hasColumn('preparation_steps', 'standard_id')) {
                $table->uuid('standard_id')->nullable();
            }
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            $this->convertPgsqlUuidColumns();
        } elseif ($driver === 'mysql') {
            $this->convertMysqlUuidColumns();
        }
    }

    public function down(): void
    {
        // Irreversible schema upgrade.
    }

    private function convertPgsqlUuidColumns(): void
    {
        $schema = $this->pgsqlSchema();

        foreach (['preparation_id', 'ingredient_id', 'uom_id', 'completed_by'] as $column) {
            if (! Schema::hasColumn('preparation_steps', $column)) {
                continue;
            }

            $udt = DB::table('information_schema.columns')
                ->where('table_schema', $schema)
                ->where('table_name', 'preparation_steps')
                ->where('column_name', $column)
                ->value('udt_name');

            if ($udt === 'uuid') {
                continue;
            }

            DB::statement("ALTER TABLE preparation_steps ALTER COLUMN {$column} DROP NOT NULL");

            DB::statement("
                ALTER TABLE preparation_steps
                ALTER COLUMN {$column} TYPE uuid
                USING (
                    CASE
                        WHEN {$column}::text ~ '^[0-9a-fA-F-]{36}$'
                            THEN {$column}::text::uuid
                        ELSE NULL
                    END
                )
            ");
        }
    }

    private function convertMysqlUuidColumns(): void
    {
        foreach (['preparation_id', 'ingredient_id', 'uom_id', 'completed_by'] as $column) {
            if (! Schema::hasColumn('preparation_steps', $column)) {
                continue;
            }

            $col = DB::selectOne("SHOW COLUMNS FROM preparation_steps WHERE Field = '{$column}'");
            if ($col && str_contains(strtolower((string) $col->Type), 'char')) {
                continue;
            }

            DB::statement("ALTER TABLE preparation_steps MODIFY {$column} VARCHAR(36) NULL");
            DB::statement("
                UPDATE preparation_steps
                SET {$column} = NULL
                WHERE {$column} IS NOT NULL
                  AND (
                    CHAR_LENGTH({$column}) <> 36
                    OR {$column} NOT REGEXP '^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$'
                  )
            ");
            DB::statement("ALTER TABLE preparation_steps MODIFY {$column} CHAR(36) NULL");
        }
    }

    private function pgsqlSchema(): string
    {
        $path = Schema::getConnection()->getConfig('search_path');
        if (is_string($path) && $path !== '') {
            return trim(explode(',', $path)[0]);
        }

        return 'public';
    }
};
