<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        if (! Schema::hasTable('analysis_elements') || ! Schema::hasColumn('analysis_elements', 'formular_id')) {
            return;
        }

        if (Schema::getColumnType('analysis_elements', 'formular_id') === 'uuid') {
            $this->ensureFormularForeignKey();

            return;
        }

        DB::table('analysis_elements')->update(['formular_id' => null]);

        $this->dropFormularForeignKeyIfExists();

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE analysis_elements ALTER COLUMN formular_id TYPE uuid USING NULL');
        } else {
            Schema::table('analysis_elements', function (Blueprint $table): void {
                $table->uuid('formular_id')->nullable()->change();
            });
        }

        $this->ensureFormularForeignKey();
    }

    public function down(): void
    {
        if (! Schema::hasTable('analysis_elements') || ! Schema::hasColumn('analysis_elements', 'formular_id')) {
            return;
        }

        if (Schema::getColumnType('analysis_elements', 'formular_id') !== 'uuid') {
            return;
        }

        DB::table('analysis_elements')->update(['formular_id' => null]);

        $this->dropFormularForeignKeyIfExists();

        Schema::table('analysis_elements', function (Blueprint $table): void {
            $table->unsignedBigInteger('formular_id')->nullable()->change();
        });
    }

    private function dropFormularForeignKeyIfExists(): void
    {
        foreach (['analysis_elements_formular_id_foreign', 'fk_analysis_elements_formular_id'] as $constraint) {
            if (! $this->foreignKeyExists('analysis_elements', $constraint)) {
                continue;
            }

            Schema::table('analysis_elements', function (Blueprint $table) use ($constraint): void {
                $table->dropForeign($constraint);
            });

            return;
        }

        if ($this->foreignKeyExistsOnColumn('analysis_elements', 'formular_id')) {
            Schema::table('analysis_elements', function (Blueprint $table): void {
                $table->dropForeign(['formular_id']);
            });
        }
    }

    private function foreignKeyExistsOnColumn(string $table, string $column): bool
    {
        $schema = Schema::getConnection()->getConfig('schema') ?? 'public';

        return (bool) DB::table('information_schema.key_column_usage as kcu')
            ->join('information_schema.table_constraints as tc', function ($join): void {
                $join->on('tc.constraint_name', '=', 'kcu.constraint_name')
                    ->on('tc.table_schema', '=', 'kcu.table_schema');
            })
            ->where('tc.constraint_type', 'FOREIGN KEY')
            ->where('kcu.table_schema', $schema)
            ->where('kcu.table_name', $table)
            ->where('kcu.column_name', $column)
            ->exists();
    }

    private function ensureFormularForeignKey(): void
    {
        if (! Schema::hasTable('formulas') || $this->foreignKeyExists('analysis_elements', 'analysis_elements_formular_id_foreign')) {
            return;
        }

        Schema::table('analysis_elements', function (Blueprint $table): void {
            $table->foreign('formular_id', 'analysis_elements_formular_id_foreign')
                ->references('id')
                ->on('formulas')
                ->nullOnDelete();
        });
    }

    private function foreignKeyExists(string $table, string $constraint): bool
    {
        $schema = Schema::getConnection()->getConfig('schema') ?? 'public';

        return (bool) DB::table('information_schema.table_constraints')
            ->where('constraint_schema', $schema)
            ->where('table_name', $table)
            ->where('constraint_name', $constraint)
            ->where('constraint_type', 'FOREIGN KEY')
            ->exists();
    }
};
