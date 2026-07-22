<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * User PII columns use SafeEncrypted casts. Laravel ciphertext routinely
     * exceeds varchar(255), so keep these columns as text.
     *
     * @var list<string>
     */
    private array $columns = [
        'name',
        'first_name',
        'middle_name',
        'last_name',
        'phone',
        'gender',
        'designation',
        'date_of_birth',
        'id_number',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $views = $this->dependentViews();

        foreach (array_keys($views) as $viewName) {
            DB::statement('DROP VIEW IF EXISTS '.$viewName);
        }

        foreach ($this->columns as $column) {
            if (! Schema::hasColumn('users', $column)) {
                continue;
            }

            DB::statement("
                ALTER TABLE users
                ALTER COLUMN {$column} TYPE text
                USING {$column}::text
            ");
        }

        foreach ($views as $viewName => $definition) {
            DB::statement("CREATE VIEW {$viewName} AS {$definition}");
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $views = $this->dependentViews();

        foreach (array_keys($views) as $viewName) {
            DB::statement('DROP VIEW IF EXISTS '.$viewName);
        }

        // Only shrink columns that were historically varchar(255) in this app.
        $shrinkable = ['name', 'first_name', 'middle_name', 'last_name'];

        foreach ($shrinkable as $column) {
            if (! Schema::hasColumn('users', $column)) {
                continue;
            }

            DB::statement("
                ALTER TABLE users
                ALTER COLUMN {$column} TYPE character varying(255)
                USING LEFT({$column}::text, 255)
            ");
        }

        foreach ($views as $viewName => $definition) {
            DB::statement("CREATE VIEW {$viewName} AS {$definition}");
        }
    }

    /**
     * Capture current definitions for views that depend on the users PII columns.
     *
     * @return array<string, string>
     */
    private function dependentViews(): array
    {
        $rows = DB::select("
            SELECT DISTINCT dependent_view.relname AS view_name
            FROM pg_depend
            JOIN pg_rewrite ON pg_depend.objid = pg_rewrite.oid
            JOIN pg_class AS dependent_view ON pg_rewrite.ev_class = dependent_view.oid
            JOIN pg_class AS source_table ON pg_depend.refobjid = source_table.oid
            JOIN pg_attribute
                ON pg_depend.refobjid = pg_attribute.attrelid
                AND pg_depend.refobjsubid = pg_attribute.attnum
            JOIN pg_namespace source_ns ON source_ns.oid = source_table.relnamespace
            WHERE source_ns.nspname = 'public'
              AND source_table.relname = 'users'
              AND pg_attribute.attname = ANY(?)
              AND dependent_view.relkind = 'v'
            ORDER BY 1
        ", ['{'.implode(',', $this->columns).'}']);

        $views = [];

        foreach ($rows as $row) {
            $viewName = (string) $row->view_name;
            $definition = DB::selectOne('SELECT pg_get_viewdef(?::regclass, true) AS definition', [$viewName]);
            if ($definition && is_string($definition->definition) && $definition->definition !== '') {
                $views[$viewName] = rtrim($definition->definition, ';');
            }
        }

        return $views;
    }
};
