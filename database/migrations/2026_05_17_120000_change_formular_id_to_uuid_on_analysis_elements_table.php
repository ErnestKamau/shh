<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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

        Schema::table('analysis_elements', function (Blueprint $table): void {
            $table->uuid('formular_id')->nullable()->change();
        });

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

        try {
            Schema::table('analysis_elements', function (Blueprint $table): void {
                $table->dropForeign(['formular_id']);
            });
        } catch (\Throwable) {
        }
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
