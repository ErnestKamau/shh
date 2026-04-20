<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('labs', 'directorate_id')) {
            Schema::table('labs', function (Blueprint $table) {
                $table->unsignedBigInteger('directorate_id')->nullable()->after('company_id')->index();
            });
        }

        if (!Schema::hasColumn('labs', 'manager_id')) {
            Schema::table('labs', function (Blueprint $table) {
                $table->bigInteger('manager_id')->nullable()->after('directorate_id')->index();
            });
        }

        if (!Schema::hasColumn('labs', 'analyst_ids')) {
            Schema::table('labs', function (Blueprint $table) {
                $table->json('analyst_ids')->nullable()->after('manager_id');
            });
        }

        if ($this->columnType('labs', 'manager_id') === 'bigint unsigned') {
            DB::statement('ALTER TABLE labs MODIFY manager_id BIGINT NULL');
        }

        if (!$this->foreignKeyExists('labs', 'labs_directorate_id_foreign')) {
            Schema::table('labs', function (Blueprint $table) {
                $table->foreign('directorate_id')->references('id')->on('directorates')->nullOnDelete();
            });
        }

        if (!$this->foreignKeyExists('labs', 'labs_manager_id_foreign')) {
            Schema::table('labs', function (Blueprint $table) {
                $table->foreign('manager_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (!$this->indexExists('labs', 'labs_directorate_name_unique')) {
            Schema::table('labs', function (Blueprint $table) {
                $table->unique(['directorate_id', 'name'], 'labs_directorate_name_unique');
            });
        }

        if (!$this->indexExists('labs', 'labs_directorate_code_unique')) {
            Schema::table('labs', function (Blueprint $table) {
                $table->unique(['directorate_id', 'code'], 'labs_directorate_code_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('labs', function (Blueprint $table) {
            if ($this->indexExists('labs', 'labs_directorate_name_unique')) {
                $table->dropUnique('labs_directorate_name_unique');
            }

            if ($this->indexExists('labs', 'labs_directorate_code_unique')) {
                $table->dropUnique('labs_directorate_code_unique');
            }

            if ($this->foreignKeyExists('labs', 'labs_directorate_id_foreign')) {
                $table->dropForeign('labs_directorate_id_foreign');
            }

            if ($this->foreignKeyExists('labs', 'labs_manager_id_foreign')) {
                $table->dropForeign('labs_manager_id_foreign');
            }
        });

        $dropColumns = [];
        foreach (['directorate_id', 'manager_id', 'analyst_ids'] as $column) {
            if (Schema::hasColumn('labs', $column)) {
                $dropColumns[] = $column;
            }
        }

        if ($dropColumns !== []) {
            Schema::table('labs', function (Blueprint $table) use ($dropColumns) {
                $table->dropColumn($dropColumns);
            });
        }
    }

    private function foreignKeyExists(string $table, string $constraintName): bool
    {
        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraintName)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }

    private function indexExists(string $table, string $indexName): bool
    {
        return DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
            ->where('TABLE_NAME', $table)
            ->where('INDEX_NAME', $indexName)
            ->exists();
    }

    private function columnType(string $table, string $column): ?string
    {
        return DB::table('information_schema.COLUMNS')
            ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
            ->where('TABLE_NAME', $table)
            ->where('COLUMN_NAME', $column)
            ->value('COLUMN_TYPE');
    }
};
