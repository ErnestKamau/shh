<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    /**
     * Align lab_category_items FK columns with UUID primary keys on related tables.
     */
    public function up(): void
    {
        if (! Schema::hasTable('lab_category_items')) {
            return;
        }

        $uuidUsing = static fn (string $column, ?string $fallbackColumn = null): string => $fallbackColumn
            ? "CASE
                    WHEN {$column}::text IS NULL THEN NULL
                    WHEN {$column}::text ~ '^[0-9a-fA-F-]{36}$' THEN {$column}::text::uuid
                    WHEN {$fallbackColumn} IS NOT NULL THEN {$fallbackColumn}
                    ELSE NULL
               END"
            : "CASE
                    WHEN {$column}::text IS NULL THEN NULL
                    WHEN {$column}::text ~ '^[0-9a-fA-F-]{36}$' THEN {$column}::text::uuid
                    ELSE NULL
               END";

        foreach (['sub_category_id', 'category_id', 'unit_measure_id'] as $column) {
            if (! Schema::hasColumn('lab_category_items', $column)) {
                continue;
            }

            DB::statement("ALTER TABLE lab_category_items ALTER COLUMN {$column} DROP DEFAULT");
            DB::statement("ALTER TABLE lab_category_items ALTER COLUMN {$column} DROP NOT NULL");
            DB::statement(
                "ALTER TABLE lab_category_items
                 ALTER COLUMN {$column} TYPE uuid
                 USING ({$uuidUsing($column)})"
            );
        }

        if (Schema::hasColumn('lab_category_items', 'reagent_id')) {
            DB::statement('ALTER TABLE lab_category_items ALTER COLUMN reagent_id DROP DEFAULT');
            DB::statement('ALTER TABLE lab_category_items ALTER COLUMN reagent_id DROP NOT NULL');
            DB::statement(
                'ALTER TABLE lab_category_items
                 ALTER COLUMN reagent_id TYPE uuid
                 USING ('.$uuidUsing('reagent_id', 'inventory_sub_category_id').')'
            );
        }

        Schema::table('lab_category_items', function (Blueprint $table): void {
            if (Schema::hasColumn('lab_category_items', 'sub_category_id')) {
                $table->foreign('sub_category_id', 'fk_lab_category_items_sub_category_id')
                    ->references('id')
                    ->on('lab_sub_category')
                    ->cascadeOnDelete();
            }

            if (Schema::hasColumn('lab_category_items', 'category_id')) {
                $table->foreign('category_id', 'fk_lab_category_items_category_id')
                    ->references('id')
                    ->on('lab_inventory_category')
                    ->restrictOnDelete();
            }

            if (Schema::hasColumn('lab_category_items', 'unit_measure_id')) {
                $table->foreign('unit_measure_id', 'fk_lab_category_items_unit_measure_id')
                    ->references('id')
                    ->on('reporting_units')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('lab_category_items')) {
            return;
        }

        DB::statement('ALTER TABLE lab_category_items DROP CONSTRAINT IF EXISTS fk_lab_category_items_sub_category_id');
        DB::statement('ALTER TABLE lab_category_items DROP CONSTRAINT IF EXISTS fk_lab_category_items_category_id');
        DB::statement('ALTER TABLE lab_category_items DROP CONSTRAINT IF EXISTS fk_lab_category_items_unit_measure_id');

        $integerUsing = static fn (string $column): string => "CASE
                WHEN {$column} IS NULL THEN NULL
                WHEN {$column}::text ~ '^[0-9]+$' THEN {$column}::text::integer
                ELSE NULL
           END";

        foreach (['sub_category_id', 'category_id', 'unit_measure_id', 'reagent_id'] as $column) {
            if (! Schema::hasColumn('lab_category_items', $column)) {
                continue;
            }

            DB::statement("ALTER TABLE lab_category_items ALTER COLUMN {$column} DROP DEFAULT");
            DB::statement("ALTER TABLE lab_category_items ALTER COLUMN {$column} DROP NOT NULL");
            DB::statement(
                "ALTER TABLE lab_category_items
                 ALTER COLUMN {$column} TYPE integer
                 USING ({$integerUsing($column)})"
            );
        }
    }
};
