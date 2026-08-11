<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('analysis_elements')) {
            $this->dropForeignKeyIfExists('analysis_elements', [
                'fk_analysis_elements_type_of_analysis_id',
                'analysis_elements_type_of_analysis_id_foreign',
            ]);
            $this->dropForeignKeyIfExists('analysis_elements', [
                'fk_analysis_elements_parameter_group_id',
                'analysis_elements_parameter_group_id_foreign',
            ]);

            Schema::table('analysis_elements', function (Blueprint $table): void {
                if (Schema::hasColumn('analysis_elements', 'type_of_analysis_id')) {
                    $table->dropColumn('type_of_analysis_id');
                }

                if (Schema::hasColumn('analysis_elements', 'parameter_group_id')) {
                    $table->dropColumn('parameter_group_id');
                }
            });
        }

        if (Schema::hasTable('types_of_analysis')) {
            Schema::drop('types_of_analysis');
        }

        if (Schema::hasTable('parameter_groups')) {
            Schema::drop('parameter_groups');
        }
    }

    public function down(): void
    {
        Schema::create('types_of_analysis', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('parameter_groups', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        if (Schema::hasTable('analysis_elements')) {
            Schema::table('analysis_elements', function (Blueprint $table): void {
                if (! Schema::hasColumn('analysis_elements', 'type_of_analysis_id')) {
                    $table->uuid('type_of_analysis_id')->nullable();
                }
                if (! Schema::hasColumn('analysis_elements', 'parameter_group_id')) {
                    $table->uuid('parameter_group_id')->nullable();
                }
                $table->foreign('type_of_analysis_id', 'fk_analysis_elements_type_of_analysis_id')
                    ->references('id')
                    ->on('types_of_analysis')
                    ->nullOnDelete();
                $table->foreign('parameter_group_id', 'fk_analysis_elements_parameter_group_id')
                    ->references('id')
                    ->on('parameter_groups')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * @param  list<string>  $candidateNames
     */
    private function dropForeignKeyIfExists(string $table, array $candidateNames): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            Schema::table($table, function (Blueprint $table) use ($candidateNames): void {
                foreach ($candidateNames as $name) {
                    try {
                        $table->dropForeign($name);
                    } catch (\Throwable) {
                        // Ignore missing constraint names on non-pgsql drivers.
                    }
                }
            });

            return;
        }

        foreach ($candidateNames as $name) {
            $exists = DB::selectOne(
                'SELECT 1 AS present
                 FROM pg_constraint
                 WHERE conrelid = ?::regclass
                   AND contype = ?
                   AND conname = ?',
                [$table, 'f', $name]
            );

            if ($exists !== null) {
                DB::statement(sprintf(
                    'ALTER TABLE %s DROP CONSTRAINT %s',
                    $table,
                    $name
                ));

                return;
            }
        }

        // Fallback: drop any FK attached to the expected column when naming differs.
        $column = str_contains(implode('|', $candidateNames), 'parameter_group')
            ? 'parameter_group_id'
            : 'type_of_analysis_id';

        $row = DB::selectOne(
            "SELECT c.conname
             FROM pg_constraint c
             JOIN pg_attribute a
               ON a.attrelid = c.conrelid
              AND a.attnum = ANY (c.conkey)
             WHERE c.conrelid = ?::regclass
               AND c.contype = 'f'
               AND a.attname = ?
             LIMIT 1",
            [$table, $column]
        );

        if ($row?->conname) {
            DB::statement(sprintf(
                'ALTER TABLE %s DROP CONSTRAINT %s',
                $table,
                $row->conname
            ));
        }
    }
};
