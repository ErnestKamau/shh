<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('supplier_by_categories') || DB::getDriverName() !== 'pgsql') {
            return;
        }

        if (! Schema::hasColumn('supplier_by_categories', 'category_id')) {
            return;
        }

        $isUuid = DB::table('information_schema.columns')
            ->where('table_schema', 'public')
            ->where('table_name', 'supplier_by_categories')
            ->where('column_name', 'category_id')
            ->where('udt_name', 'uuid')
            ->exists();

        if ($isUuid) {
            return;
        }

        DB::statement('ALTER TABLE supplier_by_categories DROP CONSTRAINT IF EXISTS fk_supplier_by_categories_category_id');
        DB::statement('ALTER TABLE supplier_by_categories ALTER COLUMN category_id DROP DEFAULT');
        DB::statement('ALTER TABLE supplier_by_categories ALTER COLUMN category_id DROP NOT NULL');

        // Legacy integer category IDs cannot map to UUID inventory_categories; keep only UUID-shaped values.
        DB::statement(
            "ALTER TABLE supplier_by_categories
             ALTER COLUMN category_id TYPE uuid
             USING (
                CASE
                    WHEN category_id::text IS NULL THEN NULL
                    WHEN category_id::text ~ '^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$'
                        THEN category_id::text::uuid
                    ELSE NULL
                END
             )"
        );

        Schema::table('supplier_by_categories', function (Blueprint $table): void {
            $table->foreign('category_id', 'fk_supplier_by_categories_category_id')
                ->references('id')
                ->on('inventory_categories')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('supplier_by_categories') || DB::getDriverName() !== 'pgsql') {
            return;
        }

        if (! Schema::hasColumn('supplier_by_categories', 'category_id')) {
            return;
        }

        DB::statement('ALTER TABLE supplier_by_categories DROP CONSTRAINT IF EXISTS fk_supplier_by_categories_category_id');
        DB::statement('ALTER TABLE supplier_by_categories ALTER COLUMN category_id DROP DEFAULT');
        DB::statement('ALTER TABLE supplier_by_categories ALTER COLUMN category_id DROP NOT NULL');

        DB::statement(
            "ALTER TABLE supplier_by_categories
             ALTER COLUMN category_id TYPE integer
             USING (
                CASE
                    WHEN category_id IS NULL THEN NULL
                    WHEN category_id::text ~ '^[0-9]+$' THEN category_id::text::integer
                    ELSE NULL
                END
             )"
        );
    }
};
