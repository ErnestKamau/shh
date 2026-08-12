<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Convert-era sample_type_categories was created with a bare integer id
 * and no primary key. Later FKs (e.g. submission_form_sample_type_categories)
 * require a unique/PK on sample_type_categories.id.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sample_type_categories')) {
            return;
        }

        if (! Schema::hasColumn('sample_type_categories', 'id')) {
            return;
        }

        if ($this->hasPrimaryKey('sample_type_categories')) {
            $this->ensureIdSequence();

            return;
        }

        $duplicates = DB::table('sample_type_categories')
            ->select('id', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('id')
            ->havingRaw('COUNT(*) > 1')
            ->limit(1)
            ->exists();

        if ($duplicates) {
            throw new RuntimeException(
                'Cannot add primary key on sample_type_categories.id: duplicate id values exist. Deduplicate first.'
            );
        }

        DB::statement('ALTER TABLE sample_type_categories ADD PRIMARY KEY (id)');

        $this->ensureIdSequence();
    }

    public function down(): void
    {
        // Intentionally not dropping the PK — converting tables need it permanently.
    }

    private function hasPrimaryKey(string $table): bool
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return false;
        }

        $row = DB::selectOne(
            'SELECT 1 AS present
             FROM pg_constraint
             WHERE conrelid = ?::regclass
               AND contype = ?',
            [$table, 'p']
        );

        return $row !== null;
    }

    private function ensureIdSequence(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        $default = DB::selectOne(
            "SELECT column_default
             FROM information_schema.columns
             WHERE table_schema = 'public'
               AND table_name = 'sample_type_categories'
               AND column_name = 'id'"
        );

        $columnDefault = (string) ($default->column_default ?? '');
        if (str_contains($columnDefault, 'nextval')) {
            return;
        }

        DB::statement('CREATE SEQUENCE IF NOT EXISTS sample_type_categories_id_seq');
        DB::statement(
            "SELECT setval(
                'sample_type_categories_id_seq',
                COALESCE((SELECT MAX(id) FROM sample_type_categories), 1)
            )"
        );
        DB::statement(
            "ALTER TABLE sample_type_categories
             ALTER COLUMN id SET DEFAULT nextval('sample_type_categories_id_seq')"
        );
        DB::statement(
            'ALTER SEQUENCE sample_type_categories_id_seq OWNED BY sample_type_categories.id'
        );
    }
};
