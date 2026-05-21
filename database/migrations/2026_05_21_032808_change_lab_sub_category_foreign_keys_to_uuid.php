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
        if (! Schema::hasTable('lab_sub_category')) {
            return;
        }

        foreach ([
            'fk_lab_sub_category_category_id',
            'fk_lab_sub_category_reporting_unit',
            'lab_sub_category_category_id_foreign',
            'lab_sub_category_reporting_unit_foreign',
        ] as $constraint) {
            DB::statement("ALTER TABLE lab_sub_category DROP CONSTRAINT IF EXISTS {$constraint}");
        }

        if (Schema::hasColumn('lab_sub_category', 'category_id')) {
            DB::statement('ALTER TABLE lab_sub_category ALTER COLUMN category_id DROP DEFAULT');
            DB::statement('ALTER TABLE lab_sub_category ALTER COLUMN category_id DROP NOT NULL');
            DB::statement(
                "ALTER TABLE lab_sub_category
                 ALTER COLUMN category_id TYPE uuid
                 USING (
                    CASE
                        WHEN category_id::text IS NULL THEN NULL
                        WHEN category_id::text ~ '^[0-9a-fA-F-]{36}$' THEN category_id::text::uuid
                        ELSE NULL
                    END
                 )"
            );
        }

        if (Schema::hasColumn('lab_sub_category', 'reporting_unit')) {
            DB::statement('ALTER TABLE lab_sub_category ALTER COLUMN reporting_unit DROP DEFAULT');
            DB::statement('ALTER TABLE lab_sub_category ALTER COLUMN reporting_unit DROP NOT NULL');
            DB::statement(
                "ALTER TABLE lab_sub_category
                 ALTER COLUMN reporting_unit TYPE uuid
                 USING (
                    CASE
                        WHEN reporting_unit::text IS NULL THEN NULL
                        WHEN reporting_unit::text ~ '^[0-9a-fA-F-]{36}$' THEN reporting_unit::text::uuid
                        ELSE NULL
                    END
                 )"
            );
        }

        Schema::table('lab_sub_category', function (Blueprint $table): void {
            if (Schema::hasColumn('lab_sub_category', 'category_id')) {
                $table->foreign('category_id', 'fk_lab_sub_category_category_id')
                    ->references('id')
                    ->on('lab_inventory_category')
                    ->restrictOnDelete();
            }

            if (Schema::hasColumn('lab_sub_category', 'reporting_unit')) {
                $table->foreign('reporting_unit', 'fk_lab_sub_category_reporting_unit')
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
        if (! Schema::hasTable('lab_sub_category')) {
            return;
        }

        DB::statement('ALTER TABLE lab_sub_category DROP CONSTRAINT IF EXISTS fk_lab_sub_category_category_id');
        DB::statement('ALTER TABLE lab_sub_category DROP CONSTRAINT IF EXISTS fk_lab_sub_category_reporting_unit');

        if (Schema::hasColumn('lab_sub_category', 'category_id')) {
            DB::statement('ALTER TABLE lab_sub_category ALTER COLUMN category_id DROP DEFAULT');
            DB::statement('ALTER TABLE lab_sub_category ALTER COLUMN category_id DROP NOT NULL');
            DB::statement(
                "ALTER TABLE lab_sub_category
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

        if (Schema::hasColumn('lab_sub_category', 'reporting_unit')) {
            DB::statement('ALTER TABLE lab_sub_category ALTER COLUMN reporting_unit DROP DEFAULT');
            DB::statement('ALTER TABLE lab_sub_category ALTER COLUMN reporting_unit DROP NOT NULL');
            DB::statement(
                "ALTER TABLE lab_sub_category
                 ALTER COLUMN reporting_unit TYPE integer
                 USING (
                    CASE
                        WHEN reporting_unit IS NULL THEN NULL
                        WHEN reporting_unit::text ~ '^[0-9]+$' THEN reporting_unit::text::integer
                        ELSE NULL
                    END
                 )"
            );
        }
    }
};
